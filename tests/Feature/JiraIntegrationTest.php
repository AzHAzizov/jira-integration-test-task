<?php

use App\Services\JiraClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

uses()->group('external'); // run with: php artisan test --group=external

/**
 * Helper to read credentials from ENV/config.
 */
function creds(): array
{
    $base  = rtrim(env('JIRA_BASE_URL') ?: config('services.jira.base_url'), '/');
    $email = env('JIRA_EMAIL') ?: config('services.jira.email');
    $token = env('JIRA_API_TOKEN') ?: config('services.jira.api_token');
    $proj  = env('JIRA_PROJECT_KEY');

    return compact('base', 'email', 'token', 'proj');
}

/**
 * Skip test if required Jira ENV variables are not set.
 */
function skipIfNoJira(): void
{
    foreach (['JIRA_BASE_URL', 'JIRA_EMAIL', 'JIRA_API_TOKEN', 'JIRA_PROJECT_KEY'] as $v) {
        if (blank(env($v))) {
            test()->markTestSkipped("$v is not set; skipping external Jira test");
        }
    }
}

it('creates, updates issue and manages comments in Jira', function () {
    skipIfNoJira();

    // Debug: show which credentials are actually loaded in the test
    $c = creds();
    dump([
        'email'      => $c['email'],
        'token_head' => $c['token'] ? substr($c['token'], 0, 8) . '…' : null,
        'projectKey' => $c['proj'],
        'base'       => $c['base'],
    ]);

    // Preflight: check who I am
    $me = Http::withBasicAuth($c['email'], $c['token'])
        ->acceptJson()
        ->get($c['base'] . '/rest/api/3/myself')
        ->throw()
        ->json();

    expect($me)->toBeArray()->toHaveKeys(['accountId', 'emailAddress']);

    // Preflight: check project permissions
    $perm = Http::withBasicAuth($c['email'], $c['token'])
        ->acceptJson()
        ->get($c['base'] . '/rest/api/3/mypermissions', [
            'projectKey'  => $c['proj'],
            'permissions' => 'BROWSE_PROJECTS,CREATE_ISSUES',
        ])
        ->throw()
        ->json();

    $createAllowed = data_get($perm, 'permissions.CREATE_ISSUES.havePermission');
    $browseAllowed = data_get($perm, 'permissions.BROWSE_PROJECTS.havePermission');

    expect($browseAllowed)->toBeTrue();
    expect($createAllowed)->toBeTrue();

    /** @var JiraClient $jira */
    $jira = app(JiraClient::class);

    $uniq = Str::upper(Str::random(6));

    // 1) Create an issue
    $created = $jira->createIssue([
        'projectKey'  => $c['proj'],
        'summary'     => "Integration Test $uniq",
        'description' => "Created by Pest integration test ($uniq).",
        'issueType'   => 'Task',
    ]);

    expect($created)->toBeArray()->toHaveKeys(['id', 'key']);
    $key = $created['key'];

    // 2) Update the issue
    $jira->editIssue($key, [
        'summary'     => "Integration Test $uniq - UPDATED",
        'description' => "Updated body ($uniq).",
    ]);

    // 3) Add a comment
    $comment = $jira->addComment($key, "Initial comment ($uniq)");
    expect($comment)->toBeArray()->toHaveKeys(['id', 'body']);
    $commentId = $comment['id'];

    // 4) Edit the comment
    $edited = $jira->editComment($key, $commentId, "Edited comment ($uniq)");
    expect($edited)->toBeArray()->toHaveKey('id', $commentId);

    // 5) Optional cleanup: delete the issue if JIRA_ALLOW_DELETE=true
    if (filter_var(env('JIRA_ALLOW_DELETE'), FILTER_VALIDATE_BOOL)) {
        Http::withBasicAuth($c['email'], $c['token'])
            ->acceptJson()
            ->delete($c['base'] . "/rest/api/3/issue/{$key}")
            ->throw();

        $resp = Http::withBasicAuth($c['email'], $c['token'])
            ->acceptJson()
            ->get($c['base'] . "/rest/api/3/issue/{$key}");
        expect($resp->status())->toBe(404);
    }
});

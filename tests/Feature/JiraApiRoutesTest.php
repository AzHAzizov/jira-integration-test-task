<?php

use Illuminate\Support\Str;

uses()->group('external'); // run with: php artisan test --group=external

/**
 * Skip test if required Jira ENV variables are not set.
 */
function skipIfNoJiraApi(): void {
    foreach (['JIRA_BASE_URL', 'JIRA_EMAIL', 'JIRA_API_TOKEN', 'JIRA_PROJECT_KEY'] as $v) {
        if (blank(env($v))) {
            test()->markTestSkipped("$v is not set; skipping Jira API route test");
        }
    }
}

it('can create issue and add/update comment via API routes', function () {
    skipIfNoJiraApi();

    $uniq = Str::upper(Str::random(6));

    // 1) Create issue via API route
    $resp = $this->postJson('/api/jira/issues', [
        'projectKey'  => env('JIRA_PROJECT_KEY'),
        'summary'     => "Route Test $uniq",
        'description' => "Created by API route test ($uniq).",
        'issueType'   => 'Task',
    ]);

    $resp->assertCreated();
    $created = $resp->json();
    expect($created)->toHaveKeys(['id','key']);
    $issueKey = $created['key'];

    // 2) Update issue via API route
    $update = $this->putJson("/api/jira/issues/{$issueKey}", [
        'summary'     => "Route Test $uniq - UPDATED",
        'description' => "Updated by API route test ($uniq).",
    ]);
    $update->assertOk();

    // 3) Add comment via API route
    $commentResp = $this->postJson("/api/jira/issues/{$issueKey}/comments", [
        'body' => "Initial comment ($uniq)",
    ]);
    $commentResp->assertCreated();
    $commentId = $commentResp->json('id');

    // 4) Edit comment via API route
    $editResp = $this->putJson("/api/jira/issues/{$issueKey}/comments/{$commentId}", [
        'body' => "Edited comment ($uniq)",
    ]);
    $editResp->assertOk();

    // 5) (Optional) cleanup: you can delete the issue directly via Jira API,
    // same as in the integration test, if JIRA_ALLOW_DELETE=true
});

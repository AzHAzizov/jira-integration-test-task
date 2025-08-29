<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class JiraClient
{
    private string $baseUrl;
    private string $email;
    private string $apiToken;

    public function __construct()
    {
        $this->baseUrl  = rtrim(config('services.jira.base_url'), '/');
        $this->email    = config('services.jira.email');
        $this->apiToken = config('services.jira.api_token');
    }

    private function client()
    {
        return Http::withBasicAuth($this->email, $this->apiToken)
            ->acceptJson()
            ->asJson()
            ->baseUrl($this->baseUrl);
    }

    /** Convert plain text to minimal ADF doc */
    private function adfFromText(?string $text): ?array
    {
        if ($text === null) {
            return null;
        }

        return [
            'type'    => 'doc',
            'version' => 1,
            'content' => [[
                'type' => 'paragraph',
                'content' => [[ 'type' => 'text', 'text' => $text ]],
            ]],
        ];
    }

    /** Create issue */
    public function createIssue(array $payload): array
    {
        $body = [
            'fields' => [
                'project'   => ['key' => $payload['projectKey']],
                'summary'   => $payload['summary'],
                'issuetype' => ['name' => $payload['issueType'] ?? 'Task'],
            ],
        ];

        // If description provided, send as ADF
        if (!empty($payload['description'])) {
            $body['fields']['description'] = $this->adfFromText($payload['description']);
        }

        $res = $this->client()->post('/rest/api/3/issue', $body)->throw();
        return $res->json();
    }

    /** Edit issue fields */
    public function editIssue(string $issueKeyOrId, array $fields): void
    {
        $jiraFields = [];

        if (array_key_exists('summary', $fields)) {
            $jiraFields['summary'] = $fields['summary'];
        }

        if (array_key_exists('description', $fields)) {
            // Convert to ADF (also supports null to clear the description)
            $jiraFields['description'] = $this->adfFromText($fields['description']);
        }

        // Add any other simple fields you pass through:
        foreach ($fields as $k => $v) {
            if (!in_array($k, ['summary','description'], true)) {
                $jiraFields[$k] = $v;
            }
        }

        $this->client()->put("/rest/api/3/issue/{$issueKeyOrId}", [
            'fields' => $jiraFields,
        ])->throw();
    }

    /** Add comment (ADF) */
    public function addComment(string $issueKeyOrId, string $comment): array
    {
        $res = $this->client()
            ->post("/rest/api/3/issue/{$issueKeyOrId}/comment", [
                'body' => $this->adfFromText($comment),
            ])->throw();

        return $res->json();
    }

    /** Edit comment (ADF) */
    public function editComment(string $issueKeyOrId, string $commentId, string $comment): array
    {
        $res = $this->client()
            ->put("/rest/api/3/issue/{$issueKeyOrId}/comment/{$commentId}", [
                'body' => $this->adfFromText($comment),
            ])->throw();

        return $res->json();
    }
}

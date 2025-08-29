<?php

namespace App\Console\Commands;

use App\Services\JiraClient;
use Illuminate\Console\Command;

class JiraDemo extends Command
{
    protected $signature = 'jira:demo {summary=Demo from CLI}';
    protected $description = 'Creates an issue then comments on it';

    public function handle(JiraClient $jira): int
    {
        $projectKey = config('app.jira_project_key', env('JIRA_PROJECT_KEY', 'ICEW'));

        $issue = $jira->createIssue([
            'projectKey'  => $projectKey,
            'summary'     => $this->argument('summary'),
            'description' => 'CLI-created issue',
            'issueType'   => 'Task',
        ]);

        $this->info('Created: '.json_encode($issue));

        $key = $issue['key'] ?? $issue['id'] ?? null;
        if ($key) {
            $comment = $jira->addComment($key, 'Hi from CLI command.');
            $this->info('Comment: '.json_encode($comment));
        }

        return self::SUCCESS;
    }
}

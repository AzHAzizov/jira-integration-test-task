<?php

namespace App\Console\Commands;

use App\Services\JiraClient;
use Illuminate\Console\Command;

class JiraUpdate extends Command
{
    protected $signature = 'jira:update {key} {--summary=} {--description=}';
    protected $description = 'Update an existing Jira issue (summary/description)';

    public function handle(JiraClient $jira): int
    {
        $key = $this->argument('key');
        $fields = [];

        if ($this->option('summary')) {
            $fields['summary'] = $this->option('summary');
        }
        if ($this->option('description')) {
            $fields['description'] = $this->option('description');
        }

        if (empty($fields)) {
            $this->error('No fields to update (use --summary or --description)');
            return self::FAILURE;
        }

        $jira->editIssue($key, $fields);

        $this->info("Issue {$key} updated.");
        return self::SUCCESS;
    }
}

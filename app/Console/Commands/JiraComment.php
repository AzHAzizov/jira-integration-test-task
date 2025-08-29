<?php

namespace App\Console\Commands;

use App\Services\JiraClient;
use Illuminate\Console\Command;

class JiraComment extends Command
{
    protected $signature = 'jira:comment {key} {text}';
    protected $description = 'Add a comment to a Jira issue';

    public function handle(JiraClient $jira): int
    {
        $key = $this->argument('key');
        $text = $this->argument('text');

        $comment = $jira->addComment($key, $text);

        $this->info("Comment added to {$key}: " . json_encode($comment));
        return self::SUCCESS;
    }
}

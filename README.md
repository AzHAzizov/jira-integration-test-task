# Jira Integration Test Task

This repository contains a simple Laravel 12 application that connects to a Jira Cloud project and demonstrates basic API operations.

## Assignment

From the given task:

1. Create a simple application that can connect to the project `icewarp-test` in Jira.
2. Create/edit a task via the API.
3. Create/edit a comment on a task.

✅ All points have been implemented.

## Features

- Connects to a Jira Cloud project  
- Create and update issues (tasks)  
- Add and edit comments on issues  
- Console commands for quick testing  
- REST API routes for HTTP interaction  
- Integration tests with Pest to verify Jira connectivity  

## Requirements

- PHP 8.2+  
- Composer  
- Docker (optional, `docker-compose.yml` is included)  
- Jira Cloud account + API token  

## Installation

```bash
git clone https://github.com/AzHAzizov/jira-integration-test-task.git
cd jira-integration-test-task

composer install
cp .env.example .env
php artisan key:generate
```

# Configuration

## Fill in your Jira credentials in .env (or .env.testing for tests):
```
JIRA_BASE_URL=https://iw-sandbox.atlassian.net
JIRA_EMAIL=your.email@example.com
JIRA_API_TOKEN=your_api_token
JIRA_PROJECT_KEY=IC
JIRA_ALLOW_DELETE=true
```

# Usage
## Console commands
+ Create issue and comment
```
php artisan jira:demo "Demo issue from CLI"
```
+ Update issue
```
php artisan jira:update ISSUE-KEY --summary="New summary" --description="New description"

```
+ Add comment
```
php artisan jira:comment ISSUE-KEY "My comment"
```
+ Edit comment
```
php artisan jira:comment-edit ISSUE-KEY COMMENT-ID "Updated comment text"
```

# API routes
```
POST /api/jira/issues → create issue
PUT /api/jira/issues/{issueKey} → update issue
POST /api/jira/issues/{issueKey}/comments → add comment
PUT /api/jira/issues/{issueKey}/comments/{commentId} → update comment
```

# Tests
## Integration tests are implemented with Pest:
```
php artisan test --group=external
```
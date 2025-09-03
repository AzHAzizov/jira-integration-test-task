<?php

namespace App\Http\Controllers;

use App\Http\Requests\Jira\AddCommentRequest;
use App\Http\Requests\Jira\CreateIssueRequest;
use App\Http\Requests\Jira\UpdateCommentRequest;
use App\Http\Requests\Jira\UpdateIssueRequest;
use App\Services\JiraClient;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class JiraController extends Controller
{
    public function __construct(private JiraClient $jira) {}

    // POST /api/jira/issues
    public function createIssue(CreateIssueRequest $request)
    {
        $created = $this->jira->createIssue($request->validated());
        return response()->json($created, Response::HTTP_CREATED);
    }

    // PUT /api/jira/issues/{issueKey}
    public function updateIssue(string $issueKey, UpdateIssueRequest $request)
    {
        $this->jira->editIssue($issueKey, $request->validated());
        return response()->json(['status' => 'ok']);
    }

    // POST /api/jira/issues/{issueKey}/comments
    public function addComment(string $issueKey, AddCommentRequest $request)
    {
        $comment = $this->jira->addComment($issueKey, $request->validated('body'));
        return response()->json($comment, Response::HTTP_CREATED);
    }

    // PUT /api/jira/issues/{issueKey}/comments/{commentId}
    public function updateComment(string $issueKey, string $commentId, UpdateCommentRequest $request)
    {
        $comment = $this->jira->editComment($issueKey, $commentId, $request->validated('body'));
        return response()->json($comment);
    }
}

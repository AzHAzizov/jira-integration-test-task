<?php

namespace App\Http\Controllers;

use App\Services\JiraClient;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class JiraController extends Controller
{
    public function __construct(private JiraClient $jira) {}

    // POST /api/jira/issues
    public function createIssue(Request $request)
    {
        $data = $request->validate([
            'projectKey'  => 'required|string',
            'summary'     => 'required|string|max:255',
            'description' => 'nullable|string',
            'issueType'   => 'nullable|string',
        ]);

        $created = $this->jira->createIssue($data);

        return response()->json($created, Response::HTTP_CREATED);
    }

    // PUT /api/jira/issues/{issueKey}
    public function updateIssue(string $issueKey, Request $request)
    {
        $fields = $request->validate([
            'summary'     => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            // Add more Jira fields as needed
        ]);

        $this->jira->editIssue($issueKey, $fields);

        return response()->json(['status' => 'ok']);
    }

    // POST /api/jira/issues/{issueKey}/comments
    public function addComment(string $issueKey, Request $request)
    {
        $data = $request->validate([
            'body' => 'required|string',
        ]);

        $comment = $this->jira->addComment($issueKey, $data['body']);

        return response()->json($comment, Response::HTTP_CREATED);
    }

    // PUT /api/jira/issues/{issueKey}/comments/{commentId}
    public function updateComment(string $issueKey, string $commentId, Request $request)
    {
        $data = $request->validate([
            'body' => 'required|string',
        ]);

        $comment = $this->jira->editComment($issueKey, $commentId, $data['body']);

        return response()->json($comment);
    }
}

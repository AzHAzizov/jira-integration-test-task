<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\JiraController;

Route::prefix('api/jira')->middleware('api')->group(function () {
    Route::post('issues', [JiraController::class, 'createIssue']);
    Route::put('issues/{issueKey}', [JiraController::class, 'updateIssue']);
    Route::post('issues/{issueKey}/comments', [JiraController::class, 'addComment']);
    Route::put('issues/{issueKey}/comments/{commentId}', [JiraController::class, 'updateComment']);
});

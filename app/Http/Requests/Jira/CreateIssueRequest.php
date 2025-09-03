<?php

namespace App\Http\Requests\Jira;

use Illuminate\Foundation\Http\FormRequest;

class CreateIssueRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'projectKey'  => 'required|string',
            'summary'     => 'required|string|max:255',
            'description' => 'nullable|string',
            'issueType'   => 'nullable|string',
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $task = $this->route('task');
        return $this->user()->can('update', $task);
    }

    public function rules(): array
    {
        // Staff can only update status
        if ($this->user()->hasRole('staff')) {
            return [
                'status' => ['sometimes', Rule::in(Task::statuses())],
            ];
        }

        // Manager and Admin can update all fields
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', Rule::in(Task::statuses())],
            'department_id' => ['sometimes', 'exists:departments,id'],
            'assigned_to' => ['sometimes', 'exists:users,id'],
            'due_date' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
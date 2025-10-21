<?php

namespace App\Http\Requests;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Task::class);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', Rule::in(Task::statuses())],
            'department_id' => ['required', 'exists:departments,id'],
            'assigned_to' => ['required', 'exists:users,id'],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'assigned_to.exists' => 'The selected user does not exist.',
            'department_id.exists' => 'The selected department does not exist.',
            'due_date.after_or_equal' => 'The due date must be today or a future date.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->has('status')) {
            $this->merge([
                'status' => Task::STATUS_PENDING,
            ]);
        }

        // Automatically set assigned_by to current user
        $this->merge([
            'assigned_by' => $this->user()->id,
        ]);
    }
}

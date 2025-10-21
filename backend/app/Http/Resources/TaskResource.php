<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'department' => new DepartmentResource($this->whenLoaded('department')),
            'assigned_to' => new UserResource($this->whenLoaded('assignee')),
            'assigned_by' => new UserResource($this->whenLoaded('assigner')),
            'due_date' => $this->due_date ? Carbon::parse($this->due_date)->toDateString() : null,
            'is_overdue' => $this->checkIfOverdue(),
            'days_until_due' => $this->calculateDaysUntilDue(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }

    /**
     * Get human-readable status label
     */
    private function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => 'Pending',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            default => ucfirst($this->status),
        };
    }

    /**
     * Check if task is overdue
     */
    private function checkIfOverdue(): bool
    {
        if (!$this->due_date || $this->status === 'completed') {
            return false;
        }

        return Carbon::parse($this->due_date)->isPast();
    }

    /**
     * Calculate days until due
     */
    private function calculateDaysUntilDue(): ?int
    {
        if (!$this->due_date || $this->status === 'completed') {
            return null;
        }

        return now()->diffInDays(Carbon::parse($this->due_date), false);
    }
}
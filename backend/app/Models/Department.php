<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'manager_id',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // Relationships
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    // Helper methods
    public function assignManager(User $user)
    {
        $this->manager_id = $user->id;
        $this->save();
        
        // Ensure the user has the manager role
        if (!$user->hasRole('manager')) {
            $user->assignRole('manager');
        }
        
        return $this;
    }

    public function removeManager()
    {
        $this->manager_id = null;
        $this->save();
        
        return $this;
    }

    public function getMembersCount()
    {
        return $this->users()->count();
    }

    public function getActiveTasksCount()
    {
        return $this->tasks()->whereIn('status', ['pending', 'in_progress'])->count();
    }

    public function getCompletedTasksCount()
    {
        return $this->tasks()->where('status', 'completed')->count();
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;

    public const STATUS_TODO = 'todo';
    public const STATUS_NEW = 'new';
    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_CODE_FINISH = 'code_finish';
    public const STATUS_CODE_REVIEW = 'code_review';
    public const STATUS_REVIEW_DONE = 'review_done';
    public const STATUS_TEST_READY = 'test_ready';
    public const STATUS_TESTING = 'testing';
    public const STATUS_TEST_DONE = 'test_done';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_REOPEN = 'reopen';
    public const STATUS_WAITING_REJECT = 'waiting_reject';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_DONE = 'done';

    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';

    protected $fillable = [
        'project_id',
        'creator_id',
        'assignee_id',
        'title',
        'description',
        'status',
        'priority',
        'deadline',
        'completed_at',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_user')->withTimestamps();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class);
    }

    public function getKanbanTitleAttribute(): string
    {
        $names = $this->relationLoaded('assignees')
            ? $this->assignees->pluck('name')->implode(', ')
            : $this->assignees()->pluck('users.name')->implode(', ');

        if ($names === '' && $this->assignee !== null) {
            $names = (string) $this->assignee->name;
        }

        return $names !== '' ? $this->title . ' [' . $names . ']' : $this->title;
    }
}

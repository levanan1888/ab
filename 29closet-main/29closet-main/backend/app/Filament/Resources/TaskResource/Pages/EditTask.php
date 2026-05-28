<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use App\Models\ActivityLog;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class EditTask extends EditRecord
{
    protected static string $resource = TaskResource::class;
    protected static string $view = 'filament.resources.task-resource.pages.edit-task';

    public string $active_tab = 'history';
    public string $note_content = '';
    public ?int $reply_to_comment_id = null;
    public bool $show_edit_form = false;
    protected array $assigneeIds = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['assignee_ids'] = $this->record->assignees()->pluck('users.id')->toArray();
        return $data;
    }

    public function getTitle(): string
    {
        return (string) ($this->record->title ?? 'Chi tiết công việc');
    }

    public function setActiveTab(string $tab): void
    {
        if (in_array($tab, ['history', 'notes', 'property_changes'], true)) {
            $this->active_tab = $tab;
        }
    }

    public function toggleEditForm(): void
    {
        $this->show_edit_form = ! $this->show_edit_form;
    }

    public function getIsWatching(): bool
    {
        $user_id = Auth::id();
        if ($user_id === null) {
            return false;
        }

        $latest = ActivityLog::query()
            ->where('subject_type', 'task')
            ->where('subject_id', $this->record->id)
            ->where('causer_id', $user_id)
            ->whereIn('action', ['watch', 'unwatch'])
            ->latest()
            ->first();

        return $latest?->action === 'watch';
    }

    public function toggleWatch(): void
    {
        ActivityLog::query()->create([
            'subject_type' => 'task',
            'subject_id' => $this->record->id,
            'action' => $this->getIsWatching() ? 'unwatch' : 'watch',
            'causer_id' => Auth::id(),
            'meta' => [],
        ]);
    }

    public function addNote(): void
    {
        $content = trim($this->note_content);
        if ($content === '') {
            Notification::make()->title('Nội dung bình luận không được để trống')->danger()->send();
            return;
        }

        TaskComment::query()->create([
            'task_id' => $this->record->id,
            'user_id' => (int) Auth::id(),
            'parent_id' => $this->reply_to_comment_id,
            'content' => $content,
        ]);

        ActivityLog::query()->create([
            'subject_type' => 'task',
            'subject_id' => $this->record->id,
            'action' => 'commented',
            'causer_id' => Auth::id(),
            'meta' => ['content' => $content, 'task_title' => $this->record->title, 'parent_id' => $this->reply_to_comment_id],
        ]);

        $this->note_content = '';
        $this->reply_to_comment_id = null;
    }

    public function setReplyTo(int $commentId): void
    {
        $this->reply_to_comment_id = $commentId;
    }

    public function getTaskComments(): Collection
    {
        return $this->record->comments()->whereNull('parent_id')->with(['user:id,name', 'replies.user:id,name'])->latest()->get();
    }

    public function getTaskHistories(): Collection
    {
        return ActivityLog::query()
            ->where('subject_type', 'task')
            ->where('subject_id', $this->record->id)
            ->with('causer:id,name')
            ->latest()
            ->get();
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->assigneeIds = array_values(array_unique(array_filter((array) ($data['assignee_ids'] ?? []))));

        $user = Auth::user();

        if ($user !== null && $user->role === User::ROLE_MEMBER) {
            return [
                'project_id' => $this->record->project_id,
                'title' => $this->record->title,
                'description' => $this->record->description,
                'assignee_id' => $this->record->assignee_id,
                'status' => $data['status'] ?? $this->record->status,
                'priority' => $this->record->priority,
                'deadline' => $this->record->deadline,
                'creator_id' => $this->record->creator_id,
                'completed_at' => ($data['status'] ?? $this->record->status) === Task::STATUS_DONE ? now() : null,
            ];
        }

        $data['completed_at'] = ($data['status'] ?? null) === Task::STATUS_DONE ? now() : null;

        return $data;
    }

    protected function afterSave(): void
    {
        if (count($this->assigneeIds) > 0) {
            $this->record->assignees()->sync($this->assigneeIds);
        } elseif ($this->record->assignee_id !== null) {
            $this->record->assignees()->sync([$this->record->assignee_id]);
        }

        ActivityLog::query()->create([
            'subject_type' => 'task',
            'subject_id' => $this->record->id,
            'action' => 'updated',
            'causer_id' => Auth::id(),
            'meta' => ['status' => $this->record->status, 'task_title' => $this->record->title],
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

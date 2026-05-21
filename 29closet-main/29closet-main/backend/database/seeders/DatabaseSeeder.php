<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $leader = User::query()->updateOrCreate(
            ['email' => 'leader@example.com'],
            [
                'name' => 'Quản lý dự án',
                'password' => Hash::make('password'),
                'role' => User::ROLE_LEADER,
            ]
        );

        $member = User::query()->updateOrCreate(
            ['email' => 'member@example.com'],
            [
                'name' => 'Thành viên dự án',
                'password' => Hash::make('password'),
                'role' => User::ROLE_MEMBER,
            ]
        );

        $project = Project::query()->create([
            'name' => 'Dự án mẫu',
            'description' => 'Dự án quản lý công việc mẫu',
            'owner_id' => $leader->id,
            'is_active' => true,
        ]);

        $project->members()->sync([
            $leader->id => ['role_in_project' => 'leader'],
            $member->id => ['role_in_project' => 'member'],
        ]);

        $task = Task::query()->create([
            'project_id' => $project->id,
            'creator_id' => $leader->id,
            'assignee_id' => $member->id,
            'title' => 'Công việc đầu tiên',
            'description' => 'Chuẩn bị luồng quản lý task đầu tiên',
            'status' => Task::STATUS_TODO,
            'priority' => Task::PRIORITY_HIGH,
            'deadline' => now()->addDays(3),
        ]);

        TaskComment::query()->create([
            'task_id' => $task->id,
            'user_id' => $leader->id,
            'content' => 'Vui lòng cập nhật trạng thái hằng ngày.',
        ]);
    }
}

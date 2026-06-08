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
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Quản trị hệ thống',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
            ]
        );

        $leaders = collect([
            ['email' => 'leader1@example.com', 'name' => 'Leader Thiết kế'],
            ['email' => 'leader2@example.com', 'name' => 'Leader Backend'],
        ])->map(function (array $data): User {
            return User::query()->updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'role' => User::ROLE_LEADER,
                ]
            );
        });

        $members = collect([
            ['email' => 'member1@example.com', 'name' => 'Member A'],
            ['email' => 'member2@example.com', 'name' => 'Member B'],
            ['email' => 'member3@example.com', 'name' => 'Member C'],
            ['email' => 'member4@example.com', 'name' => 'Member D'],
            ['email' => 'member5@example.com', 'name' => 'Member E'],
        ])->map(function (array $data): User {
            return User::query()->updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'role' => User::ROLE_MEMBER,
                ]
            );
        });

        $projects = collect([
            [
                'name' => 'Dự án mẫu 1',
                'description' => 'Quản lý công việc nội bộ',
                'leader' => $leaders[0],
                'members' => [$members[0], $members[1], $members[2]],
            ],
            [
                'name' => 'Dự án mẫu 2',
                'description' => 'Xây dựng module backend',
                'leader' => $leaders[1],
                'members' => [$members[1], $members[3]],
            ],
            [
                'name' => 'Dự án mẫu 3',
                'description' => 'Triển khai dashboard theo dõi',
                'leader' => $leaders[0],
                'members' => [$members[2], $members[4]],
            ],
        ])->map(function (array $data) use ($admin): Project {
            return Project::query()->create([
                'name' => $data['name'],
                'description' => $data['description'],
                'owner_id' => $admin->id,
                'is_active' => true,
            ]);
        });

        $projectMemberships = [
            $projects[0]->id => [
                $leaders[0]->id => ['role_in_project' => 'leader'],
                $members[0]->id => ['role_in_project' => 'member'],
                $members[1]->id => ['role_in_project' => 'member'],
                $members[2]->id => ['role_in_project' => 'member'],
            ],
            $projects[1]->id => [
                $leaders[1]->id => ['role_in_project' => 'leader'],
                $members[1]->id => ['role_in_project' => 'member'],
                $members[3]->id => ['role_in_project' => 'member'],
            ],
            $projects[2]->id => [
                $leaders[0]->id => ['role_in_project' => 'leader'],
                $members[2]->id => ['role_in_project' => 'member'],
                $members[4]->id => ['role_in_project' => 'member'],
            ],
        ];

        foreach ($projectMemberships as $projectId => $memberships) {
            Project::query()->findOrFail($projectId)->members()->sync($memberships);
        }

        $tasks = [
            [
                'project' => $projects[0],
                'creator' => $leaders[0],
                'assignee' => $members[0],
                'title' => 'Thiết kế wireframe',
                'description' => 'Phác thảo giao diện dashboard và màn danh sách.',
                'status' => Task::STATUS_IN_PROGRESS,
                'priority' => Task::PRIORITY_HIGH,
                'deadline' => now()->addDays(2),
                'assignees' => [$members[0]->id, $members[1]->id],
                'comments' => [
                    ['user' => $leaders[0], 'content' => 'Ưu tiên chốt wireframe trước cuối tuần.'],
                    ['user' => $members[0], 'content' => 'Em đã dựng bản nháp đầu tiên.'],
                ],
            ],
            [
                'project' => $projects[0],
                'creator' => $leaders[0],
                'assignee' => $members[1],
                'title' => 'API đăng nhập',
                'description' => 'Hoàn thiện luồng login và phân quyền role.',
                'status' => Task::STATUS_REVIEW_DONE,
                'priority' => Task::PRIORITY_MEDIUM,
                'deadline' => now()->addDays(4),
                'assignees' => [$members[1]->id, $members[2]->id],
                'comments' => [
                    ['user' => $leaders[0], 'content' => 'Review lại validation giúp anh.'],
                ],
            ],
            [
                'project' => $projects[1],
                'creator' => $leaders[1],
                'assignee' => $members[3],
                'title' => 'Tối ưu query báo cáo',
                'description' => 'Giảm số lượng query khi load thống kê.',
                'status' => Task::STATUS_TESTING,
                'priority' => Task::PRIORITY_HIGH,
                'deadline' => now()->addDays(5),
                'assignees' => [$members[1]->id, $members[3]->id],
                'comments' => [
                    ['user' => $leaders[1], 'content' => 'Chạy benchmark trước khi merge.'],
                    ['user' => $members[3], 'content' => 'Em đang kiểm tra lại index.'],
                ],
            ],
            [
                'project' => $projects[2],
                'creator' => $leaders[0],
                'assignee' => $members[4],
                'title' => 'Bổ sung thống kê sprint',
                'description' => 'Hiển thị số task theo trạng thái và độ ưu tiên.',
                'status' => Task::STATUS_NEW,
                'priority' => Task::PRIORITY_LOW,
                'deadline' => now()->addDays(7),
                'assignees' => [$members[2]->id, $members[4]->id],
                'comments' => [
                    ['user' => $members[4], 'content' => 'Em sẽ nhận task này sau khi xong việc hiện tại.'],
                ],
            ],
        ];

        foreach ($tasks as $taskData) {
            $task = Task::query()->create([
                'project_id' => $taskData['project']->id,
                'creator_id' => $taskData['creator']->id,
                'assignee_id' => $taskData['assignee']->id,
                'title' => $taskData['title'],
                'description' => $taskData['description'],
                'status' => $taskData['status'],
                'priority' => $taskData['priority'],
                'deadline' => $taskData['deadline'],
                'completed_at' => in_array($taskData['status'], [Task::STATUS_DONE, Task::STATUS_CLOSED, Task::STATUS_TEST_DONE], true) ? now() : null,
            ]);

            $task->assignees()->sync($taskData['assignees']);

            foreach ($taskData['comments'] as $commentData) {
                TaskComment::query()->create([
                    'task_id' => $task->id,
                    'user_id' => $commentData['user']->id,
                    'content' => $commentData['content'],
                ]);
            }
        }
    }
}

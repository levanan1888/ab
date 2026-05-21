<x-filament-panels::page
    @class([
        'fi-resource-list-records-page',
        'fi-resource-' . str_replace('/', '-', $this->getResource()::getSlug()),
    ])
>
    <style>
        .redmine-projects {
            color: #333;
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        }

        .redmine-filter-bar {
            border-bottom: 1px solid #d7d7d7;
            border-top: 1px solid #eeeeee;
            font-size: 13px;
            padding: 10px 0 12px;
        }

        .redmine-filter-row {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .redmine-select {
            border: 1px solid #cfcfcf;
            border-radius: 3px;
            font-size: 13px;
            min-width: 150px;
            padding: 4px 8px;
        }

        .redmine-actions {
            color: #1683d8;
            display: flex;
            gap: 14px;
            margin: 14px 0 18px;
        }

        .redmine-grid {
            column-count: 3;
            column-gap: 12px;
        }

        .redmine-project-card {
            background: #ffffff;
            border: 1px solid #d6d6d6;
            border-radius: 4px;
            break-inside: avoid;
            margin: 0 0 14px;
            padding: 14px 18px;
        }

        .redmine-project-title {
            color: #1683d8;
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .redmine-project-title a,
        .redmine-task-link {
            color: #1683d8;
            text-decoration: none;
        }

        .redmine-project-title a:hover,
        .redmine-task-link:hover {
            text-decoration: underline;
        }

        .redmine-project-meta {
            color: #666;
            font-size: 12px;
            margin-bottom: 8px;
        }

        .redmine-task-list {
            border-left: 3px solid #dedede;
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-left: 2px;
            padding-left: 14px;
        }

        .redmine-task-description {
            color: #333;
            font-size: 13px;
            margin-top: 2px;
        }

        .redmine-empty {
            color: #777;
            font-size: 13px;
        }

        @media (max-width: 1100px) {
            .redmine-grid {
                column-count: 2;
            }
        }

        @media (max-width: 700px) {
            .redmine-grid {
                column-count: 1;
            }
        }
    </style>

    <div class="redmine-projects">
        <div class="redmine-filter-bar">
            <div class="redmine-filter-row">
                <span>▾ Bộ lọc</span>
                <label for="project_status">Trạng thái</label>
                <select id="project_status" class="redmine-select" wire:model.live="project_status">
                    <option value="active">Đang hoạt động</option>
                    <option value="inactive">Ngừng hoạt động</option>
                    <option value="all">Tất cả</option>
                </select>
            </div>
        </div>

        <div class="redmine-actions">
            <span>✓ Áp dụng</span>
            <span>↻ Xóa</span>
            <span>💾 Lưu truy vấn</span>
        </div>

        <div class="redmine-grid">
            @forelse ($this->getProjectCards() as $project)
                <section class="redmine-project-card">
                    <h2 class="redmine-project-title">
                        <a href="<?php echo e($this->getResource()::getUrl('edit', ['record' => $project])); ?>">
                            [<?php echo e($project->name); ?>]
                        </a>
                    </h2>

                    <div class="redmine-project-meta">
                        Quản lý: <?php echo e($project->owner?->name ?? 'Chưa có'); ?> ·
                        Thành viên: <?php echo e($project->members_count); ?> ·
                        Mở: <?php echo e($project->open_tasks_count); ?> ·
                        Đóng: <?php echo e($project->closed_tasks_count); ?>
                    </div>

                    @if (!empty($project->description))
                        <p class="redmine-task-description"><?php echo e($project->description); ?></p>
                    @endif

                    <div class="redmine-task-list">
                        @forelse ($project->tasks as $task)
                            <div>
                                <a class="redmine-task-link" href="<?php echo e(\App\Filament\Resources\TaskResource::getUrl('edit', ['record' => $task])); ?>">
                                    <?php echo e($task->title); ?>
                                </a>
                                <div class="redmine-task-description">
                                    <?php echo e(\Illuminate\Support\Str::limit((string) $task->description, 90)); ?>
                                </div>
                            </div>
                        @empty
                            <span class="redmine-empty">Chưa có công việc.</span>
                        @endforelse
                    </div>
                </section>
            @empty
                <section class="redmine-project-card">
                    <span class="redmine-empty">Không có dự án phù hợp.</span>
                </section>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>

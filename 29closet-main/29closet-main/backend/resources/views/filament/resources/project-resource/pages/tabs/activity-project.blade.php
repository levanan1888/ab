<x-filament-panels::page>
    <style>
        .redmine-overview { color: #333; font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; }
        .redmine-tabs { align-items: center; background: #3b4650; display: flex; flex-wrap: wrap; margin-bottom: 14px; padding: 0 10px; }
        .redmine-tab { color: #cfd6de; font-size: 14px; font-weight: 600; padding: 12px 10px; text-decoration: none; }
        .redmine-tab.active, .redmine-tab:hover { color: #fff; }
        .redmine-box { background: #fff; border: 1px solid #d6d6d6; border-radius: 4px; padding: 16px; }
    </style>

    <div class="redmine-overview">
        @php($tabs = $this->getProjectTabUrls())
        @include('filament.resources.project-resource.pages.tabs._tabs', ['tabs' => $tabs, 'activeTab' => 'activity'])

        <section class="redmine-box">
            <h2 style="font-size: 16px; font-weight: 700; margin-bottom: 8px;">Activity</h2>
            @forelse ($this->getActivities() as $item)
                @php
                    $taskTitle = data_get($item->meta, 'task_title');
                    $projectName = data_get($item->meta, 'project_name', $record->name);
                    $memberName = data_get($item->meta, 'member_name');
                    $roleInProject = data_get($item->meta, 'role_in_project');
                    $actionLabel = match ($item->action) {
                        'created' => 'đã tạo',
                        'updated' => 'đã cập nhật',
                        'commented' => 'đã bình luận vào',
                        'watch' => 'đã theo dõi',
                        'unwatch' => 'đã bỏ theo dõi',
                        'status_changed' => 'đã đổi trạng thái của',
                        'member_attached' => 'đã thêm',
                        'member_detached' => 'đã xóa',
                        'member_role_updated' => 'đã cập nhật vai trò của',
                        default => 'đã thực hiện',
                    };
                @endphp
                <article style="border-top:1px solid #ececec;padding:10px 0;">
                    <div style="font-size:14px;">
                        <strong>{{ $item->causer->name ?? 'System' }}</strong>
                        <strong>{{ $actionLabel }}</strong>
                        @if ($item->subject_type === 'project' && !empty($memberName))
                            <strong>{{ $memberName }}</strong>
                            @if ($item->action === 'member_attached')
                                vào nhóm <strong>{{ $projectName }}</strong>
                                @if (!empty($roleInProject))
                                    với vai trò <strong>{{ $roleInProject }}</strong>
                                @endif
                            @elseif ($item->action === 'member_detached')
                                khỏi nhóm <strong>{{ $projectName }}</strong>
                            @elseif ($item->action === 'member_role_updated')
                                trong nhóm <strong>{{ $projectName }}</strong>
                                @if (!empty($roleInProject))
                                    thành <strong>{{ $roleInProject }}</strong>
                                @endif
                            @endif
                        @elseif (!empty($taskTitle))
                            task: <strong>{{ $taskTitle }}</strong>
                        @else
                            task #{{ $item->subject_id }}
                        @endif
                    </div>
                    <div style="font-size:12px;color:#6b7280;">
                        {{ $item->created_at?->format('d/m/Y H:i') }}
                    </div>
                    @if (!empty($item->meta))
                        <div style="font-size:12px;color:#6b7280;">{{ json_encode($item->meta, JSON_UNESCAPED_UNICODE) }}</div>
                    @endif
                </article>
            @empty
                <p>Chưa có hoạt động.</p>
            @endforelse
        </section>
    </div>
</x-filament-panels::page>

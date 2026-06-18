<x-filament-panels::page>
    <section style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:16px;">
        @foreach($this->getStatusSummary() as $status)
            <div style="background:{{ $status['background'] }};border:2px solid {{ $status['border'] }};border-radius:14px;padding:14px 16px;box-shadow:0 4px 14px rgba(15,23,42,0.05);">
                <div style="font-size:13px;font-weight:700;color:{{ $status['color'] }};text-transform:uppercase;letter-spacing:0.04em;">{{ $status['label'] }}</div>
                <div style="font-size:28px;font-weight:800;color:{{ $status['color'] }};line-height:1.2;">{{ $status['count'] }}</div>
            </div>
        @endforeach
    </section>

    <section style="background:#fff;border:1px solid #dbe3ee;border-radius:14px;padding:16px;box-shadow:0 6px 24px rgba(15,23,42,0.05);margin-bottom:16px;">
        <h2 style="font-size:18px;font-weight:800;margin-bottom:12px;color:#0f172a;">Bộ lọc nhanh</h2>

        <form method="GET" action="{{ \App\Filament\Pages\MyPage::getUrl() }}" style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;align-items:end;">
            <div>
                <label for="priority" style="display:block;font-size:13px;font-weight:700;color:#334155;margin-bottom:6px;">Ưu tiên</label>
                <select id="priority" name="priority" style="width:100%;padding:10px 12px;border:1px solid #cbd5e1;border-radius:10px;background:#fff;">
                    @foreach($this->getPriorityFilterOptions() as $value => $label)
                        <option value="{{ $value }}" @selected($this->getSelectedPriority() === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status_filter" style="display:block;font-size:13px;font-weight:700;color:#334155;margin-bottom:6px;">Trạng thái</label>
                <select id="status_filter" name="status_filter" style="width:100%;padding:10px 12px;border:1px solid #cbd5e1;border-radius:10px;background:#fff;">
                    @foreach($this->getStatusFilterOptions() as $value => $label)
                        <option value="{{ $value }}" @selected($this->getSelectedStatus() === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="deadline_filter" style="display:block;font-size:13px;font-weight:700;color:#334155;margin-bottom:6px;">Hạn chót</label>
                <select id="deadline_filter" name="deadline_filter" style="width:100%;padding:10px 12px;border:1px solid #cbd5e1;border-radius:10px;background:#fff;">
                    @foreach($this->getDeadlineFilterOptions() as $value => $label)
                        <option value="{{ $value }}" @selected($this->getSelectedDeadline() === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div style="display:flex;gap:8px;">
                <button type="submit" style="padding:10px 14px;border:none;border-radius:10px;background:#2563eb;color:#fff;font-weight:700;cursor:pointer;">
                    Lọc
                </button>
                <a href="{{ $this->getResetFilterUrl() }}" style="display:inline-flex;align-items:center;justify-content:center;padding:10px 14px;border-radius:10px;background:#e2e8f0;color:#334155;font-weight:700;text-decoration:none;">
                    Reset
                </a>
            </div>
        </form>
    </section>

    <section style="background:#fff;border:1px solid #dbe3ee;border-radius:14px;padding:16px;box-shadow:0 6px 24px rgba(15,23,42,0.05);">
        <h2 style="font-size:20px;font-weight:800;margin-bottom:12px;color:#0f172a;">Task được giao cho tôi</h2>

        <table style="width:100%;border-collapse:separate;border-spacing:0 8px;font-size:14px;">
            <thead>
                <tr>
                    <th style="padding:10px 12px;text-align:left;color:#334155;font-size:13px;">Tiêu đề</th>
                    <th style="padding:10px 12px;text-align:left;color:#334155;font-size:13px;">Nhóm</th>
                    <th style="padding:10px 12px;text-align:left;color:#334155;font-size:13px;">Ưu tiên</th>
                    <th style="padding:10px 12px;text-align:left;color:#334155;font-size:13px;">Trạng thái</th>
                    <th style="padding:10px 12px;text-align:left;color:#334155;font-size:13px;">Hạn chót</th>
                </tr>
            </thead>
            <tbody>
                @forelse($this->getMyTasks() as $task)
                    <tr style="{{ $this->getTaskRowStyles($task) }}box-shadow:0 6px 18px rgba(15,23,42,0.06);">
                        <td style="padding:14px 12px;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;border-right:1px solid #e2e8f0;">
                            <a href="{{ \App\Filament\Resources\TaskResource::getUrl('edit', ['record' => $task]) }}" style="color:#1d4ed8;text-decoration:none;font-weight:800;font-size:18px;">
                                {{ $task->title }}
                            </a>
                            <div style="margin-top:6px;font-size:13px;color:#475569;line-height:1.5;">
                                ↳ {{ $task->description ? \Illuminate\Support\Str::limit($task->description, 110) : 'Không có mô tả' }}
                            </div>
                        </td>
                        <td style="padding:14px 12px;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;border-right:1px solid #e2e8f0;">
                            @if($task->project)
                                <a href="{{ \App\Filament\Resources\ProjectResource::getUrl('edit', ['record' => $task->project]) }}" style="color:#2563eb;text-decoration:none;font-weight:700;">
                                    {{ $task->project->name }}
                                </a>
                            @else
                                <span style="color:#64748b;">N/A</span>
                            @endif
                        </td>
                        <td style="padding:14px 12px;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;border-right:1px solid #e2e8f0;">
                            <span style="display:inline-block;padding:7px 12px;border-radius:999px;font-size:13px;letter-spacing:0.03em;{{ $this->getPriorityBadgeStyles($task->priority) }}">
                                {{ $this->getPriorityLabel($task->priority) }}
                            </span>
                        </td>
                        <td style="padding:14px 12px;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;border-right:1px solid #e2e8f0;">
                            <a href="{{ \App\Filament\Resources\TaskResource::getUrl('index', ['tableFilters' => ['status' => ['value' => $task->status]]]) }}" style="text-decoration:none;">
                                <span style="display:inline-block;padding:7px 12px;border-radius:999px;font-size:13px;{{ $this->getStatusBadgeStyles($task->status) }}">
                                    {{ $this->getStatusLabel($task->status) }}
                                </span>
                            </a>
                        </td>
                        <td style="padding:14px 12px;border:1px solid #e2e8f0;">
                            <a href="{{ \App\Filament\Resources\TaskResource::getUrl('edit', ['record' => $task]) }}" style="text-decoration:none;{{ $this->getDeadlineStyles($task) }}">
                                {{ $task->deadline ? $task->deadline->format('d/m/Y H:i') : 'Không có' }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="padding:16px;border:1px solid #e2e8f0;color:#64748b;border-radius:12px;background:#f8fafc;">Bạn chưa có task nào cần xử lý.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    @if(auth()->user()?->role === \App\Models\User::ROLE_LEADER)
        <section style="background:#fff;border:1px solid #dbe3ee;border-radius:14px;padding:16px;margin-top:16px;box-shadow:0 6px 24px rgba(15,23,42,0.05);">
            <h2 style="font-size:20px;font-weight:800;margin-bottom:12px;color:#0f172a;">Task tôi giao cho thành viên</h2>
            <table style="width:100%;border-collapse:collapse;font-size:14px;">
                <thead>
                    <tr style="background:#f8fafc;">
                        <th style="padding:10px;border:1px solid #e2e8f0;text-align:left;">Tiêu đề</th>
                        <th style="padding:10px;border:1px solid #e2e8f0;text-align:left;">Nhóm</th>
                        <th style="padding:10px;border:1px solid #e2e8f0;text-align:left;">Người nhận</th>
                        <th style="padding:10px;border:1px solid #e2e8f0;text-align:left;">Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->getDelegatedTasks() as $task)
                        <tr>
                            <td style="padding:10px;border:1px solid #e2e8f0;">
                                <a href="{{ \App\Filament\Resources\TaskResource::getUrl('edit', ['record' => $task]) }}" style="color:#2563eb;text-decoration:none;font-weight:700;">
                                    {{ $task->title }}
                                </a>
                            </td>
                            <td style="padding:10px;border:1px solid #e2e8f0;">{{ $task->project->name ?? 'N/A' }}</td>
                            <td style="padding:10px;border:1px solid #e2e8f0;">{{ $task->assignee->name ?? 'Chưa gán' }}</td>
                            <td style="padding:10px;border:1px solid #e2e8f0;">
                                <span style="display:inline-block;padding:7px 12px;border-radius:999px;font-size:13px;{{ $this->getStatusBadgeStyles($task->status) }}">
                                    {{ $this->getStatusLabel($task->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="padding:10px;border:1px solid #e2e8f0;color:#64748b;">Bạn chưa giao task nào cho thành viên.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    @endif
</x-filament-panels::page>

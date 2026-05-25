<x-filament-panels::page>
    <section style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;margin-bottom:12px;">
        @foreach($this->getStatusSummary() as $status)
            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:10px 12px;">
                <div style="font-size:12px;color:#6b7280;">{{ $status['label'] }}</div>
                <div style="font-size:20px;font-weight:700;color:{{ $status['color'] }};">{{ $status['count'] }}</div>
            </div>
        @endforeach
    </section>

    <section style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:12px;">
        <h2 style="font-size:18px;font-weight:700;margin-bottom:10px;">Task được giao cho tôi</h2>

        <table style="width:100%;border-collapse:collapse;font-size:14px;">
            <thead>
                <tr style="background:#f3f4f6;">
                    <th style="padding:8px;border:1px solid #e5e7eb;text-align:left;">Tiêu đề</th>
                    <th style="padding:8px;border:1px solid #e5e7eb;text-align:left;">Nhóm</th>
                    <th style="padding:8px;border:1px solid #e5e7eb;text-align:left;">Trạng thái</th>
                    <th style="padding:8px;border:1px solid #e5e7eb;text-align:left;">Hạn chót</th>
                </tr>
            </thead>
            <tbody>
                @forelse($this->getMyTasks() as $task)
                    <tr>
                        <td style="padding:8px;border:1px solid #e5e7eb;">
                            <a href="{{ \App\Filament\Resources\TaskResource::getUrl('edit', ['record' => $task]) }}" style="color:#2563eb;text-decoration:none;font-weight:600;">
                                {{ $task->title }}
                            </a>
                            <div style="margin-top:4px;margin-left:14px;font-size:12px;color:#6b7280;">
                                ↳ {{ $task->description ? \Illuminate\Support\Str::limit($task->description, 90) : 'Không có mô tả' }}
                            </div>
                        </td>
                        <td style="padding:8px;border:1px solid #e5e7eb;">
                            @if($task->project)
                                <a href="{{ \App\Filament\Resources\ProjectResource::getUrl('edit', ['record' => $task->project]) }}" style="color:#2563eb;text-decoration:none;">
                                    {{ $task->project->name }}
                                </a>
                            @else
                                N/A
                            @endif
                        </td>
                        <td style="padding:8px;border:1px solid #e5e7eb;">
                            <a href="{{ \App\Filament\Resources\TaskResource::getUrl('index', ['tableFilters' => ['status' => ['value' => $task->status]]]) }}" style="color:#2563eb;text-decoration:none;">
                                <span style="display:inline-block;padding:2px 8px;border-radius:999px;background:#f3f4f6;">
                                    {{ $this->getStatusLabel($task->status) }}
                                </span>
                            </a>
                        </td>
                        <td style="padding:8px;border:1px solid #e5e7eb;">
                            <a href="{{ \App\Filament\Resources\TaskResource::getUrl('edit', ['record' => $task]) }}" style="color:#111827;text-decoration:none;">
                                {{ $task->deadline ? $task->deadline->format('d/m/Y H:i') : 'Không có' }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="padding:10px;border:1px solid #e5e7eb;color:#6b7280;">Bạn chưa có task nào cần xử lý.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    @if(auth()->user()?->role === \App\Models\User::ROLE_LEADER)
        <section style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:12px;margin-top:12px;">
            <h2 style="font-size:18px;font-weight:700;margin-bottom:10px;">Task tôi giao cho thành viên</h2>
            <table style="width:100%;border-collapse:collapse;font-size:14px;">
                <thead>
                    <tr style="background:#f3f4f6;">
                        <th style="padding:8px;border:1px solid #e5e7eb;text-align:left;">Tiêu đề</th>
                        <th style="padding:8px;border:1px solid #e5e7eb;text-align:left;">Nhóm</th>
                        <th style="padding:8px;border:1px solid #e5e7eb;text-align:left;">Người nhận</th>
                        <th style="padding:8px;border:1px solid #e5e7eb;text-align:left;">Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->getDelegatedTasks() as $task)
                        <tr>
                            <td style="padding:8px;border:1px solid #e5e7eb;">
                                <a href="{{ \App\Filament\Resources\TaskResource::getUrl('edit', ['record' => $task]) }}" style="color:#2563eb;text-decoration:none;">
                                    {{ $task->title }}
                                </a>
                            </td>
                            <td style="padding:8px;border:1px solid #e5e7eb;">{{ $task->project->name ?? 'N/A' }}</td>
                            <td style="padding:8px;border:1px solid #e5e7eb;">{{ $task->assignee->name ?? 'Chưa gán' }}</td>
                            <td style="padding:8px;border:1px solid #e5e7eb;">{{ $this->getStatusLabel($task->status) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="padding:10px;border:1px solid #e5e7eb;color:#6b7280;">Bạn chưa giao task nào cho thành viên.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    @endif
</x-filament-panels::page>

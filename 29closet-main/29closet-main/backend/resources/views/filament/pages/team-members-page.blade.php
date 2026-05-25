<x-filament-panels::page>
    <section style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:12px;">
        <div style="display:flex;gap:10px;align-items:center;margin-bottom:12px;">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Tìm tên hoặc email..." style="min-width:260px;border:1px solid #d1d5db;border-radius:8px;padding:8px 10px;" />
            <select wire:model.live="group_id" style="min-width:240px;border:1px solid #d1d5db;border-radius:8px;padding:8px 10px;">
                <option value="">Tất cả nhóm</option>
                @foreach($this->getGroupOptions() as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>

        <table style="width:100%;border-collapse:collapse;font-size:14px;">
            <thead>
                <tr style="background:#f3f4f6;">
                    <th style="padding:8px;border:1px solid #e5e7eb;text-align:left;">Thành viên</th>
                    <th style="padding:8px;border:1px solid #e5e7eb;text-align:left;">Email</th>
                    <th style="padding:8px;border:1px solid #e5e7eb;text-align:left;">Thuộc nhóm</th>
                </tr>
            </thead>
            <tbody>
                @forelse($this->getUsersWithTeams() as $user)
                    <tr>
                        <td style="padding:8px;border:1px solid #e5e7eb;">
                            <a href="{{ \App\Filament\Pages\UserProfilePage::getUrl(['user' => $user->id]) }}" style="color:#2563eb;text-decoration:none;font-weight:600;">
                                {{ $user->name }}
                            </a>
                        </td>
                        <td style="padding:8px;border:1px solid #e5e7eb;">{{ $user->email }}</td>
                        <td style="padding:8px;border:1px solid #e5e7eb;">
                            @forelse($user->projects as $project)
                                <a href="{{ \App\Filament\Resources\ProjectResource::getUrl('edit', ['record' => $project]) }}" style="color:#2563eb;text-decoration:none;display:inline-block;margin-right:8px;">
                                    {{ $project->name }}
                                </a>
                            @empty
                                <span style="color:#6b7280;">Chưa thuộc nhóm nào</span>
                            @endforelse
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" style="padding:10px;border:1px solid #e5e7eb;color:#6b7280;">Không có dữ liệu thành viên phù hợp.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
</x-filament-panels::page>

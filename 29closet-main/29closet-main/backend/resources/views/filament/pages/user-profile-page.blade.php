<x-filament-panels::page>
    <div class="space-y-6 max-w-4xl">
        <x-filament::section heading="Thông tin thành viên">
            <x-filament-panels::form wire:submit="save">
                {{ $this->form }}
                <x-filament::button type="submit">
                    Lưu thông tin
                </x-filament::button>
            </x-filament-panels::form>
        </x-filament::section>

        <x-filament::section heading="Nhóm đang tham gia">
            <div class="flex flex-wrap gap-2">
                @forelse($this->userRecord->projects as $project)
                    <a href="{{ \App\Filament\Resources\ProjectResource::getUrl('edit', ['record' => $project]) }}" class="fi-badge fi-color-primary">
                        {{ $project->name }}
                    </a>
                @empty
                    <span class="text-sm text-gray-500">Chưa thuộc nhóm nào</span>
                @endforelse
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>

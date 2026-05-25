<x-filament-panels::page
    @class([
        'fi-resource-edit-record-page',
        'fi-resource-' . str_replace('/', '-', $this->getResource()::getSlug()),
        'fi-resource-record-' . $record->getKey(),
    ])
>
    <style>
        .fi-resource-edit-record-page {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }

        .fi-resource-edit-record-page .fi-page-content,
        .fi-resource-edit-record-page .fi-main,
        .fi-resource-edit-record-page .fi-section-content {
            max-width: 100% !important;
            width: 100% !important;
        }

        .rm-task-wrap { font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; color: #1f2937; max-width: 100%; width: 100%; }
        .rm-tabs { display: flex; gap: 0; border-bottom: 1px solid #d6d6d6; margin-bottom: 12px; }
        .rm-tab { background: #efefef; border: 1px solid #d6d6d6; border-bottom: 0; color: #666; cursor: pointer; font-size: 13px; font-weight: 600; margin-right: 4px; padding: 7px 12px; }
        .rm-tab.active { background: #fff; color: #111; }
        .rm-main-panel { border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 16px; background: #ffffff; }
        .rm-panel { border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; margin-bottom: 14px; background: #fff; }
        .rm-history-item { border-top: 1px solid #e6e6e6; padding: 10px 0; }
        .rm-history-item:first-child { border-top: 0; padding-top: 0; }
        .rm-meta { color: #777; font-size: 12px; }
        .rm-label { color: #6b7280; font-size: 13px; margin-right: 8px; min-width: 120px; display: inline-block; }
        .rm-note-box { min-height: 100px; width: 100%; border: 1px solid #cfcfcf; border-radius: 3px; padding: 8px; font-size: 13px; }
        .rm-btn { background: #1683d8; color: #fff; border: 0; border-radius: 4px; padding: 8px 12px; font-size: 13px; font-weight: 600; }
        .rm-summary { display: grid; gap: 18px; grid-template-columns: repeat(2, minmax(0, 1fr)); margin: 14px 0 14px; }
        .rm-card { border: 0; border-radius: 0; padding: 0; }
        .rm-title { font-size: 22px; font-weight: 700; margin-bottom: 6px; color: #111827; }
        .rm-task-name { font-size: 30px; font-weight: 700; margin: 2px 0 8px; color: #111827; line-height: 1.2; }
        .rm-desc { border-top: 1px solid #e5e7eb; margin-top: 14px; padding-top: 12px; }
        .rm-desc-title { font-weight: 700; margin-bottom: 8px; }
        .rm-edit-wrap { border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; background: #fff; margin-top: 16px; }
        .rm-edit-title { font-size: 16px; font-weight: 700; margin-bottom: 10px; color: #111827; }
        .rm-actions { display: flex; gap: 10px; justify-content: flex-end; margin-bottom: 10px; }
        .rm-action-link { color: #0a66c2; cursor: pointer; font-size: 14px; text-decoration: none; background: transparent; border: 0; padding: 0; }
        .rm-action-link:hover { text-decoration: underline; }
        @@media (max-width: 900px) { .rm-summary { grid-template-columns: 1fr; } }
    </style>

	    <div class="rm-task-wrap">
	        <div class="rm-actions">
            <button type="button" class="rm-action-link" wire:click="toggleEditForm">✏️ Edit</button>
            <button type="button" class="rm-action-link" wire:click="toggleWatch">
                {{ $this->getIsWatching() ? '★ Unwatch' : '☆ Watch' }}
            </button>
            <span class="rm-action-link">•••</span>
	        </div>

        <section class="rm-main-panel">
            <div class="rm-title">Task #{{ $record->id }}</div>
            <div class="rm-task-name">{{ $record->title }}</div>
            <div class="rm-meta">Added by {{ $record->creator?->name ?? 'N/A' }} · Updated {{ $record->updated_at?->diffForHumans() }}</div>

            <div class="rm-summary">
                <section class="rm-card">
                    <div><span class="rm-label">Nhóm:</span>{{ $record->project?->name ?? 'N/A' }}</div>
                    <div><span class="rm-label">Người thực hiện:</span>{{ $record->assignee?->name ?? 'Chưa gán' }}</div>
                </section>
                <section class="rm-card">
                    <div><span class="rm-label">Trạng thái:</span>{{ $record->status }}</div>
                    <div><span class="rm-label">Ưu tiên:</span>{{ $record->priority }}</div>
                    <div><span class="rm-label">Hạn chót:</span>{{ optional($record->deadline)?->format('d/m/Y H:i') ?? 'Không có' }}</div>
                </section>
            </div>

            <div class="rm-desc">
                <div class="rm-desc-title">Description</div>
                <div>{{ $record->description ?: 'Chưa có mô tả.' }}</div>
            </div>
	        </section>

        <div class="rm-tabs">
            <button type="button" class="rm-tab {{ $active_tab === 'history' ? 'active' : '' }}" wire:click="setActiveTab('history')">History</button>
            <button type="button" class="rm-tab {{ $active_tab === 'notes' ? 'active' : '' }}" wire:click="setActiveTab('notes')">Notes</button>
            <button type="button" class="rm-tab {{ $active_tab === 'property_changes' ? 'active' : '' }}" wire:click="setActiveTab('property_changes')">Property changes</button>
        </div>

	        <section class="rm-panel">
	            @if ($active_tab === 'history')
                @forelse ($this->getTaskHistories() as $item)
                    <article class="rm-history-item">
                        <div><strong>{{ $item->causer?->name ?? 'System' }}</strong> {{ $item->action }}</div>
                        <div class="rm-meta">{{ $item->created_at?->diffForHumans() }} ({{ $item->created_at?->format('d/m/Y H:i') }})</div>
                    </article>
	                @empty
	                    <div class="rm-meta">Chưa có lịch sử thay đổi.</div>
	                @endforelse
            @elseif ($active_tab === 'notes')
                <div style="margin-bottom: 10px;">
                    <textarea class="rm-note-box" wire:model.defer="note_content" placeholder="Nhập ghi chú..."></textarea>
                </div>
                <button type="button" class="rm-btn" wire:click="addNote">Thêm ghi chú</button>

	                <div style="margin-top: 14px;">
                    @forelse ($this->getTaskComments() as $comment)
                        <article class="rm-history-item">
                            <div><strong>{{ $comment->user?->name ?? 'N/A' }}</strong></div>
                            <div>{{ $comment->content }}</div>
                            <div class="rm-meta">{{ $comment->created_at?->diffForHumans() }}</div>
                        </article>
	                    @empty
	                        <div class="rm-meta">Chưa có ghi chú.</div>
	                    @endforelse
	                </div>
            @else
                @forelse ($this->getTaskHistories() as $item)
                    <article class="rm-history-item">
                        <div><strong>{{ $item->action }}</strong></div>
                        <div class="rm-meta">{{ json_encode($item->meta, JSON_UNESCAPED_UNICODE) ?: '{}' }}</div>
                    </article>
                @empty
                    <div class="rm-meta">Chưa có thay đổi thuộc tính.</div>
                @endforelse
            @endif
        </section>
    </div>

    @capture($form)
        <x-filament-panels::form
            id="form"
            :wire:key="$this->getId() . '.forms.' . $this->getFormStatePath()"
            wire:submit="save"
        >
            {{ $this->form }}

            <x-filament-panels::form.actions
                :actions="$this->getCachedFormActions()"
                :full-width="$this->hasFullWidthFormActions()"
            />
        </x-filament-panels::form>
    @endcapture

    @if ($show_edit_form)
        <section class="rm-edit-wrap">
            <h2 class="rm-edit-title">Chỉnh sửa task</h2>
            {{ $form() }}
        </section>
    @endif

    <x-filament-panels::page.unsaved-data-changes-alert />
</x-filament-panels::page>

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
                <article style="border-top:1px solid #ececec;padding:10px 0;">
                    <div style="font-size:14px;">
                        <strong>{{ $item->causer->name ?? 'System' }}</strong>
                        đã <strong>{{ $item->action }}</strong> task #{{ $item->subject_id }}
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

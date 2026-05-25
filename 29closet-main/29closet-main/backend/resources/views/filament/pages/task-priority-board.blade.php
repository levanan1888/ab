<x-filament-panels::page>
    @php($groups = $this->getTasksByPriority())

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

    <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;">
        @foreach ([['key' => 'high', 'label' => 'Ưu tiên cao', 'color' => '#ef4444'], ['key' => 'medium', 'label' => 'Ưu tiên trung bình', 'color' => '#f59e0b'], ['key' => 'low', 'label' => 'Ưu tiên thấp', 'color' => '#10b981']] as $col)
            <section style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:10px;">
                <h3 style="font-size:15px;font-weight:700;color:{{ $col['color'] }};margin-bottom:8px;">{{ $col['label'] }}</h3>
                <div class="priority-column" data-priority="{{ $col['key'] }}" style="min-height:280px;display:flex;flex-direction:column;gap:8px;">
                    @foreach ($groups[$col['key']] as $task)
                        <article data-task-id="{{ $task->id }}" style="border:1px solid #e5e7eb;border-radius:8px;padding:8px;background:#f9fafb;cursor:grab;">
                            <div style="font-weight:600;">
                                <a href="{{ \App\Filament\Resources\TaskResource::getUrl('edit', ['record' => $task]) }}" style="color:#2563eb;text-decoration:none;">
                                    {{ $task->title }}
                                </a>
                            </div>
                            <div style="font-size:12px;color:#6b7280;margin-top:4px;">Nhóm: {{ $task->project->name ?? 'N/A' }}</div>
                            <div style="font-size:12px;color:#6b7280;">Assignee: {{ $task->assignee->name ?? 'Chưa gán' }}</div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>

    <script>
        document.querySelectorAll('.priority-column').forEach((el) => {
            new Sortable(el, {
                group: 'priority-board',
                animation: 150,
                onAdd: function (evt) {
                    const taskId = evt.item.getAttribute('data-task-id');
                    const priority = evt.to.getAttribute('data-priority');
                    if (window.Livewire) {
                        window.Livewire.find('{{ $this->getId() }}').call('updateTaskPriority', parseInt(taskId, 10), priority);
                    }
                },
            });
        });
    </script>
</x-filament-panels::page>

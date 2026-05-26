<x-filament-panels::page
    @class([
        'fi-resource-edit-record-page',
        'fi-resource-' . str_replace('/', '-', $this->getResource()::getSlug()),
        'fi-resource-record-' . $record->getKey(),
    ])
>
    <style>
        .redmine-overview {
            color: #333;
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        }

        .redmine-project-name {
            color: #1f2937;
            font-size: 24px;
            font-weight: 700;
            margin: 0 0 10px;
        }

        .redmine-tabs {
            align-items: center;
            background: #3b4650;
            display: flex;
            flex-wrap: wrap;
            gap: 0;
            margin-bottom: 14px;
            padding: 0 10px;
        }

        .redmine-tab {
            color: #cfd6de;
            display: inline-block;
            font-size: 14px;
            font-weight: 600;
            padding: 12px 10px;
            text-decoration: none;
        }

        .redmine-tab:hover {
            color: #ffffff;
            text-decoration: none;
        }

        .redmine-tab.active {
            color: #ffffff;
            font-weight: 700;
        }

        .redmine-overview-grid {
            display: grid;
            gap: 10px;
            grid-template-columns: minmax(0, 1fr) minmax(320px, 0.95fr);
        }

        .redmine-box {
            background: #fff;
            border: 1px solid #d6d6d6;
            border-radius: 4px;
            padding: 16px;
        }

        .redmine-box-title {
            color: #333;
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .redmine-issue-table {
            border-collapse: collapse;
            font-size: 13px;
            width: 100%;
        }

        .redmine-issue-table th,
        .redmine-issue-table td {
            border: 1px solid #d6d6d6;
            padding: 7px 9px;
            text-align: center;
        }

        .redmine-issue-table th {
            background: #eeeeee;
            font-weight: 700;
        }

        .redmine-issue-table td:first-child {
            color: #1683d8;
            text-align: left;
        }

        .redmine-link {
            color: #1683d8;
            text-decoration: none;
        }

        .redmine-link:hover {
            text-decoration: underline;
        }

        .redmine-side-stack {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .redmine-member-list,
        .redmine-task-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 12px;
            font-size: 13px;
        }

        .redmine-task-row {
            border-left: 3px solid #dedede;
            padding-left: 10px;
            width: 100%;
        }

        .redmine-description {
            color: #555;
            font-size: 13px;
            margin-bottom: 14px;
        }

        @@media (max-width: 900px) {
            .redmine-overview-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="redmine-overview">
        <?php $tabs = $this->getProjectTabUrls(); ?>
        <?php
            $tab = request()->query('tab');
            $activeTab = in_array($tab, ['overview', 'activity', 'dashboard'], true) ? $tab : 'overview';
        ?>
        <?php echo $__env->make('filament.resources.project-resource.pages.tabs._tabs', ['tabs' => $tabs, 'activeTab' => $activeTab], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <?php if (!empty($record->description)): ?>
            <p class="redmine-description"><?php echo e($record->description); ?></p>
        <?php endif; ?>

        <?php if ($activeTab === 'activity'): ?>
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
                    </article>
                @empty
                    <p>Chưa có hoạt động.</p>
                @endforelse
            </section>
        <?php elseif ($activeTab === 'dashboard'): ?>
            @php
                $all = $this->getTaskCounts();
                $high = $this->getTaskCounts(\App\Models\Task::PRIORITY_HIGH);
                $medium = $this->getTaskCounts(\App\Models\Task::PRIORITY_MEDIUM);
                $low = $this->getTaskCounts(\App\Models\Task::PRIORITY_LOW);
                $chartId = 'project-dashboard-chart-' . $record->id;
                $timelineLabels = [];
                $timelineOpen = [];
                $timelineClosed = [];
                $openStatuses = [
                    \App\Models\Task::STATUS_TODO,
                    \App\Models\Task::STATUS_NEW,
                    \App\Models\Task::STATUS_PENDING,
                    \App\Models\Task::STATUS_IN_PROGRESS,
                    \App\Models\Task::STATUS_CODE_FINISH,
                    \App\Models\Task::STATUS_CODE_REVIEW,
                    \App\Models\Task::STATUS_REVIEW_DONE,
                    \App\Models\Task::STATUS_TEST_READY,
                    \App\Models\Task::STATUS_TESTING,
                    \App\Models\Task::STATUS_REOPEN,
                    \App\Models\Task::STATUS_WAITING_REJECT,
                    \App\Models\Task::STATUS_REJECTED,
                ];
                $closedStatuses = [
                    \App\Models\Task::STATUS_TEST_DONE,
                    \App\Models\Task::STATUS_DONE,
                    \App\Models\Task::STATUS_CLOSED,
                ];
                for ($i = 6; $i >= 0; $i--) {
                    $dayStart = now()->startOfDay()->subDays($i);
                    $dayEnd = (clone $dayStart)->endOfDay();
                    $timelineLabels[] = $dayStart->format('d/m');
                    $timelineOpen[] = $record->tasks()->whereBetween('created_at', [$dayStart, $dayEnd])->whereIn('status', $openStatuses)->count();
                    $timelineClosed[] = $record->tasks()->whereBetween('created_at', [$dayStart, $dayEnd])->whereIn('status', $closedStatuses)->count();
                }
            @endphp
            <div class="redmine-overview-grid">
                <section class="redmine-box">
                    <h2 class="redmine-box-title">Chỉ số dự án</h2>
                    <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;">
                        <div style="border:1px solid #e5e7eb;border-radius:6px;padding:10px;">
                            <div style="font-size:12px;color:#6b7280;">Tổng công việc</div>
                            <div style="font-size:22px;font-weight:700;">{{ $all['total'] }}</div>
                        </div>
                        <div style="border:1px solid #e5e7eb;border-radius:6px;padding:10px;">
                            <div style="font-size:12px;color:#6b7280;">Đang mở</div>
                            <div style="font-size:22px;font-weight:700;color:#b45309;">{{ $all['open'] }}</div>
                        </div>
                        <div style="border:1px solid #e5e7eb;border-radius:6px;padding:10px;">
                            <div style="font-size:12px;color:#6b7280;">Đã đóng</div>
                            <div style="font-size:22px;font-weight:700;color:#047857;">{{ $all['closed'] }}</div>
                        </div>
                    </div>
                    <div style="margin-top:16px;">
                        <canvas id="{{ $chartId }}" height="120"></canvas>
                    </div>
                </section>
                <section class="redmine-box">
                    <h2 class="redmine-box-title">Phân bổ công việc</h2>
                    <div style="height:260px;">
                        <canvas id="{{ $chartId }}-pie"></canvas>
                    </div>
                </section>
            </div>
            <section class="redmine-box" style="margin-top:10px;">
                <h2 class="redmine-box-title">Xu hướng 7 ngày</h2>
                <div style="height:260px;">
                    <canvas id="{{ $chartId }}-line"></canvas>
                </div>
            </section>
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                (function () {
                    function renderProjectCharts() {
                    const id = @js($chartId);
                    const canvas = document.getElementById(id);
                    if (!canvas || typeof Chart === 'undefined') return;
                    const old = Chart.getChart(canvas);
                    if (old) old.destroy();
                    const ctx = canvas.getContext('2d');
                    const openGradient = ctx.createLinearGradient(0, 0, 0, 220);
                    openGradient.addColorStop(0, 'rgba(245, 158, 11, 0.95)');
                    openGradient.addColorStop(1, 'rgba(245, 158, 11, 0.55)');
                    const closedGradient = ctx.createLinearGradient(0, 0, 0, 220);
                    closedGradient.addColorStop(0, 'rgba(16, 185, 129, 0.95)');
                    closedGradient.addColorStop(1, 'rgba(16, 185, 129, 0.55)');
                    new Chart(canvas, {
                        type: 'bar',
                        data: {
                            labels: ['Tổng', 'Ưu tiên cao', 'Ưu tiên trung bình', 'Ưu tiên thấp'],
                            datasets: [
                                { label: 'Đang mở', data: [{{ $all['open'] }}, {{ $high['open'] }}, {{ $medium['open'] }}, {{ $low['open'] }}], backgroundColor: openGradient, borderRadius: 10, borderSkipped: false },
                                { label: 'Đã đóng', data: [{{ $all['closed'] }}, {{ $high['closed'] }}, {{ $medium['closed'] }}, {{ $low['closed'] }}], backgroundColor: closedGradient, borderRadius: 10, borderSkipped: false }
                            ],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { labels: { usePointStyle: true, pointStyle: 'circle' } },
                                tooltip: { backgroundColor: '#111827', padding: 10 }
                            },
                            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
                        },
                    });

                    const pieCanvas = document.getElementById(id + '-pie');
                    if (!pieCanvas) return;
                    const oldPie = Chart.getChart(pieCanvas);
                    if (oldPie) oldPie.destroy();
                    new Chart(pieCanvas, {
                        type: 'doughnut',
                        data: {
                            labels: ['Ưu tiên cao', 'Ưu tiên trung bình', 'Ưu tiên thấp'],
                            datasets: [{
                                data: [{{ $high['total'] }}, {{ $medium['total'] }}, {{ $low['total'] }}],
                                backgroundColor: ['#ef4444', '#f59e0b', '#3b82f6'],
                                borderWidth: 0,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '62%',
                            plugins: {
                                legend: { position: 'bottom', labels: { usePointStyle: true, pointStyle: 'circle' } },
                                tooltip: { backgroundColor: '#111827', padding: 10 }
                            }
                        },
                    });

                    const lineCanvas = document.getElementById(id + '-line');
                    if (!lineCanvas) return;
                    const oldLine = Chart.getChart(lineCanvas);
                    if (oldLine) oldLine.destroy();
                    const lineCtx = lineCanvas.getContext('2d');
                    const lineGradientOpen = lineCtx.createLinearGradient(0, 0, 0, 260);
                    lineGradientOpen.addColorStop(0, 'rgba(59, 130, 246, 0.35)');
                    lineGradientOpen.addColorStop(1, 'rgba(59, 130, 246, 0.03)');
                    const lineGradientClosed = lineCtx.createLinearGradient(0, 0, 0, 260);
                    lineGradientClosed.addColorStop(0, 'rgba(16, 185, 129, 0.35)');
                    lineGradientClosed.addColorStop(1, 'rgba(16, 185, 129, 0.03)');
                    new Chart(lineCanvas, {
                        type: 'line',
                        data: {
                            labels: @json($timelineLabels),
                            datasets: [
                                { label: 'Mở mới', data: @json($timelineOpen), borderColor: '#3b82f6', backgroundColor: lineGradientOpen, fill: true, tension: 0.35, pointRadius: 3, pointHoverRadius: 5 },
                                { label: 'Đóng', data: @json($timelineClosed), borderColor: '#10b981', backgroundColor: lineGradientClosed, fill: true, tension: 0.35, pointRadius: 3, pointHoverRadius: 5 },
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { labels: { usePointStyle: true, pointStyle: 'circle' } },
                                tooltip: { backgroundColor: '#111827', padding: 10 }
                            },
                            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                        }
                    });
                    }

                    if (typeof Chart === 'undefined') {
                        window.addEventListener('load', renderProjectCharts);
                    } else {
                        renderProjectCharts();
                    }
                })();
            </script>
        <?php else: ?>
        <div class="redmine-overview-grid">
            <section class="redmine-box">
                <h2 class="redmine-box-title">📊 Theo dõi công việc</h2>
                <table class="redmine-issue-table">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Mở</th>
                            <th>Đóng</th>
                            <th>Tổng</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->getOverviewRows() as $label => $counts)
                            <tr>
                                <td><?php echo e($label); ?></td>
                                <td>
                                    <a class="redmine-link" href="<?php echo e($this->getTaskListUrl($counts['priority'], 'todo')); ?>">
                                        <?php echo e($counts['open']); ?>
                                    </a>
                                </td>
                                <td>
                                    <a class="redmine-link" href="<?php echo e($this->getTaskListUrl($counts['priority'], 'done')); ?>">
                                        <?php echo e($counts['closed']); ?>
                                    </a>
                                </td>
                                <td>
                                    <a class="redmine-link" href="<?php echo e($this->getTaskListUrl($counts['priority'])); ?>">
                                        <?php echo e($counts['total']); ?>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <p class="mt-3 text-sm">
                    <a class="redmine-link" href="<?php echo e($this->getTaskListUrl()); ?>">Xem tất cả công việc</a>
                </p>
            </section>

            <div class="redmine-side-stack">
                <section class="redmine-box">
                    <h2 class="redmine-box-title">👥 Thành viên</h2>
                    <div class="redmine-member-list">
                        <span>Quản lý: <a class="redmine-link"><?php echo e($record->owner?->name ?? 'Chưa có'); ?></a></span>
                        @foreach ($record->members as $member)
                            <a class="redmine-link"><?php echo e($member->name); ?></a>
                        @endforeach
                    </div>
                </section>

                <section class="redmine-box">
                    <h2 class="redmine-box-title">🧩 Công việc gần đây</h2>
                    <div class="redmine-task-list">
                        @forelse ($this->getRecentTasks() as $task)
                            <div class="redmine-task-row">
                                <a class="redmine-link" href="<?php echo e(\App\Filament\Resources\TaskResource::getUrl('edit', ['record' => $task])); ?>">
                                    <?php echo e($task->title); ?>
                                </a>
                                <span> · <?php echo e($task->assignee?->name ?? 'Chưa gán'); ?></span>
                            </div>
                        @empty
                            <span class="text-sm text-gray-500">Chưa có công việc.</span>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>
        <?php endif; ?>
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

    @php
        $relationManagers = $this->getRelationManagers();
        $hasCombinedRelationManagerTabsWithContent = $this->hasCombinedRelationManagerTabsWithContent();
    @endphp

    @if ((! $hasCombinedRelationManagerTabsWithContent) || (! count($relationManagers)))
        {{ $form() }}
    @endif

    @if (count($relationManagers))
        <x-filament-panels::resources.relation-managers
            :active-locale="isset($activeLocale) ? $activeLocale : null"
            :active-manager="$this->activeRelationManager ?? ($hasCombinedRelationManagerTabsWithContent ? null : array_key_first($relationManagers))"
            :content-tab-label="$this->getContentTabLabel()"
            :content-tab-icon="$this->getContentTabIcon()"
            :content-tab-position="$this->getContentTabPosition()"
            :managers="$relationManagers"
            :owner-record="$record"
            :page-class="static::class"
        >
            @if ($hasCombinedRelationManagerTabsWithContent)
                <x-slot name="content">
                    {{ $form() }}
                </x-slot>
            @endif
        </x-filament-panels::resources.relation-managers>
    @endif

    <x-filament-panels::page.unsaved-data-changes-alert />
</x-filament-panels::page>

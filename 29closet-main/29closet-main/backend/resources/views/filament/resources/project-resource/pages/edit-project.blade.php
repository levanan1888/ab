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
        <?php $activeTab = request()->query('tab') === 'activity' ? 'activity' : 'overview'; ?>
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

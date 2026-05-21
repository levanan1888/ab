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

        @media (max-width: 900px) {
            .redmine-overview-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="redmine-overview">
        @if (!empty($record->description))
            <p class="redmine-description"><?php echo e($record->description); ?></p>
        @endif

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
                                <td><?php echo e($counts['open']); ?></td>
                                <td><?php echo e($counts['closed']); ?></td>
                                <td><?php echo e($counts['total']); ?></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <p class="mt-3 text-sm">
                    <a class="redmine-link" href="<?php echo e(\App\Filament\Resources\TaskResource::getUrl('index', ['tableFilters' => ['project_id' => ['value' => $record->id]]])); ?>">Xem tất cả công việc</a>
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

<x-filament-panels::page>
    <style>
        .task-row-high-priority > td {
            background: #ffe4e6 !important;
        }

        .dark .task-row-high-priority > td {
            background: rgba(127, 29, 29, 0.34) !important;
        }
    </style>

    {{ $this->table }}
</x-filament-panels::page>

<x-filament-panels::page>
    @php($data = $this->getChartData())
    @php($total = $data['status']['todo'] + $data['status']['in_progress'] + $data['status']['done'])

    <div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:12px;">
        <section style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:10px 12px;">
            <div style="font-size:12px;color:#6b7280;">Tổng task</div>
            <div style="font-size:20px;font-weight:700;">{{ $total }}</div>
        </section>
        <section style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:10px 12px;">
            <div style="font-size:12px;color:#6b7280;">Chưa làm</div>
            <div style="font-size:20px;font-weight:700;">{{ $data['status']['todo'] }}</div>
        </section>
        <section style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:10px 12px;">
            <div style="font-size:12px;color:#6b7280;">Đang làm</div>
            <div style="font-size:20px;font-weight:700;">{{ $data['status']['in_progress'] }}</div>
        </section>
        <section style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:10px 12px;">
            <div style="font-size:12px;color:#6b7280;">Hoàn thành</div>
            <div style="font-size:20px;font-weight:700;">{{ $data['status']['done'] }}</div>
        </section>
    </div>

    <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;">
        <section style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:14px;">
            <h3 style="font-weight:700;margin-bottom:10px;">Task theo trạng thái</h3>
            <div style="height:220px;max-height:220px;">
                <canvas id="statusChart"></canvas>
            </div>
        </section>

        <section style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:14px;">
            <h3 style="font-weight:700;margin-bottom:10px;">Task theo ưu tiên</h3>
            <div style="height:220px;max-height:220px;">
                <canvas id="priorityChart"></canvas>
            </div>
        </section>

        <section style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:14px;">
            <h3 style="font-weight:700;margin-bottom:10px;">Top nhóm theo số task</h3>
            <div style="height:220px;max-height:220px;">
                <canvas id="projectChart"></canvas>
            </div>
        </section>

        <section style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:14px;">
            <h3 style="font-weight:700;margin-bottom:10px;">Xu hướng trạng thái task (7 ngày)</h3>
            <div style="height:220px;max-height:220px;">
                <canvas id="timelineChart"></canvas>
            </div>
        </section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const chartData = @json($data);

        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: ['Chưa làm', 'Đang làm', 'Hoàn thành'],
                datasets: [{
                    data: [chartData.status.todo, chartData.status.in_progress, chartData.status.done],
                    backgroundColor: ['#9ca3af', '#f59e0b', '#10b981'],
                }]
            },
            options: {responsive: true, maintainAspectRatio: false}
        });

        new Chart(document.getElementById('priorityChart'), {
            type: 'pie',
            data: {
                labels: ['Thấp', 'Trung bình', 'Cao'],
                datasets: [{
                    data: [chartData.priority.low, chartData.priority.medium, chartData.priority.high],
                    backgroundColor: ['#22c55e', '#f59e0b', '#ef4444'],
                }]
            },
            options: {responsive: true, maintainAspectRatio: false}
        });

        new Chart(document.getElementById('projectChart'), {
            type: 'bar',
            data: {
                labels: chartData.projects.labels,
                datasets: [{
                    label: 'Số task',
                    data: chartData.projects.values,
                    backgroundColor: '#3b82f6',
                }]
            },
            options: {responsive: true, maintainAspectRatio: false, scales: {y: {beginAtZero: true}}}
        });

        new Chart(document.getElementById('timelineChart'), {
            type: 'line',
            data: {
                labels: chartData.timeline.labels,
                datasets: [
                    {
                        label: 'Chưa làm',
                        data: chartData.timeline.todo,
                        borderColor: '#9ca3af',
                        backgroundColor: 'rgba(156, 163, 175, 0.15)',
                        tension: 0.35,
                        fill: false,
                    },
                    {
                        label: 'Đang làm',
                        data: chartData.timeline.in_progress,
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, 0.15)',
                        tension: 0.35,
                        fill: false,
                    },
                    {
                        label: 'Hoàn thành',
                        data: chartData.timeline.done,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.15)',
                        tension: 0.35,
                        fill: false,
                    },
                ]
            },
            options: {responsive: true, maintainAspectRatio: false, scales: {y: {beginAtZero: true}}}
        });
    </script>
</x-filament-panels::page>

@extends('layouts.app')

@section('content')
<div id="reports" class="page">
    <div class="max-w-7xl mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Reports & Analytics</h1>
        <p class="text-gray-600 mb-8">Current Sprint: <span class="font-semibold">{{ $activeSprintName }}</span></p>

        <div class="grid lg:grid-cols-2 gap-8 mb-8">
            {{-- Burndown Chart --}}
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h3 class="text-xl font-semibold mb-4 flex items-center">
                    <i class="fas fa-chart-line text-blue-600 mr-2"></i>Burndown Chart
                </h3>
                @if(!empty($burndownChartData))
                    <canvas id="burndownChart" width="400" height="200"></canvas>
                @else
                    <p class="text-center text-gray-500 py-16">No active sprint to display Burndown Chart.</p>
                @endif
            </div>

            {{-- Velocity Chart --}}
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h3 class="text-xl font-semibold mb-4 flex items-center">
                    <i class="fas fa-tachometer-alt text-green-600 mr-2"></i>Velocity Chart
                </h3>
                 @if(!empty($velocityChartData))
                    <canvas id="velocityChart" width="400" height="200"></canvas>
                @else
                    <p class="text-center text-gray-500 py-16">No completed sprints to display Velocity Chart.</p>
                @endif
            </div>
        </div>

        {{-- Team Performance Table --}}
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <h3 class="text-xl font-semibold mb-4 flex items-center">
                <i class="fas fa-user-chart text-purple-600 mr-2"></i>Team Performance (Current Sprint)
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-3 px-4">Team Member</th>
                            <th class="text-left py-3 px-4">Tasks Completed</th>
                            <th class="text-left py-3 px-4">Story Points</th>
                            <th class="text-left py-3 px-4">Efficiency</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($teamPerformance as $member)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-3 px-4 font-medium">{{ $member['name'] }}</td>
                                <td class="py-3 px-4">{{ $member['tasks_completed'] }}</td>
                                <td class="py-3 px-4">{{ $member['story_points'] }}</td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center">
                                        <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                                            <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $member['efficiency'] }}%"></div>
                                        </div>
                                        <span class="text-sm text-gray-600">{{ $member['efficiency'] }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                             <tr>
                                <td colspan="4" class="text-center text-gray-500 py-8">No active sprint to display team performance.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Dữ liệu từ Controller
    const burndownData = @json($burndownChartData);
    const velocityData = @json($velocityChartData);

    // --- Burndown Chart ---
    const burndownCtx = document.getElementById('burndownChart');
    if (burndownCtx && burndownData && burndownData.labels) {
        new Chart(burndownCtx, {
            type: 'line',
            data: {
                labels: burndownData.labels,
                datasets: [{
                    label: 'Actual Remaining',
                    data: burndownData.actualData,
                    borderColor: '#007BFF',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    tension: 0.1,
                    fill: true
                }, {
                    label: 'Ideal Burndown',
                    data: burndownData.idealData,
                    borderColor: '#FFA502',
                    borderDash: [5, 5],
                    backgroundColor: 'transparent',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: { y: { beginAtZero: true, title: { display: true, text: 'Story Points' } } }
            }
        });
    }

    // --- Velocity Chart ---
    const velocityCtx = document.getElementById('velocityChart');
    if (velocityCtx && velocityData && velocityData.labels) {
        new Chart(velocityCtx, {
            type: 'bar',
            data: {
                labels: velocityData.labels,
                datasets: [{
                    label: 'Story Points Completed',
                    data: velocityData.data,
                    backgroundColor: '#2ED573',
                }]
            },
            options: {
                responsive: true,
                scales: { y: { beginAtZero: true, title: { display: true, text: 'Story Points' } } }
            }
        });
    }
});
</script>
@endpush

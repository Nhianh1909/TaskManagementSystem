@extends('layouts.app')

@section('content')
<div id="reports" class="page">
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Reports & Analytics</h1>
                <p class="text-gray-600">Current Sprint: <span class="font-semibold">{{ $activeSprintName }}</span></p>
            </div>

            {{-- Sprint Filter Dropdown --}}
            @if(count($recentSprints) > 0)
            <div class="flex items-center gap-3">
                <label for="sprint-filter" class="text-sm font-medium text-gray-700">Filter by Sprint:</label>
                <select id="sprint-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @foreach($recentSprints as $sprint)
                        <option value="{{ $sprint->id }}" {{ $selectedSprintId == $sprint->id || (!$selectedSprintId && $loop->first) ? 'selected' : '' }}>
                            {{ $sprint->name }}
                            @if($sprint->is_active)
                                <span>(Active)</span>
                            @else
                                <span>(Completed)</span>
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>
            @endif
        </div>

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
                        <tr class="border-b bg-gray-50">
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">User Story</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Team Member</th>
                            <th class="text-center py-3 px-4 font-semibold text-gray-700">Total Subtasks</th>
                            <th class="text-center py-3 px-4 font-semibold text-gray-700">Subtasks Completed</th>
                            <th class="text-center py-3 px-4 font-semibold text-gray-700">Completion Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($teamPerformance && count($teamPerformance) > 0)
                            {{-- Hiển thị User Stories với members --}}
                            @if(isset($teamPerformance['user_stories']) && count($teamPerformance['user_stories']) > 0)
                                @foreach($teamPerformance['user_stories'] as $userStory)
                                    @php
                                        $memberCount = count($userStory['members']);
                                    @endphp

                                    @foreach($userStory['members'] as $index => $member)
                                        <tr class="border-b hover:bg-gray-50 {{ $loop->parent->first && $loop->first ? 'border-t-2 border-purple-200' : '' }}">
                                            {{-- Cột User Story - Chỉ hiển thị ở dòng đầu tiên với rowspan --}}
                                            @if($index === 0)
                                                <td rowspan="{{ $memberCount }}" class="py-3 px-4 font-semibold text-purple-700 bg-purple-50 border-r-2 border-purple-200 align-top">
                                                    <div class="flex items-center">
                                                        <i class="fas fa-tasks text-purple-500 mr-2"></i>
                                                        <span class="text-sm">{{ $userStory['user_story'] }}</span>
                                                    </div>
                                                </td>
                                            @endif

                                            {{-- Cột Team Member --}}
                                            <td class="py-3 px-4 font-medium text-gray-800">
                                                <i class="fas fa-user text-gray-400 mr-2"></i>
                                                {{ $member['name'] }}
                                            </td>

                                            {{-- Cột Total Subtasks --}}
                                            <td class="py-3 px-4 text-center text-gray-700">
                                                {{ $member['total_subtasks'] }}
                                            </td>

                                            {{-- Cột Subtasks Completed --}}
                                            <td class="py-3 px-4 text-center">
                                                <span class="px-2 py-1 rounded-full {{ $member['completed_subtasks'] == $member['total_subtasks'] ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">
                                                    {{ $member['completed_subtasks'] }}
                                                </span>
                                            </td>

                                            {{-- Cột Completion Rate --}}
                                            <td class="py-3 px-4 text-center">
                                                @php
                                                    $rate = $member['completion_rate'];
                                                    if ($rate >= 80) {
                                                        $colorClass = 'bg-green-100 text-green-700';
                                                    } elseif ($rate >= 50) {
                                                        $colorClass = 'bg-yellow-100 text-yellow-700';
                                                    } elseif ($rate > 0) {
                                                        $colorClass = 'bg-orange-100 text-orange-700';
                                                    } else {
                                                        $colorClass = 'bg-gray-100 text-gray-700';
                                                    }
                                                @endphp
                                                <span class="px-3 py-1 rounded-full font-semibold {{ $colorClass }}">
                                                    {{ $rate }}%
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            @endif
                            
                            {{-- Dòng NONE - Hiển thị members không có subtask --}}
                            @if(isset($teamPerformance['members_without_subtasks']) && count($teamPerformance['members_without_subtasks']) > 0)
                                <tr class="border-t-4 border-gray-300">
                                    <td class="py-3 px-4 font-semibold text-gray-500 bg-gray-50">NONE</td>
                                    <td colspan="4" class="py-3 px-4">
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($teamPerformance['members_without_subtasks'] as $member)
                                                <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-sm">
                                                    <i class="fas fa-user-slash mr-1"></i>{{ $member['name'] }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @else
                            <tr>
                                <td colspan="5" class="text-center text-gray-500 py-8">
                                    No active sprint or no team members assigned to subtasks.
                                </td>
                            </tr>
                        @endif
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
    // Sprint filter change handler
    const sprintFilter = document.getElementById('sprint-filter');
    if (sprintFilter) {
        sprintFilter.addEventListener('change', function() {
            window.location.href = '/reports?sprint_id=' + this.value;
        });
    }

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

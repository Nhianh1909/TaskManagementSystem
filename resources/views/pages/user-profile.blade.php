<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - {{ $user->name }}</title>
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-white">
    {{-- Include navbar --}}
    @include('particals.navbar')

    <div class="min-h-screen bg-white py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Header với title và nút Logout --}}
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900">User Profile</h1>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 border border-gray-800 text-gray-800 rounded-lg hover:bg-gray-100 transition-colors flex items-center gap-2">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </button>
                </form>
            </div>

            {{-- Layout 2 cột: Trái (thông tin user + stats), Phải (subtasks + activity) --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                {{-- ===== CỘT TRÁI ===== --}}
                <div class="space-y-6">
                    {{-- Card thông tin user --}}
                    <div class="bg-white border border-gray-300 rounded-xl shadow-md p-6">
                        <div class="flex flex-col items-center text-center">
                            {{-- Avatar (grayscale) --}}
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=6B7280&color=fff&size=128" 
                                 alt="{{ $user->name }}" 
                                 class="w-32 h-32 rounded-full border-4 border-gray-300 mb-4 grayscale">
                            
                            {{-- Tên user --}}
                            <h2 class="text-2xl font-bold text-gray-900 mb-1">{{ $user->name }}</h2>
                            
                            {{-- Role trong team --}}
                            <p class="text-gray-600 mb-4">{{ $roleDisplay }}</p>
                            
                            {{-- Chi tiết: Email, Team, Role --}}
                            <div class="w-full space-y-3 mt-4 text-left">
                                {{-- Email --}}
                                <div class="flex items-center gap-3 text-gray-700">
                                    <i class="fas fa-envelope w-5 text-gray-500"></i>
                                    <span class="text-sm">{{ $user->email }}</span>
                                </div>
                                
                                @if($team)
                                {{-- Tên team --}}
                                <div class="flex items-center gap-3 text-gray-700">
                                    <i class="fas fa-users w-5 text-gray-500"></i>
                                    <span class="text-sm">Team: {{ $team->name }}</span>
                                </div>
                                
                                {{-- Role trong team --}}
                                <div class="flex items-center gap-3 text-gray-700">
                                    <i class="fas fa-user-tag w-5 text-gray-500"></i>
                                    <span class="text-sm">Role: {{ $roleDisplay }}</span>
                                </div>
                                @else
                                {{-- Trường hợp không có team --}}
                                <div class="flex items-center gap-3 text-gray-400">
                                    <i class="fas fa-users w-5 text-gray-400"></i>
                                    <span class="text-sm italic">Not assigned to any team</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Cards thống kê (4 cards trong grid 2x2) --}}
                    <div class="grid grid-cols-2 gap-4">
                        {{-- Card 1: Tasks hoàn thành --}}
                        <div class="bg-white border border-gray-300 rounded-xl shadow-md p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">Tasks Done</p>
                                    <p class="text-3xl font-bold text-gray-900">{{ $tasksDone }}</p>
                                </div>
                                <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-check text-xl text-gray-700"></i>
                                </div>
                            </div>
                        </div>

                        {{-- Card 2: Tasks đang làm --}}
                        <div class="bg-white border border-gray-300 rounded-xl shadow-md p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">In Progress</p>
                                    <p class="text-3xl font-bold text-gray-900">{{ $tasksInProgress }}</p>
                                </div>
                                <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-spinner text-xl text-gray-700"></i>
                                </div>
                            </div>
                        </div>

                        {{-- Card 3: Tổng Story Points --}}
                        <div class="bg-white border border-gray-300 rounded-xl shadow-md p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">Story Points</p>
                                    <p class="text-3xl font-bold text-gray-900">{{ $totalStoryPoints }}</p>
                                </div>
                                <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-star text-xl text-gray-700"></i>
                                </div>
                            </div>
                        </div>

                        {{-- Card 4: Phần trăm hoàn thành --}}
                        <div class="bg-white border border-gray-300 rounded-xl shadow-md p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">Completion</p>
                                    <p class="text-3xl font-bold text-gray-900">{{ $averageCompletion }}%</p>
                                </div>
                                <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-chart-line text-xl text-gray-700"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===== CỘT PHẢI ===== --}}
                <div class="space-y-6">
                    {{-- Bảng danh sách Subtasks được gán --}}
                    <div class="bg-white border border-gray-300 rounded-xl shadow-md p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Assigned Subtasks</h3>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                {{-- Header của bảng --}}
                                <thead>
                                    <tr class="border-b border-gray-300">
                                        <th class="text-left py-3 px-2 text-sm font-semibold text-gray-700">Subtask</th>
                                        <th class="text-left py-3 px-2 text-sm font-semibold text-gray-700">Parent Task</th>
                                        <th class="text-left py-3 px-2 text-sm font-semibold text-gray-700">Status</th>
                                        <th class="text-left py-3 px-2 text-sm font-semibold text-gray-700">Due Date</th>
                                        <th class="text-left py-3 px-2 text-sm font-semibold text-gray-700">Points</th>
                                    </tr>
                                </thead>
                                {{-- Body của bảng --}}
                                <tbody>
                                    {{-- Loop qua từng subtask --}}
                                    @forelse($subtasks as $subtask)
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        {{-- Tên subtask (giới hạn 30 ký tự) --}}
                                        <td class="py-3 px-2 text-sm text-gray-800">{{ Str::limit($subtask->title, 30) }}</td>
                                        {{-- Tên parent task --}}
                                        <td class="py-3 px-2 text-sm text-gray-600">
                                            {{ $subtask->parent ? Str::limit($subtask->parent->title, 20) : 'N/A' }}
                                        </td>
                                        {{-- Status với badge màu --}}
                                        <td class="py-3 px-2">
                                            @php
                                                // Map status với label và màu badge
                                                $statusMap = [
                                                    'done' => ['label' => 'Done', 'class' => 'bg-gray-800 text-white'],
                                                    'inProgress' => ['label' => 'In Progress', 'class' => 'bg-gray-500 text-white'],
                                                    'toDo' => ['label' => 'Todo', 'class' => 'bg-gray-200 text-gray-800']
                                                ];
                                                $status = $statusMap[$subtask->status] ?? ['label' => ucfirst($subtask->status), 'class' => 'bg-gray-100 text-gray-800'];
                                            @endphp
                                            <span class="px-2 py-1 rounded text-xs font-medium {{ $status['class'] }}">
                                                {{ $status['label'] }}
                                            </span>
                                        </td>
                                        {{-- Due Date từ sprint --}}
                                        <td class="py-3 px-2 text-sm text-gray-600">
                                            @if($subtask->sprint && $subtask->sprint->endDate)
                                                {{ \Carbon\Carbon::parse($subtask->sprint->endDate)->format('M d') }}
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        {{-- Story Points --}}
                                        <td class="py-3 px-2 text-sm text-gray-800 font-semibold">
                                            {{ $subtask->storyPoints ?? '-' }}
                                        </td>
                                    </tr>
                                    @empty
                                    {{-- Hiển thị khi không có subtasks --}}
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-gray-500 text-sm">
                                            <i class="fas fa-inbox text-2xl text-gray-300 mb-2"></i>
                                            <p>No subtasks assigned yet</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Timeline hoạt động gần đây --}}
                    <div class="bg-white border border-gray-300 rounded-xl shadow-md p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Recent Activity</h3>
                        
                        <div class="space-y-4">
                            {{-- Loop qua từng activity --}}
                            @forelse($activities as $activity)
                            <div class="flex gap-4">
                                {{-- Icon tròn bên trái --}}
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-gray-800 text-white rounded-full flex items-center justify-center">
                                        {{-- Icon thay đổi theo loại activity --}}
                                        @if($activity['type'] === 'completed')
                                            <i class="fas fa-check text-sm"></i>
                                        @elseif($activity['type'] === 'commented')
                                            <i class="fas fa-comment text-sm"></i>
                                        @elseif($activity['type'] === 'assigned')
                                            <i class="fas fa-plus text-sm"></i>
                                        @else
                                            <i class="fas fa-circle text-xs"></i>
                                        @endif
                                    </div>
                                </div>
                                
                                {{-- Nội dung activity --}}
                                <div class="flex-1">
                                    <p class="text-sm text-gray-800 font-medium">{{ $activity['message'] }}</p>
                                    {{-- Thời gian (hiển thị dạng 'X days ago') --}}
                                    <p class="text-xs text-gray-500 mt-1">{{ $activity['date']->diffForHumans() }}</p>
                                </div>
                            </div>
                            @empty
                            {{-- Hiển thị khi không có activity --}}
                            <div class="text-center py-8 text-gray-500 text-sm">
                                <i class="fas fa-clock text-2xl text-gray-300 mb-2"></i>
                                <p>No recent activity</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

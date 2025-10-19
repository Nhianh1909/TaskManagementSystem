
@extends('layouts.app')

@section('content')
<div id="sprint" class="page">
    <div class="max-w-4xl mx-auto px-4 py-8">

        {{-- TRƯỜNG HỢP 1: ĐÃ CÓ SPRINT ĐANG CHẠY --}}
        @if($activeSprint)
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Sprint in Progress</h1>
            <p class="text-gray-600 mb-8">Một sprint đang được thực hiện. Bạn không thể tạo sprint mới cho đến khi sprint này kết thúc hoặc bị hủy.</p>

            <div class="bg-white rounded-2xl shadow-lg p-8">
                <div class="space-y-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">{{ $activeSprint->name }}</h3>
                        <p class="text-sm text-gray-500">{{ $activeSprint->goal }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4 border-t pt-4">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Start Date</p>
                            <p class="font-semibold">{{ \Carbon\Carbon::parse($activeSprint->start_date)->format('d M, Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600">End Date</p>
                            <p class="font-semibold">{{ \Carbon\Carbon::parse($activeSprint->end_date)->format('d M, Y') }}</p>
                        </div>
                    </div>
                </div>
                <div class="mt-8 border-t pt-6 flex justify-end">
                    <form action="{{ route('sprint.cancel') }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn hủy Sprint này không? Các task chưa hoàn thành sẽ được trả về Backlog.')">
                        @csrf
                        <button type="submit" class="bg-red-500 text-white px-6 py-3 rounded-lg font-semibold hover:bg-red-600 transition-colors">
                            <i class="fas fa-times-circle mr-2"></i>Cancel Current Sprint
                        </button>
                    </form>
                </div>
            </div>

        {{-- TRƯỜNG HỢP 2: CHƯA CÓ SPRINT NÀO --}}
        @else
            <h1 class="text-3xl font-bold text-gray-800 mb-8">Sprint Planning</h1>
            <div class="bg-white rounded-2xl shadow-lg p-8">
                {{-- Form nhập thông tin cơ bản của Sprint --}}
                <form id="sprint-form" class="space-y-6">
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label for="sprint_name" class="block text-sm font-medium text-gray-700 mb-2">Sprint Name</label>
                            <input id="sprint_name" name="name" type="text" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="e.g., Sprint #7">
                        </div>
                        <div>
                            <label for="sprint_goal" class="block text-sm font-medium text-gray-700 mb-2">Sprint Goal</label>
                            <input id="sprint_goal" name="goal" type="text" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Main objective for this sprint">
                        </div>
                    </div>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                            <input id="start_date" name="start_date" type="date" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                            <input id="end_date" name="end_date" type="date" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div class="flex justify-end space-x-4 pt-4">
                        <button type="button" class="px-6 py-3 border rounded-lg hover:bg-gray-50">Cancel</button>
                        <button id="open-tasks-modal-btn" type="button" class="gradient-btn text-white px-8 py-3 rounded-lg font-semibold">
                            <i class="fas fa-tasks mr-2"></i>Select Tasks & Start
                        </button>
                    </div>
                </form>
            </div>
        @endif
    </div>
</div>

{{-- MODAL CHỌN TASK TỪ PRODUCT BACKLOG --}}
@if(!$activeSprint)
<div id="tasks-modal" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col">
        <div class="p-6 border-b">
            <h2 class="text-2xl font-bold text-gray-800">Select Tasks from Product Backlog</h2>
            <p class="text-sm text-gray-500">Choose tasks to include in this sprint.</p>
        </div>
        <div class="p-6 overflow-y-auto">
            @if($backlogTasks->isEmpty())
                <p class="text-center text-gray-500 py-8">Product Backlog is empty. Please create some tasks first.</p>
            @else
                <div class="space-y-3">
                    @foreach($backlogTasks as $task)
                    <label class="flex items-center space-x-4 p-4 border rounded-lg hover:bg-gray-50 cursor-pointer transition-all">
                        <input type="checkbox" name="task_ids[]" value="{{ $task->id }}" class="task-checkbox h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <div class="flex-grow">
                            <span class="font-semibold text-gray-800">{{ $task->title }}</span>
                            <p class="text-sm text-gray-600 truncate">{{ $task->description }}</p>
                        </div>
                        <span class="text-xs font-bold text-gray-500">SP: {{ $task->storyPoints ?? 0 }}</span>
                    </label>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="p-6 bg-gray-50 rounded-b-2xl flex justify-end space-x-4">
            <button id="close-tasks-modal-btn" type="button" class="px-6 py-3 border rounded-lg text-gray-700 hover:bg-gray-100">Cancel</button>
            <button id="start-sprint-btn" type="button" class="gradient-btn text-white px-8 py-3 rounded-lg font-semibold" {{ $backlogTasks->isEmpty() ? 'disabled' : '' }}>
                <i class="fas fa-rocket mr-2"></i>Start Sprint
            </button>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
{{-- Script này chỉ cần thiết khi không có sprint nào đang chạy --}}
@if(!$activeSprint)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sprintForm = document.getElementById('sprint-form');
    const openModalBtn = document.getElementById('open-tasks-modal-btn');
    const closeModalBtn = document.getElementById('close-tasks-modal-btn');
    const startSprintBtn = document.getElementById('start-sprint-btn');
    const tasksModal = document.getElementById('tasks-modal');

    openModalBtn.addEventListener('click', () => {
        if (sprintForm.checkValidity()) {
            tasksModal.classList.remove('hidden');
        } else {
            sprintForm.reportValidity();
        }
    });

    closeModalBtn.addEventListener('click', () => {
        tasksModal.classList.add('hidden');
    });

    startSprintBtn.addEventListener('click', async () => {
        startSprintBtn.disabled = true;
        startSprintBtn.innerHTML = '<div class="loading-spinner inline-block mr-2"></div>Starting...';

        const formData = new FormData(sprintForm);
        const sprintData = Object.fromEntries(formData.entries());

        const selectedTasks = document.querySelectorAll('.task-checkbox:checked');
        sprintData.task_ids = Array.from(selectedTasks).map(cb => cb.value);

        if (sprintData.task_ids.length === 0) {
            alert('Please select at least one task.');
            startSprintBtn.disabled = false;
            startSprintBtn.innerHTML = '<i class="fas fa-rocket mr-2"></i>Start Sprint';
            return;
        }

        try {
            const response = await fetch("{{ route('sprint.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: JSON.stringify(sprintData)
            });
            const result = await response.json();
            if (!response.ok) {
                throw new Error(result.message || 'An error occurred.');
            }
            alert(result.message);
            window.location.href = result.redirect;
        } catch (error) {
            alert('Error: ' + error.message);
            startSprintBtn.disabled = false;
            startSprintBtn.innerHTML = '<i class="fas fa-rocket mr-2"></i>Start Sprint';
        }
    });
});
</script>
@endif
@endpush

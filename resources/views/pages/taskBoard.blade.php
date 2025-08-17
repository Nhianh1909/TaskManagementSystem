@extends('layouts.app')

@section('content')
{{ Auth::user()->role }}
<div id="taskboard" class="page">
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Task Board</h1>
                @if($activeSprint)
                    <p class="text-gray-600">Current Sprint: <span class="font-semibold">{{ $activeSprint->name }}</span></p>
                @else
                    <p class="text-gray-600">No active sprint.</p>
                @endif
            </div>

            {{-- Chỉ PO mới thấy nút Add Task --}}
            @if(Auth::user()->role === 'product_owner')
            <button onclick="openTaskModal()" class="gradient-btn text-white px-6 py-3 rounded-lg font-semibold">
                <i class="fas fa-plus mr-2"></i>Add Task
            </button>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @php
                $columns = [
                    'backlog' => ['title' => 'Backlog', 'icon' => 'fa-inbox', 'color' => 'gray-500', 'tasks' => $backlogTasks],
                    'toDo' => ['title' => 'To Do', 'icon' => 'fa-list', 'color' => 'blue-500', 'tasks' => $sprintTasks->where('status', 'toDo')],
                    'inProgress' => ['title' => 'In Progress', 'icon' => 'fa-spinner', 'color' => 'yellow-500', 'tasks' => $sprintTasks->where('status', 'inProgress')],
                    'done' => ['title' => 'Done', 'icon' => 'fa-check', 'color' => 'green-500', 'tasks' => $sprintTasks->where('status', 'done')],
                ];
            @endphp

            @foreach($columns as $key => $column)
            <div class="bg-{{ explode('-', $column['color'])[0] }}-100 rounded-2xl p-4">
                <h3 class="font-semibold text-gray-700 mb-4 flex items-center">
                    <i class="fas {{ $column['icon'] }} text-{{ $column['color'] }} mr-2"></i>{{ $column['title'] }}
                    <span class="ml-auto bg-{{ $column['color'] }} text-white text-xs px-2 py-1 rounded-full">{{ count($column['tasks']) }}</span>
                </h3>
                <div class="space-y-3 min-h-[100px]" ondrop="drop(event)" ondragover="allowDrop(event)" data-column="{{ $key }}">
                    @foreach($column['tasks'] as $task)
                    <div class="task-card priority-{{ $task->priority }} bg-white p-4 rounded-lg shadow-sm cursor-move glow-effect" draggable="true" ondragstart="drag(event)" data-task-id="{{ $task->id }}">
                        <div class="flex justify-between items-start">
                             <h4 class="font-medium text-gray-800 mb-2">{{ $task->title }}</h4>
                             {{-- Chỉ PO mới có quyền sửa/xóa task trong Backlog --}}
                             @if(Auth::user()->role === 'product_owner' && is_null($task->sprint_id))
                                <div class="flex-shrink-0 ml-2">
                                    <button onclick="editTask({{ $task->id }})" class="text-blue-500 hover:text-blue-700 text-xs p-1"><i class="fas fa-edit"></i></button>
                                    <button onclick="deleteTask({{ $task->id }})" class="text-red-500 hover:text-red-700 text-xs p-1"><i class="fas fa-trash"></i></button>
                                </div>
                             @endif
                        </div>
                        <p class="text-sm text-gray-600 mb-3">{{ $task->description }}</p>
                        <div class="flex items-center justify-between">
                            @php
                                $priorityColors = ['low' => 'green', 'medium' => 'orange', 'high' => 'red'];
                            @endphp
                            <span class="text-xs bg-{{ $priorityColors[$task->priority] }}-100 text-{{ $priorityColors[$task->priority] }}-800 px-2 py-1 rounded-full">{{ ucfirst($task->priority) }}</span>
                            @if($task->user)
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($task->user->name) }}&background=random" alt="Assignee" class="w-6 h-6 rounded-full" title="Assigned to {{ $task->user->name }}">
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

@if(Auth::user()->role === 'product_owner')
<div id="taskModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white p-6 rounded-xl shadow-xl max-w-md w-full mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 id="modal-title" class="text-lg font-semibold text-gray-800">Add New Task</h3>
            <button onclick="closeTaskModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <form id="task-form" class="space-y-4">
            @csrf
            <input type="hidden" id="task-id" name="task_id">
            <input type="hidden" id="form-method" name="_method" value="POST">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Task Title</label>
                <input type="text" id="task-title" name="title" required class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Enter task title">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea rows="3" id="task-description" name="description" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Enter task description"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                    <select id="task-priority" name="priority" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Story Points</label>
                    <input type="number" id="task-storyPoints" name="storyPoints" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="e.g., 5">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Assignee (Optional)</label>
                <select id="task-assignee" name="assigned_to" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">Unassigned</option>
                    @foreach($teamMembers as $member)
                        <option value="{{ $member->id }}">{{ $member->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex space-x-3 pt-4">
                <button type="submit" id="modal-submit-btn" class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700">Create Task</button>
                <button type="button" onclick="closeTaskModal()" class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
    // --- CRUD CHO PRODUCT BACKLOG (chỉ dành cho PO) ---
    const taskModal = document.getElementById('taskModal');
    const taskForm = document.getElementById('task-form');
    const modalTitle = document.getElementById('modal-title');
    const modalSubmitBtn = document.getElementById('modal-submit-btn');
    const taskIdInput = document.getElementById('task-id');
    const formMethodInput = document.getElementById('form-method');

    function openTaskModal() {
        taskForm.reset();
        taskIdInput.value = '';
        modalTitle.textContent = 'Add New Task';
        modalSubmitBtn.textContent = 'Create Task';
        formMethodInput.value = 'POST';
        taskModal.classList.remove('hidden');
        taskModal.classList.add('flex');
    }

    function closeTaskModal() {
        taskModal.classList.add('hidden');
        taskModal.classList.remove('flex');
    }

    async function editTask(id) {
        const response = await fetch(`/tasks/${id}/edit`);
        if (!response.ok) {
            alert('Could not fetch task details.');
            return;
        }
        const task = await response.json();

        openTaskModal();
        modalTitle.textContent = 'Edit Task';
        modalSubmitBtn.textContent = 'Update Task';
        formMethodInput.value = 'PATCH';

        document.getElementById('task-id').value = task.id;
        document.getElementById('task-title').value = task.title;
        document.getElementById('task-description').value = task.description;
        document.getElementById('task-priority').value = task.priority;
        document.getElementById('task-storyPoints').value = task.storyPoints;
        document.getElementById('task-assignee').value = task.assigned_to || '';
    }

    taskForm.addEventListener('submit', async (e) => {
        e.preventDefault();//ngăn chặn reload trang khi submit form để xử lý request fetch API
        const formData = new FormData(taskForm);
        const data = Object.fromEntries(formData.entries());
        const taskId = data.task_id;
        const method = formMethodInput.value;
        const url = taskId ? `/tasks/${taskId}` : "{{ route('tasks.store') }}";
        // tạo các biến để lưu trữ giá trị của form bao gồm: nội dung task form, task_id, method và url
        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),//khai báo token để bảo vệ form
                    'Accept': 'application/json',
                },
                body: JSON.stringify(data),
            });//khai báo nội dung của resquest fetch API

            const result = await response.json();
            //nhận kết quả trả về từ server
            if (!response.ok) {
                // Hiển thị lỗi validation nếu có
                if (response.status === 422) {
                    let errorMsg = 'Please check your input:\n';
                    for (const field in result.errors) {
                        errorMsg += `- ${result.errors[field].join(', ')}\n`;
                    }
                    alert(errorMsg);
                } else {
                    throw new Error(result.message || 'An error occurred.');
                }
            } else {
                alert(result.message);
                location.reload(); // Cách đơn giản nhất là tải lại trang
            }
        } catch (error) {
            alert(error.message);
        }
    });

    async function deleteTask(id) {
        if (!confirm('Are you sure you want to delete this task?')) {
            return;
        }

        try {
            const response = await fetch(`/tasks/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'Could not delete the task.');
            }

            alert(result.message);
            document.querySelector(`[data-task-id="${id}"]`).remove(); // Xóa task khỏi giao diện
        } catch (error) {
            alert(error.message);
        }
    }

</script>
@endpush

@extends('layouts.app')

@section('content')
<div id="taskboard" class="page">
    <div class="max-w-7xl mx-auto px-4 py-8">

        {{-- SỬA LỖI 1: Thêm z-20 để header luôn nằm trên --}}
        <div class="flex justify-between items-center mb-8 relative z-20">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Task Board</h1>
                @if($activeSprint)
                    <p class="text-gray-600">Current Sprint: <span class="font-semibold">{{ $activeSprint->name }}</span></p>
                @else
                    <p class="text-gray-600">No active sprint. Viewing Product Backlog.</p>
                @endif
            </div>
            {{-- Sửa lỗi phân quyền: Dùng $userRoleInTeam --}}
            @if(isset($userRoleInTeam) && $userRoleInTeam === 'product_owner')
            <button onclick="openTaskModal()" class="gradient-btn text-white px-6 py-3 rounded-lg font-semibold">
                <i class="fas fa-plus mr-2"></i>Add Task to Backlog
            </button>
            @endif
        </div>

        {{-- SỬA LỖI 2: Thêm z-10 để lưới nằm dưới header --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 relative z-10">
            @php
                $columns = [
                    'backlog' => ['title' => 'Product Backlog', 'icon' => 'fa-inbox', 'color' => 'gray-500', 'tasks' => $backlogTasks],
                    'toDo' => ['title' => 'To Do', 'icon' => 'fa-list', 'color' => 'blue-500', 'tasks' => $sprintTasks->where('status', 'toDo')],
                    'inProgress' => ['title' => 'In Progress', 'icon' => 'fa-spinner', 'color' => 'yellow-500', 'tasks' => $sprintTasks->where('status', 'inProgress')],
                    'done' => ['title' => 'Done', 'icon' => 'fa-check', 'color' => 'green-500', 'tasks' => $sprintTasks->where('status', 'done')],
                ];
            @endphp

            @foreach($columns as $key => $column)
            <div class="bg-{{ explode('-', $column['color'])[0] }}-50 rounded-2xl p-4 flex flex-col">
                <h3 class="font-semibold text-gray-700 mb-4 flex items-center flex-shrink-0">
                    <i class="fas {{ $column['icon'] }} text-{{ $column['color'] }} mr-2"></i>{{ $column['title'] }}
                    <span class="ml-auto bg-{{ $column['color'] }} text-white text-xs px-2 py-1 rounded-full">{{ count($column['tasks']) }}</span>
                </h3>

                <div class="task-column space-y-3 min-h-[200px] flex-grow overflow-y-auto"
                     ondrop="drop(event)"
                     ondragover="allowDrop(event)"
                     ondragleave="dragLeave(event)"
                     data-column-status="{{ $key }}">

                    @foreach($column['tasks'] as $task)
                        @php
                            $isDraggable = (Auth::id() === $task->assigned_to || (isset($userRoleInTeam) && $userRoleInTeam === 'scrum_master'));

                            // LOGIC LÀM ĐẬM TASK MỚI
                            $isHighlighted = (
                                // 1. Là task được giao cho tôi
                                Auth::id() === $task->assigned_to ||
                                // 2. Hoặc tôi là Scrum Master (thấy đậm mọi task trong sprint)
                                (isset($userRoleInTeam) && $userRoleInTeam === 'scrum_master') ||
                                // 3. Hoặc tôi là Product Owner VÀ task này đang ở trong Product Backlog
                                (isset($userRoleInTeam) && $userRoleInTeam === 'product_owner' && is_null($task->sprint_id))
                            );
                        @endphp

                        <div class="task-card priority-{{ $task->priority }} bg-white p-4 rounded-lg shadow-sm {{ $isDraggable ? 'cursor-move' : 'cursor-not-allowed' }} transition-all duration-200 @if($isHighlighted) border-2 border-blue-500 scale-105 @else opacity-70 @endif"
                            draggable="{{ $isDraggable ? 'true' : 'false' }}"
                            ondragstart="drag(event)"
                            data-task-id="{{ $task->id }}"
                            data-task-sprint-id="{{ $task->sprint_id }}">

                            <div class="flex justify-between items-start">
                                 <h4 class="font-medium text-gray-800 mb-2">{{ $task->title }}</h4>
                                 {{-- Sửa lỗi phân quyền: Dùng $userRoleInTeam --}}
                                 @if(isset($userRoleInTeam) && $userRoleInTeam === 'product_owner' && is_null($task->sprint_id))
                                    <div class="flex-shrink-0 ml-2">
                                        <button onclick="editTask({{ $task->id }})" class="text-blue-500 hover:text-blue-700 text-xs p-1"><i class="fas fa-edit"></i></button>
                                        <button onclick="deleteTask({{ $task->id }})" class="text-red-500 hover:text-red-700 text-xs p-1"><i class="fas fa-trash"></i></button>
                                    </div>
                                 @endif
                            </div>
                            <p class="text-sm text-gray-600 mb-3">{{ Str::limit($task->description, 100) }}</p>
                            <div class="flex items-center justify-between">
                                @php
                                    $priorityColors = ['low' => 'green', 'medium' => 'yellow', 'high' => 'red'];
                                @endphp
                                <span class="text-xs font-semibold bg-{{ $priorityColors[$task->priority] }}-100 text-{{ $priorityColors[$task->priority] }}-800 px-2 py-1 rounded-full">{{ ucfirst($task->priority) }}</span>
                                <div class="flex items-center gap-3">
                                    <button type="button" class="comment-toggle text-gray-500 hover:text-blue-600 flex items-center gap-1" data-task-id="{{ $task->id }}" title="Comment">
                                        <i class="far fa-comment"></i>
                                        <span class="comment-count text-xs bg-gray-100 text-gray-700 px-1.5 py-0.5 rounded-full">{{ $task->comments_count ?? 0 }}</span>
                                    </button>
                                    @if($task->assignee)
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($task->assignee->name) }}&background=random&color=fff" alt="Assignee" class="w-6 h-6 rounded-full" title="Assigned to {{ $task->assignee->name }}">
                                    @endif
                                </div>
                            </div>

                            <div class="comment-box mt-3 hidden w-full overflow-hidden" data-task-id="{{ $task->id }}">
                                <div class="flex items-center gap-2 w-full">
                                    <input type="text" class="comment-input w-0 flex-1 min-w-0 border border-gray-300 rounded-md px-3 py-2 text-sm" placeholder="Nhập nhận xét và nhấn Enter..." />
                                    <button type="button" class="comment-send shrink-0 bg-blue-600 text-white px-3 py-2 rounded-md" data-task-id="{{ $task->id }}" title="Gửi">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </div>
                                <div class="comments-list mt-2 space-y-2 max-h-48 overflow-y-auto text-sm text-gray-700" data-loaded="false"></div>
                                <button type="button" class="load-more-comments hidden mt-2 text-xs text-blue-600 hover:underline" data-task-id="{{ $task->id }}">Xem thêm bình luận</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Modal Add/Edit Task (Dành cho Product Owner) --}}
@if(isset($userRoleInTeam) && $userRoleInTeam === 'product_owner')
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
                <div class="flex items-center space-x-2">
                    <input type="text" id="task-title" name="title" required class="flex-grow w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Enter task title">
                    <button type="button" id="ai-suggest-btn" class="bg-purple-500 text-white px-3 py-2 rounded-lg hover:bg-purple-600 transition-colors">
                        <i class="fas fa-magic"></i> AI
                    </button>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea rows="4" id="task-description" name="description" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="AI will suggest a description here..."></textarea>
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
    // --- COMMENT: toggle & submit logic ---
    document.addEventListener('click', function(e) {
        const toggleBtn = e.target.closest('.comment-toggle');
        if (toggleBtn) {
            const card = toggleBtn.closest('.task-card');
            const box = card.querySelector('.comment-box');
            const wasHidden = box.classList.contains('hidden');
            box.classList.toggle('hidden');
            if (wasHidden) {
                // Load latest comments when opening
                const list = box.querySelector('.comments-list');
                list.dataset.loaded = 'false';
                list.innerHTML = '';
                box.querySelector('.load-more-comments').classList.add('hidden');
                fetchAndRenderComments(card.getAttribute('data-task-id'), box);
            }
        }
    });

    async function submitComment(taskId, inputEl, card) {
        const content = inputEl.value.trim();
        if (!content) return;
        const sendBtn = card.querySelector('.comment-send');
        sendBtn.disabled = true;
        sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        try {
            const res = await fetch(`/tasks/${taskId}/comments`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ content })
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Không thể gửi bình luận.');

            // Tăng bộ đếm
            const countEl = card.querySelector('.comment-count');
            const current = parseInt(countEl.textContent || '0', 10);
            countEl.textContent = current + 1;
            // Dọn input và ẩn box
            inputEl.value = '';
            card.querySelector('.comment-box').classList.add('hidden');
        } catch (err) {
            alert(err.message);
        } finally {
            sendBtn.disabled = false;
            sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
        }
    }

    document.addEventListener('click', function(e) {
        const sendBtn = e.target.closest('.comment-send');
        if (sendBtn) {
            const card = sendBtn.closest('.task-card');
            const inputEl = card.querySelector('.comment-input');
            submitComment(sendBtn.dataset.taskId, inputEl, card);
        }
    });

    document.addEventListener('keydown', function(e) {
        const inputEl = e.target.closest('.comment-input');
        if (inputEl && e.key === 'Enter') {
            e.preventDefault();
            const card = inputEl.closest('.task-card');
            const taskId = card.getAttribute('data-task-id');
            submitComment(taskId, inputEl, card);
        }
    });

    async function fetchAndRenderComments(taskId, box, before = null) {
        const list = box.querySelector('.comments-list');
        const loadMoreBtn = box.querySelector('.load-more-comments');
        try {
            const params = new URLSearchParams();
            params.set('limit', '10');
            if (before) params.set('before', before);
            const res = await fetch(`/tasks/${taskId}/comments?` + params.toString(), {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Không tải được bình luận.');

            const items = data.data || [];
            // Append comments (older at bottom)
            items.forEach(c => {
                const row = document.createElement('div');
                row.className = 'flex items-start gap-2';
                const initials = (c.user?.name || '?').split(' ').map(s => s[0]).join('').slice(0,2).toUpperCase();
                row.innerHTML = `
                    <div class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-xs text-gray-600">${initials}</div>
                    <div class="flex-1 min-w-0">
                        <div class="text-xs text-gray-500">${escapeHtml(c.user?.name || 'Unknown')} • ${formatTime(c.created_at)}</div>
                        <div class="whitespace-pre-wrap">${escapeHtml(c.content)}</div>
                    </div>
                `;
                list.appendChild(row);
            });

            if (data.has_more && data.next_before) {
                loadMoreBtn.classList.remove('hidden');
                loadMoreBtn.dataset.nextBefore = data.next_before;
            } else {
                loadMoreBtn.classList.add('hidden');
                loadMoreBtn.dataset.nextBefore = '';
            }

            list.dataset.loaded = 'true';
        } catch (err) {
            const errRow = document.createElement('div');
            errRow.className = 'text-xs text-red-600';
            errRow.textContent = err.message;
            list.appendChild(errRow);
        }
    }

    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.load-more-comments');
        if (btn) {
            const card = btn.closest('.task-card');
            const box = card.querySelector('.comment-box');
            const nextBefore = btn.dataset.nextBefore;
            if (nextBefore) fetchAndRenderComments(card.getAttribute('data-task-id'), box, nextBefore);
        }
    });

    function escapeHtml(str) {
        return (str || '').replace(/[&<>"']/g, function(m) {
            return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[m]);
        });
    }

    function formatTime(str) {
        try { return new Date(str).toLocaleString(); } catch { return str; }
    }

    // --- DRAG AND DROP LOGIC (Đã nâng cấp) ---
    function allowDrop(ev) {
        ev.preventDefault();
        const targetColumn = ev.currentTarget;
        const taskSprintId = ev.dataTransfer.getData("text/sprint-id");
        const isSprintActive = {{ $activeSprint ? 'true' : 'false' }};

        if (isMoveAllowed(taskSprintId, targetColumn.dataset.columnStatus, isSprintActive)) {
            if (targetColumn.classList.contains('task-column')) {
                targetColumn.classList.add('bg-blue-100', 'border-2', 'border-dashed', 'border-blue-400');
            }
        }
    }

    function dragLeave(ev) {
        if (ev.currentTarget.classList.contains('task-column')) {
            ev.currentTarget.classList.remove('bg-blue-100', 'border-2', 'border-dashed', 'border-blue-400');
        }
    }

    function drag(ev) {
        const taskElement = ev.target.closest('.task-card');
        ev.dataTransfer.setData("text/plain", taskElement.dataset.taskId);
        ev.dataTransfer.setData("text/sprint-id", taskElement.dataset.taskSprintId);
    }

    function isMoveAllowed(taskSprintId, newStatus, isSprintActive) {
        const isTaskInBacklog = taskSprintId === '';

        if (!isSprintActive) {
            return newStatus === 'backlog';
        }

        if (isTaskInBacklog && newStatus !== 'backlog') {
            return false;
        }
        if (!isTaskInBacklog && newStatus === 'backlog') {
            return false;
        }

        return true;
    }

    async function drop(ev) {
        ev.preventDefault();
        const targetColumn = ev.currentTarget;
        dragLeave(ev);

        const newStatus = targetColumn.dataset.columnStatus;
        const taskId = ev.dataTransfer.getData("text/plain");
        const taskSprintId = ev.dataTransfer.getData("text/sprint-id");
        const isSprintActive = {{ $activeSprint ? 'true' : 'false' }};

        if (!isMoveAllowed(taskSprintId, newStatus, isSprintActive)) {
            if (!isSprintActive) {
                alert('Sprint has not started. You cannot move tasks out of the Product Backlog.');
            } else if (taskSprintId === '' && newStatus !== 'backlog') {
                alert('This task is in the Product Backlog. Please add it to the sprint via the Sprint Planning page.');
            } else if (taskSprintId !== '' && newStatus === 'backlog') {
                alert('Cannot drag tasks from an active sprint back to the Product Backlog.');
            }
            return;
        }

        const draggedElement = document.querySelector(`[data-task-id="${taskId}"]`);

        try {
            const response = await fetch(`/tasks/${taskId}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ status: newStatus })
            });

            const result = await response.json();
            if (!response.ok) {
                throw new Error(result.message || 'Update failed.');
            }
            targetColumn.appendChild(draggedElement);
        } catch (error) {
            alert('Error: ' + error.message);
        }
    }

    // --- LOGIC CRUD VÀ AI CHO PRODUCT OWNER ---
    @if(isset($userRoleInTeam) && $userRoleInTeam === 'product_owner')
        document.addEventListener('DOMContentLoaded', function() {
            const taskModal = document.getElementById('taskModal');
            const taskForm = document.getElementById('task-form');
            const modalTitle = document.getElementById('modal-title');
            const modalSubmitBtn = document.getElementById('modal-submit-btn');
            const taskIdInput = document.getElementById('task-id');
            const formMethodInput = document.getElementById('form-method');

            const aiSuggestBtn = document.getElementById('ai-suggest-btn');
            const taskTitleInput = document.getElementById('task-title');
            const taskDescriptionInput = document.getElementById('task-description');
            const taskPriorityInput = document.getElementById('task-priority');
            const taskStoryPointsInput = document.getElementById('task-storyPoints');
            const taskAssigneeInput = document.getElementById('task-assignee');

            if(aiSuggestBtn) {
                aiSuggestBtn.addEventListener('click', async () => {
                    const title = taskTitleInput.value.trim();
                    if (!title) {
                        alert('Please enter a task title first.');
                        return;
                    }

                    aiSuggestBtn.disabled = true;
                    aiSuggestBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                    try {
                        const response = await fetch("{{ route('tasks.suggest') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ title: title })
                        });

                        if (!response.ok) {
                            const errorText = await response.text();
                            throw new Error(`Server responded with status ${response.status}. Response: ${errorText}`);
                        }

                        const result = await response.json();
                        console.log('AI Suggestion Result:', result);
                        if (result.description) {
                            let fullDescription = result.description;
                            if (result.sub_tasks && result.sub_tasks.length > 0) {
                                fullDescription += "\n\n**Suggested Sub-tasks:**\n";
                                result.sub_tasks.forEach(sub => {
                                    fullDescription += `- ${sub}\n`;
                                });
                            }
                            taskDescriptionInput.value = fullDescription;
                        }
                        if (result.priority) {
                            taskPriorityInput.value = result.priority;
                        }
                        if (result.storyPoints) {
                            taskStoryPointsInput.value = result.storyPoints;
                        }
                        if (result.suggested_assignee_id) {
                            taskAssigneeInput.value = result.suggested_assignee_id;
                        }

                    } catch (error) {
                        console.error('An error occurred during the AI suggestion request:', error);
                        alert('An error occurred. Please check the browser console (F12) for more details.');
                    } finally {
                        aiSuggestBtn.disabled = false;
                        aiSuggestBtn.innerHTML = '<i class="fas fa-magic"></i> AI';
                    }
                });
            }

            window.openTaskModal = function() {
                taskForm.reset();
                taskIdInput.value = '';
                modalTitle.textContent = 'Add New Task';
                modalSubmitBtn.textContent = 'Create Task';
                formMethodInput.value = 'POST';
                taskModal.classList.remove('hidden');
                taskModal.classList.add('flex');
            }

            window.closeTaskModal = function() {
                taskModal.classList.add('hidden');
                taskModal.classList.remove('flex');
            }

            window.editTask = async function(id) {
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
                e.preventDefault();
                const formData = new FormData(taskForm);
                const data = Object.fromEntries(formData.entries());
                const taskId = data.task_id;
                const method = formMethodInput.value;
                const url = taskId ? `/tasks/${taskId}` : "{{ route('tasks.store') }}";

                try {
                    const response = await fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(data),
                    });

                    const result = await response.json();
                    if (!response.ok) {
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
                        location.reload();
                    }
                } catch (error) {
                    alert(error.message);
                }
            });

            window.deleteTask = async function(id) {
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
                    document.querySelector(`[data-task-id="${id}"]`).remove();
                } catch (error) {
                    alert(error.message);
                }
            }
        });
    @endif
</script>
@endpush
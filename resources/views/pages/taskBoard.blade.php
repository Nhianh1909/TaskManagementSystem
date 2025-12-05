@extends('layouts.app')

@section('content')
<div id="taskboard" class="page h-screen flex flex-col">
    <div class="max-w-7xl mx-auto px-4 py-8 flex-1 flex flex-col h-full">

            {{-- HEADER --}}
            <div class="flex justify-between items-center mb-4 relative z-20 flex-shrink-0">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Task Board</h1>
                    @if($activeSprint)
                        <p class="text-gray-600">Current Sprint: <span class="font-semibold">{{ $activeSprint->name }}</span></p>
                    @else
                        <p class="text-gray-600">No active sprint. Viewing Product Backlog.</p>
                    @endif
                </div>
                {{-- Nút thêm cột (Chỉ hiện cho PO/SM) --}}
                @if(in_array($userRoleInTeam, ['product_owner', 'scrum_master']))
                <div class="flex gap-2">
                    <button onclick="openAddColumnModal()" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-semibold hover:bg-gray-300 transition-colors">
                        <i class="fas fa-columns mr-2"></i>Add Column
                    </button>
                    @if($userRoleInTeam === 'product_owner')
                    <button onclick="openTaskModal()" class="gradient-btn text-white px-6 py-2 rounded-lg font-semibold">
                        <i class="fas fa-plus mr-2"></i>Add Task
                    </button>
                    @endif
                </div>
                @endif
            </div>

        {{-- 🔥 MAIN BOARD GRID (SCROLL NGANG CHO CÁC CỘT ĐỘNG) --}}
        <div class="flex-1 overflow-x-auto overflow-y-hidden pb-4">
            <div class="flex h-full gap-6 min-w-full">

                {{-- ======================================================= --}}
                {{-- 1. CỘT CỐ ĐỊNH: US DECOMPOSITION (Giữ nguyên UI cũ) --}}
                {{-- ======================================================= --}}
                <div class="flex-shrink-0 w-80 flex flex-col bg-purple-50 rounded-2xl p-4 border-t-4 border-purple-500 h-full">
                    <h3 class="font-semibold text-gray-700 mb-4 flex items-center flex-shrink-0">
                        <i class="fas fa-sitemap text-purple-500 mr-2"></i>US Decomposition
                    </h3>

                    <div class="flex-1 flex flex-col min-h-0 space-y-4 overflow-y-auto pr-2 custom-scrollbar-y">
                        {{-- Filter User Stories --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-filter mr-1"></i>Filter User Story:
                            </label>
                            @php
                                // Lấy US từ sprint tasks (những task có parent_id = null)
                                // Lưu ý: $columns từ controller trả về đã chứa task con,
                                // ta cần lấy danh sách US từ $activeSprint hoặc query riêng.
                                // Ở đây tạm dùng $activeSprint để lấy US
                                $userStories = $activeSprint ? $activeSprint->tasks()->whereNull('parent_id')->get() : collect();
                            @endphp
                            <select id="user-story-filter" class="w-full border border-purple-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 bg-white">
                                <option value="">-- All User Stories --</option>
                                @foreach($userStories as $story)
                                    <option value="{{ $story->id }}">
                                        #{{ $story->id }} - {{ Str::limit($story->title, 35) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Create Subtask Button --}}
                        @if(isset($userRoleInTeam) && $userRoleInTeam === 'product_owner')
                            <button id="create-subtask-btn" class="w-full bg-purple-600 text-white py-2.5 rounded-lg hover:bg-purple-700 transition-colors font-semibold disabled:bg-gray-300 disabled:cursor-not-allowed flex-shrink-0" disabled>
                                <i class="fas fa-plus mr-2"></i>Create Subtask
                            </button>
                        @endif

                        {{-- Danh sách User Stories (ReadOnly List) --}}
                        <div class="space-y-2">
                             @forelse($userStories as $story)
                                <div class="bg-white p-3 rounded-lg shadow-sm border-l-4 border-purple-400 hover:shadow-md transition-shadow">
                                    <p class="text-sm font-semibold text-gray-800">#{{ $story->id }} - {{ Str::limit($story->title, 40) }}</p>
                                    <div class="flex items-center gap-3 mt-2 text-xs text-gray-500">
                                        <span><i class="fas fa-chart-simple mr-1 text-blue-500"></i>{{ $story->storyPoints ?? 0 }} pts</span>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8 text-gray-400">No stories in sprint</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- ======================================================= --}}
                {{-- 2. CÁC CỘT ĐỘNG TỪ DATABASE (To Do, In Progress...) --}}
                {{-- ======================================================= --}}
                @foreach($columns as $column)
                <div class="flex-shrink-0 w-80 flex flex-col bg-gray-100 rounded-2xl shadow-sm h-full max-h-full border-t-4 {{ $column->color_class ?? 'border-gray-400' }}">

                    {{-- Header Cột --}}
                    <div class="p-4 flex justify-between items-center bg-gray-200 rounded-t-xl border-b border-gray-300 flex-shrink-0 task-column-header cursor-move hover:bg-gray-300 transition-colors group">
                        <h3 class="font-bold text-gray-700 text-sm uppercase tracking-wide truncate flex-1" title="{{ $column->name }}">
                            {{ $column->name }}
                        </h3>
                        <span class="bg-white text-gray-600 text-xs px-2 py-1 rounded-full font-bold shadow-sm mr-2">
                            {{ $column->tasks->count() }}
                        </span>
                        {{-- Delete Column Button --}}
                        @if(in_array($userRoleInTeam, ['product_owner', 'scrum_master']))
                            <button onclick="deleteColumn({{ $column->id }}, '{{ $column->name }}', {{ $column->tasks->count() }})"
                                    class="text-red-500 hover:text-red-700 hover:bg-red-100 p-1 rounded transition-colors opacity-0 group-hover:opacity-100"
                                    title="Delete column">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        @endif
                    </div>

                    {{-- Dropzone (Nơi chứa Task) --}}
                    <div class="flex-1 p-3 overflow-y-auto space-y-3 custom-scrollbar-y min-h-[100px] task-column"
                         id="status-{{ $column->id }}"
                         data-column-status="{{ $column->id }}"
                         ondrop="drop(event, {{ $column->id }})"
                         ondragover="allowDrop(event)"
                         ondragleave="dragLeave(event)">

                        @foreach($column->tasks as $task)
                            {{-- Chỉ hiển thị Subtask (có parent_id) trong bảng Kanban --}}
                            @if($task->parent_id)
                                @php
                                    $isDraggable = (Auth::id() === $task->assigned_to || (isset($userRoleInTeam) && $userRoleInTeam === 'scrum_master'));
                                    $isHighlighted = (Auth::id() === $task->assigned_to);
                                @endphp

                                <div class="task-card priority-{{ $task->priority }} bg-white p-4 rounded-lg shadow-sm border border-gray-200
                                     {{ $isDraggable ? 'cursor-move hover:shadow-md' : 'cursor-not-allowed opacity-80' }}
                                     transition-all duration-200 group relative
                                     @if($isHighlighted) ring-2 ring-blue-400 @endif"
                                     draggable="{{ $isDraggable ? 'true' : 'false' }}"
                                     ondragstart="drag(event, {{ $task->id }})"
                                     id="task-{{ $task->id }}"
                                     data-task-id="{{ $task->id }}"
                                     data-parent-id="{{ $task->parent_id }}">

                                    {{-- Parent Story Label --}}
                                    <div class="mb-1">
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-purple-600 bg-purple-50 px-1.5 py-0.5 rounded">
                                            US #{{ $task->parent_id }}
                                        </span>
                                    </div>

                                    {{-- Title & Menu --}}
                                    <div class="flex justify-between items-start mb-2">
                                        <h4 class="font-medium text-gray-800 text-sm leading-snug hover:text-blue-600 cursor-pointer" onclick="openTaskDetail({{ $task->id }})">
                                            {{ $task->title }}
                                        </h4>

                                        {{-- Edit/Delete Buttons (Cho PO) --}}
                                        @if(isset($userRoleInTeam) && $userRoleInTeam === 'product_owner')
                                        <div class="flex-shrink-0 ml-1 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button onclick="editSubtask({{ $task->id }})" class="text-gray-400 hover:text-blue-600"><i class="fas fa-pen text-xs"></i></button>
                                            <button onclick="deleteSubtask({{ $task->id }})" class="text-gray-400 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button>
                                        </div>
                                        @endif
                                    </div>

                                    {{-- Description Preview --}}
                                    @if($task->description)
                                        <p class="text-xs text-gray-500 mb-3 line-clamp-2">{{ $task->description }}</p>
                                    @endif

                                    {{-- Footer: Priority, Comments, Avatar --}}
                                    <div class="flex items-center justify-between mt-2 pt-2 border-t border-dashed border-gray-100">
                                        <div class="flex items-center gap-2">
                                            {{-- Priority Badge --}}
                                            @php
                                                $pColor = match($task->priority) {
                                                    'high' => 'text-red-700 bg-red-50 border-red-200',
                                                    'medium' => 'text-yellow-700 bg-yellow-50 border-yellow-200',
                                                    'low' => 'text-green-700 bg-green-50 border-green-200',
                                                    default => 'text-gray-700 bg-gray-50'
                                                };
                                            @endphp
                                            <span class="text-[10px] px-1.5 py-0.5 rounded border {{ $pColor }} font-medium">
                                                {{ ucfirst($task->priority) }}
                                            </span>

                                            {{-- Comment Count --}}
                                            <button class="comment-toggle text-gray-400 hover:text-blue-500 flex items-center gap-1 text-xs" data-task-id="{{ $task->id }}">
                                                <i class="far fa-comment"></i>
                                                <span>{{ $task->comments_count ?? 0 }}</span>
                                            </button>
                                        </div>

                                        {{-- Avatar --}}
                                        <div class="flex items-center">
                                            @if($task->assignee)
                                                <img src="https://ui-avatars.com/api/?name={{ urlencode($task->assignee->name) }}&background=random&size=24&color=fff"
                                                     class="w-6 h-6 rounded-full border border-white shadow-sm"
                                                     title="{{ $task->assignee->name }}">
                                            @else
                                                <div class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-[10px] text-gray-400 border border-white">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Comment Box (Hidden) --}}
                                    <div class="comment-box mt-3 hidden w-full" data-task-id="{{ $task->id }}">
                                        {{-- (Giữ nguyên code comment cũ của bạn) --}}
                                        <div class="flex items-center gap-2 w-full">
                                            <input type="text" class="comment-input w-0 flex-1 border border-gray-300 rounded px-2 py-1 text-xs" placeholder="Comment..." />
                                            <button type="button" class="comment-send bg-blue-600 text-white px-2 py-1 rounded text-xs" data-task-id="{{ $task->id }}">
                                                <i class="fas fa-paper-plane"></i>
                                            </button>
                                        </div>
                                        <div class="comments-list mt-2 space-y-1 max-h-32 overflow-y-auto text-xs" data-loaded="false"></div>
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        @if($column->tasks->whereNotNull('parent_id')->isEmpty())
                            <div class="h-20 flex items-center justify-center text-gray-400 text-xs italic border-2 border-dashed border-gray-200 rounded-lg bg-gray-50/50">
                                Drop tasks here
                            </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
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

{{-- Modal Create/Edit Subtask (Dành cho Product Owner) --}}
@if(isset($userRoleInTeam) && $userRoleInTeam === 'product_owner')
<div id="subtaskModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white p-6 rounded-xl shadow-xl max-w-md w-full mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 id="subtask-modal-title" class="text-lg font-semibold text-gray-800">
                <i class="fas fa-layer-group text-purple-600 mr-2"></i>Create Subtask
            </h3>
            <button onclick="closeSubtaskModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="subtask-form" class="space-y-4">
            @csrf
            <input type="hidden" id="subtask-id" name="subtask_id">
            <input type="hidden" id="subtask-parent-id" name="parent_id">
            <input type="hidden" id="subtask-method" name="_method" value="POST">

            {{-- Hiển thị User Story đang chọn --}}
            <div class="bg-purple-50 border border-purple-200 rounded-lg p-3">
                <p class="text-xs text-gray-600 mb-1">Parent User Story:</p>
                <p id="subtask-parent-title" class="text-sm font-semibold text-purple-700">
                    <i class="fas fa-sitemap mr-1"></i>
                    Select a User Story first
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Subtask Title <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    id="subtask-title"
                    name="title"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                    placeholder="Enter subtask title">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea
                    rows="3"
                    id="subtask-description"
                    name="description"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                    placeholder="Describe the subtask..."></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                    <select id="subtask-priority" name="priority" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>

                {{-- ✅ ĐÃ XÓA Ô Story Points - Vì subtask không có points riêng --}}
                {{-- Story Points chỉ nằm ở User Story (task cha) --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Assign To</label>
                    <select id="subtask-assigned-to" name="assigned_to" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        <option value="">Unassigned</option>
                        @foreach($teamMembers as $member)
                            <option value="{{ $member->id }}">{{ $member->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Thông báo cho user biết --}}
            <div class="bg-blue-50 border-l-4 border-blue-400 p-3 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-xs text-blue-700">
                            <strong>Note:</strong> Subtasks inherit Story Points from the parent User Story.
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex space-x-3 pt-4">
                <button
                    type="submit"
                    id="subtask-submit-btn"
                    class="flex-1 bg-purple-600 text-white py-2 px-4 rounded-lg hover:bg-purple-700 font-semibold transition-colors">
                    <i class="fas fa-plus mr-2"></i>Create Subtask
                </button>
                <button
                    type="button"
                    onclick="closeSubtaskModal()"
                    class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400 font-semibold">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- 🔥 Modal Add Column (Dành cho PO/SM) --}}
@if(in_array($userRoleInTeam, ['product_owner', 'scrum_master']))
<div id="addColumnModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white p-6 rounded-xl shadow-xl max-w-md w-full mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-columns text-blue-600 mr-2"></i>Create New Column
            </h3>
            <button onclick="closeAddColumnModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="add-column-form" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Column Name</label>
                <input
                    type="text"
                    id="column-name"
                    name="name"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="e.g., Review, QA Testing">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Color</label>
                <div class="flex gap-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="color_class" value="border-blue-400" checked class="w-4 h-4">
                        <span class="inline-block w-6 h-6 bg-blue-100 border-2 border-blue-400 rounded"></span>
                        <span class="text-sm text-gray-600">Blue</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="color_class" value="border-green-400" class="w-4 h-4">
                        <span class="inline-block w-6 h-6 bg-green-100 border-2 border-green-400 rounded"></span>
                        <span class="text-sm text-gray-600">Green</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="color_class" value="border-yellow-400" class="w-4 h-4">
                        <span class="inline-block w-6 h-6 bg-yellow-100 border-2 border-yellow-400 rounded"></span>
                        <span class="text-sm text-gray-600">Yellow</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="color_class" value="border-red-400" class="w-4 h-4">
                        <span class="inline-block w-6 h-6 bg-red-100 border-2 border-red-400 rounded"></span>
                        <span class="text-sm text-gray-600">Red</span>
                    </label>
                </div>
            </div>

            <div class="flex items-center gap-2 bg-blue-50 border-l-4 border-blue-400 p-3 rounded">
                <i class="fas fa-info-circle text-blue-600"></i>
                <p class="text-xs text-blue-700">Column will be added at the end. Drag header to reorder.</p>
            </div>

            <div class="flex space-x-3 pt-4">
                <button type="submit" class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 font-semibold transition-colors">
                    <i class="fas fa-plus mr-2"></i>Create Column
                </button>
                <button type="button" onclick="closeAddColumnModal()" class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400 font-semibold">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- 🔥 Modal Delete Column Warning --}}
@if(in_array($userRoleInTeam, ['product_owner', 'scrum_master']))
<div id="deleteColumnModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white p-6 rounded-xl shadow-2xl max-w-md w-full mx-4">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Delete Column</h3>
            <p class="text-gray-600 mb-4" id="delete-column-message">
                Are you sure you want to delete this column?
            </p>

            <div id="task-warning" class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4 hidden">
                <p class="text-sm text-yellow-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong id="task-count-text">0 tasks</strong> will be moved to another column or deleted.
                </p>
            </div>

            <div id="move-tasks-section" class="mb-4 hidden">
                <label class="block text-sm font-medium text-gray-700 mb-2 text-left">Move tasks to:</label>
                <select id="target-column-select" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500">
                    <option value="">-- Select a column --</option>
                </select>
            </div>
        </div>

        <div class="flex space-x-3 pt-4">
            <button onclick="confirmDeleteColumn()" class="flex-1 bg-red-600 text-white py-2 px-4 rounded-lg hover:bg-red-700 font-semibold transition-colors">
                <i class="fas fa-trash mr-2"></i>Delete Column
            </button>
            <button onclick="closeDeleteColumnModal()" class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400 font-semibold">
                Cancel
            </button>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
    // ===================================================================================
    // US DECOMPOSITION - Filter & Create Subtask Logic
    // ===================================================================================
    document.addEventListener('DOMContentLoaded', function() {
        const filterSelect = document.getElementById('user-story-filter');
        const createBtn = document.getElementById('create-subtask-btn');

        if (!filterSelect) return; // Nếu không có filter (không có sprint) thì thoát

        // Biến lưu User Story đang được chọn
        let selectedStoryId = null;
        let selectedStoryTitle = '';

        // // Restore filter từ sessionStorage sau khi reload
        // const savedStoryId = sessionStorage.getItem('selectedUserStory');
        // if (savedStoryId) {
        //     filterSelect.value = savedStoryId;
        //     sessionStorage.removeItem('selectedUserStory'); // Xóa sau khi dùng
        //     filterSelect.dispatchEvent(new Event('change')); // Trigger filter
        // }

        // Xử lý khi thay đổi filter
        filterSelect.addEventListener('change', function() {
            selectedStoryId = this.value;
            const selectedOption = this.options[this.selectedIndex];
            selectedStoryTitle = selectedOption ? selectedOption.text : '';

            // Filter task cards trong các cột To Do, In Progress, Done
            filterTasksByUserStory(selectedStoryId);

            // Enable/Disable nút Create Subtask
            if (createBtn) {
                if (selectedStoryId) {
                    createBtn.disabled = false;
                    createBtn.classList.remove('bg-gray-300', 'cursor-not-allowed');
                    createBtn.classList.add('bg-purple-600', 'hover:bg-purple-700');
                } else {
                    createBtn.disabled = true;
                    createBtn.classList.add('bg-gray-300', 'cursor-not-allowed');
                    createBtn.classList.remove('bg-purple-600', 'hover:bg-purple-700');
                }
            }

            console.log('Selected User Story:', selectedStoryId, selectedStoryTitle);
        });

        // Function để filter tasks theo User Story
        function filterTasksByUserStory(storyId) {
            const allTaskCards = document.querySelectorAll('.task-card');
            let visibleCount = { toDo: 0, inProgress: 0, done: 0 }; //Tạo một biến đếm tạm thời để theo dõi xem sau khi lọc xong thì mỗi cột (To Do, In Progress, Done) còn lại bao nhiêu task hiển thị.

            allTaskCards.forEach(card => {
                const parentId = card.getAttribute('data-parent-id');
                const column = card.closest('.task-column'); //tìm xem task này đang nằm ở cột nào.
                const columnStatus = column ? column.getAttribute('data-column-status') : null;//lấy tên trạng thái todo và done

                // Bỏ qua các task không có parent_id (User Stories)
                // Chỉ xử lý subtasks (tasks có parent_id)
                if (!parentId || parentId === '') {
                    return; // Skip User Stories
                }

                if (storyId === '') {//trường hợp storyId rỗng thì hiên thị tất cả suptask
                    // Không filter: Hiển thị tất cả subtasks
                    card.classList.remove('hidden');
                    if (columnStatus) visibleCount[columnStatus]++;//mỗi lần vòng lặp tìm thấy cột sẽ cộng số lượng vào cột đó
                } else {
                    // Filter: Chỉ hiển thị subtasks của User Story được chọn
                    if (parentId === storyId) {
                        card.classList.remove('hidden');
                        if (columnStatus) visibleCount[columnStatus]++;
                    } else {
                        card.classList.add('hidden');
                    }
                }
            });

            // Cập nhật counter badge của mỗi cột
            updateColumnCounters(visibleCount);

            console.log('Filtered tasks. Visible count:', visibleCount);
        }

        // Function để cập nhật số lượng task hiển thị trên badge
        function updateColumnCounters(counts) {
            const columns = document.querySelectorAll('.task-column');
            columns.forEach(column => {
                const status = column.getAttribute('data-column-status');
                const badge = column.closest('div').querySelector('h3 span');
                if (badge && counts[status] !== undefined) {
                    badge.textContent = counts[status];
                }
            });
        }

        // Xử lý khi click nút Create Subtask
        if (createBtn) {
            createBtn.addEventListener('click', function() {
                if (selectedStoryId) {
                    openSubtaskModal(selectedStoryId, selectedStoryTitle);
                }
            });
        }

        // Function mở Subtask Modal
        function openSubtaskModal(parentId, parentTitle) {
            const modal = document.getElementById('subtaskModal');
            const form = document.getElementById('subtask-form');
            const modalTitle = document.getElementById('subtask-modal-title');
            const parentTitleEl = document.getElementById('subtask-parent-title');
            const submitBtn = document.getElementById('subtask-submit-btn');

            if (!modal) return;

            // Reset form
            form.reset();
            document.getElementById('subtask-id').value = '';
            document.getElementById('subtask-parent-id').value = parentId;
            document.getElementById('subtask-method').value = 'POST';

            // Set modal title and parent info
            modalTitle.innerHTML = '<i class="fas fa-layer-group text-purple-600 mr-2"></i>Create Subtask';
            parentTitleEl.innerHTML = '<i class="fas fa-sitemap mr-1"></i>' + parentTitle;
            submitBtn.innerHTML = '<i class="fas fa-plus mr-2"></i>Create Subtask';

            // Show modal
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            // Focus vào input title
            setTimeout(() => {
                document.getElementById('subtask-title').focus();
            }, 100);
        }

        // Function đóng Subtask Modal
        window.closeSubtaskModal = function() {
            const modal = document.getElementById('subtaskModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }

        // Xử lý submit Subtask Form
        const subtaskForm = document.getElementById('subtask-form');
        if (subtaskForm) {
            subtaskForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const subtaskId = formData.get('subtask_id');
                const method = formData.get('_method');
                const isEdit = subtaskId && method === 'PATCH';

                // Chuẩn bị dữ liệu
                const data = {
                    title: formData.get('title'),
                    description: formData.get('description') || '',
                    priority: formData.get('priority'),
                    storyPoints: parseInt(formData.get('storyPoints')) || null,
                    assigned_to: formData.get('assigned_to') || null,
                    parent_id: formData.get('parent_id')
                };

                console.log('Subtask data being sent:', data); // Debug

                // Chỉ set status và sprint_id khi CREATE, không set khi EDIT
                if (!isEdit) {
                    data.status = 'toDo'; // Default status cho subtask mới
                    data.sprint_id = {{ $activeSprint->id ?? 'null' }}; // Gán vào sprint hiện tại
                }

                const url = isEdit ? `/tasks/${subtaskId}` : '/tasks';
                const submitBtn = document.getElementById('subtask-submit-btn');

                // Disable button và hiển thị loading
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';

                try {
                    const response = await fetch(url, {
                        method: isEdit ? 'PATCH' : 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(data)
                    });

                    const result = await response.json();

                    if (!response.ok) {
                        throw new Error(result.message || 'Failed to save subtask');
                    }

                    // Success
                    alert(isEdit ? 'Subtask updated successfully!' : 'Subtask created successfully!');
                    closeSubtaskModal();
                    window.location.reload();

                } catch (error) {
                    console.error('Error:', error);
                    alert('Error: ' + error.message);

                    // Re-enable button
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = isEdit ?
                        '<i class="fas fa-save mr-2"></i>Update Subtask' :
                        '<i class="fas fa-plus mr-2"></i>Create Subtask';
                }
            });
        }

        // ===================================================================================
        // EDIT & DELETE SUBTASK FUNCTIONS
        // ===================================================================================

        // Function để Edit Subtask
        window.editSubtask = async function(subtaskId) {
            try {
                // Fetch subtask data từ API
                const response = await fetch(`/tasks/${subtaskId}/edit`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Failed to load subtask');
                }

                const task = data.task;

                // Mở modal với dữ liệu từ API
                const modal = document.getElementById('subtaskModal');
                const form = document.getElementById('subtask-form');
                const modalTitle = document.getElementById('subtask-modal-title');
                const parentTitleEl = document.getElementById('subtask-parent-title');
                const submitBtn = document.getElementById('subtask-submit-btn');

                if (!modal) return;

                // Fill form với dữ liệu subtask
                form.reset();
                document.getElementById('subtask-id').value = task.id;
                document.getElementById('subtask-parent-id').value = task.parent_id;
                document.getElementById('subtask-method').value = 'PATCH';
                document.getElementById('subtask-title').value = task.title;
                document.getElementById('subtask-description').value = task.description || '';
                document.getElementById('subtask-priority').value = task.priority;
                // ✅ ĐÃ XÓA dòng set subtask-story-points - Vì subtask không có points
                document.getElementById('subtask-assigned-to').value = task.assigned_to || '';

                // Tìm tên User Story từ dropdown
                const parentOption = document.querySelector(`#user-story-filter option[value="${task.parent_id}"]`);
                const parentTitle = parentOption ? parentOption.textContent : `US #${task.parent_id}`;

                // Set modal title
                modalTitle.innerHTML = '<i class="fas fa-edit text-purple-600 mr-2"></i>Edit Subtask';
                parentTitleEl.innerHTML = '<i class="fas fa-sitemap mr-1"></i>' + parentTitle;
                submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Update Subtask';

                // Show modal
                modal.classList.remove('hidden');
                modal.classList.add('flex');

                // Focus vào title
                setTimeout(() => {
                    document.getElementById('subtask-title').focus();
                }, 100);

            } catch (error) {
                console.error('Error loading subtask:', error);
                alert('Error loading subtask: ' + error.message);
            }
        }

        // Function để Delete Subtask
        window.deleteSubtask = async function(subtaskId) {
            if (!confirm('Are you sure you want to delete this subtask?\n\nThis action cannot be undone.')) {
                return;
            }

            try {
                const response = await fetch(`/tasks/${subtaskId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Failed to delete subtask');
                }

                // Success
                alert('Subtask deleted successfully!');

                // Reload page
                window.location.reload();

            } catch (error) {
                console.error('Error deleting subtask:', error);
                alert('Error deleting subtask: ' + error.message);
            }
        }
    });

    // ===================================================================================
    // COMMENT SYSTEM
    // ===================================================================================
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

    // --- DRAG AND DROP LOGIC (Simplified) ---
    function allowDrop(ev) {
        ev.preventDefault();
        const isSprintActive = {{ $activeSprint ? 'true' : 'false' }};

        // Only allow drag-drop when sprint is active
        if (isSprintActive) {
            const targetColumn = ev.currentTarget;
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

    async function drop(ev) {
        ev.preventDefault();
        const targetColumn = ev.currentTarget;
        dragLeave(ev);

        const isSprintActive = {{ $activeSprint ? 'true' : 'false' }};

        // Block if sprint hasn't started
        if (!isSprintActive) {
            alert('Sprint has not started yet. Please start a sprint to move tasks.');
            return;
        }

        const newStatusId = targetColumn.dataset.columnStatus;
        const taskId = ev.dataTransfer.getData("text/plain");

        try {
            const response = await fetch(`/tasks/${taskId}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ status_id: newStatusId })
            });

            const result = await response.json();
            if (!response.ok) {
                throw new Error(result.message || 'Update failed.');
            }
            location.reload();
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
                        window.location.reload();
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

    // 🔥 THÊM: Kéo-thả sắp xếp column
    document.addEventListener('DOMContentLoaded', function() {
        const boardContainer = document.querySelector('.flex.h-full.gap-6.min-w-full');

        if (!boardContainer) return;

        // Khởi tạo SortableJS cho columns
        new Sortable(boardContainer, {
            animation: 150,
            ghostClass: 'opacity-50',
            dragClass: 'bg-blue-50',
            handle: '.task-column-header', // Chỉ kéo từ header
            onEnd: async function(evt) {
                // Lấy danh sách ID cột mới theo thứ tự
                const columnIds = Array.from(boardContainer.querySelectorAll('[data-column-status]'))
                    .map(col => parseInt(col.dataset.columnStatus));

                // Gọi API để cập nhật thứ tự
                try {
                    const response = await fetch('/task-statuses/reorder', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ order: columnIds })
                    });

                    const result = await response.json();
                    if (!response.ok) {
                        throw new Error(result.message || 'Reorder failed');
                    }
                    console.log('✅ Columns reordered:', columnIds);
                } catch (error) {
                    alert('Error reordering columns: ' + error.message);
                    location.reload(); // Reload để rollback
                }
            }
        });
    });

    // 🔥 Add Column Modal Functions
    window.openAddColumnModal = function() {
        const modal = document.getElementById('addColumnModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.getElementById('column-name').focus();
        }
    };

    window.closeAddColumnModal = function() {
        const modal = document.getElementById('addColumnModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.getElementById('add-column-form').reset();
        }
    };

    // Handle form submission for Add Column
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('add-column-form');
        if (form) {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                const formData = {
                    name: document.getElementById('column-name').value,
                    color_class: document.querySelector('input[name="color_class"]:checked').value
                };

                // Validation
                if (!formData.name.trim()) {
                    alert('Please enter a column name');
                    return;
                }

                try {
                    const response = await fetch('/task-statuses', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(formData)
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
                            throw new Error(result.message || 'Failed to create column');
                        }
                    } else {
                        alert('✅ Column created successfully!');
                        window.closeAddColumnModal();
                        window.location.reload();
                    }
                } catch (error) {
                    alert('❌ Error: ' + error.message);
                }
            });
        }
    });

    // 🔥 Delete Column Functions
    let columnToDelete = null;

    window.deleteColumn = function(columnId, columnName, taskCount) {
        columnToDelete = { id: columnId, name: columnName, taskCount: taskCount };

        const modal = document.getElementById('deleteColumnModal');
        const messageEl = document.getElementById('delete-column-message');
        const warningEl = document.getElementById('task-warning');
        const moveTasksSection = document.getElementById('move-tasks-section');
        const taskCountText = document.getElementById('task-count-text');

        // Update message
        messageEl.textContent = `Delete column "${columnName}"?`;

        if (taskCount > 0) {
            // Show warning
            warningEl.classList.remove('hidden');
            taskCountText.textContent = taskCount + ' task' + (taskCount > 1 ? 's' : '');

            // Show move tasks section
            moveTasksSection.classList.remove('hidden');
            populateTargetColumns(columnId);
        } else {
            warningEl.classList.add('hidden');
            moveTasksSection.classList.add('hidden');
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };

    window.closeDeleteColumnModal = function() {
        const modal = document.getElementById('deleteColumnModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        columnToDelete = null;
    };

    function populateTargetColumns(excludeColumnId) {
        const select = document.getElementById('target-column-select');
        select.innerHTML = '<option value="">-- Select a column --</option>';

        // Get all columns except current one
        const columns = document.querySelectorAll('[data-column-status]');
        columns.forEach(col => {
            const colId = parseInt(col.dataset.columnStatus);
            if (colId !== excludeColumnId) {
                // Header is sibling of dropzone, so climb to wrapper then find h3
                const wrapper = col.closest('.flex-shrink-0');
                const headerText = wrapper ? wrapper.querySelector('.task-column-header h3')?.textContent?.trim() : 'Unknown';
                const option = document.createElement('option');
                option.value = colId;
                option.textContent = headerText || 'Unknown';
                select.appendChild(option);
            }
        });
    }

    window.confirmDeleteColumn = async function() {
        if (!columnToDelete) return;

        const { id: columnId, taskCount } = columnToDelete;
        let moveToColumnId = null;

        // If there are tasks, user must select target column
        if (taskCount > 0) {
            moveToColumnId = document.getElementById('target-column-select').value;
            if (!moveToColumnId) {
                alert('⚠️ Please select a column to move tasks to');
                return;
            }
        }

        try {
            const response = await fetch(`/task-statuses/${columnId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    move_to_status_id: moveToColumnId ? parseInt(moveToColumnId) : null
                })
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'Failed to delete column');
            }

            alert('✅ Column deleted successfully!');
            window.closeDeleteColumnModal();
            window.location.reload();
        } catch (error) {
            alert('❌ Error: ' + error.message);
        }
    };

</script>
@endpush

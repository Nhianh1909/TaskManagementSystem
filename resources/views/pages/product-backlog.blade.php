
@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-gray-800">Product Backlog</h1>
            {{-- BUTTON CREATE EPIC VỚI HÀM ONCLICK LÀ openCreateModal() --}}
            <button onclick="openCreateModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-md shadow hover:bg-indigo-500 focus:outline-none">
                <span class="text-lg">+</span>
                <span>Create Epic</span>
            </button>
        </div>
        {{-- BỘ LỌC --}}
        <div class="grid grid-cols-12 gap-6">
            <!-- Sidebar -->
            {{-- <aside class="col-span-3 bg-white rounded-lg p-4 shadow">
                <h2 class="font-medium text-gray-700 mb-3">Filters</h2>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Search</label>
                        <input type="text" id="filter-search" placeholder="Search items..." class="w-full px-3 py-2 border rounded focus:ring-2 focus:ring-indigo-200">
                    </div>

                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Sprint</label>
                        <select id="filter-sprint" class="w-full px-3 py-2 border rounded">
                            <option value="all">All Sprints</option>
                            <option value="sprint-1">Sprint 1</option>
                            <option value="sprint-2">Sprint 2</option>
                            <option value="sprint-3">Sprint 3</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Epic</label>
                        <select id="filter-epic" class="w-full px-3 py-2 border rounded">
                            <option value="all">All Epics</option>
                            <option value="auth">User Authentication</option>
                            <option value="payments">Payment Integration</option>
                            <option value="reports">Reporting Dashboard</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm text-gray-600 mb-2">Status</label>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" id="status-all" onchange="toggleStatusAll()">
                                <span>All</span>
                            </label>
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" id="status-todo" onchange="toggleStatus('todo')">
                                <span>To Do</span>
                            </label>
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" id="status-inprogress" onchange="toggleStatus('inprogress')">
                                <span>In Progress</span>
                            </label>
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" id="status-done" onchange="toggleStatus('done')">
                                <span>Done</span>
                            </label>
                        </div>
                    </div>
                </div>
            </aside> --}}

            <!-- Main content -->
            <main class="col-span-6">
                <div class="space-y-4">
                    <section>
                        <h3 class="text-sm text-gray-500 mb-3">Product Backlog</h3>
                        <div class="space-y-4">
                            {{-- VÒNG LẶP LẤY RA CÁC EPIC --}}
                            @foreach($getEpics as $epic)
                                <div class="bg-white rounded-lg shadow hover:shadow-md">
                                    <div class="p-4">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-3">
                                                    {{-- BUTTON MỞ RỘNG USERSTORIES ĐỂ XEM --}}
                                                    <button type="button"
                                                            class="flex items-center justify-center w-8 h-8 rounded hover:bg-gray-100 expand-btn"
                                                            onclick="toggleExpand({{ $epic->id }})"
                                                            id="expand-btn-{{ $epic->id }}"
                                                            aria-label="Toggle expand">
                                                            {{-- NÚT MŨI TÊN ĐỂ MỠ RỘNG --}}
                                                        <svg class="w-4 h-4 text-gray-600 transform transition-transform"
                                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                        </svg>
                                                    </button>
                                                    {{-- NHẤN VÀO TIÊU ĐỀ SẼ MỞ PANEL CỦA EPIC --}}
                                                    <h4 class="text-lg font-semibold text-gray-800 truncate cursor-pointer hover:text-indigo-600"
                                                        onclick="openEpicPanel({{ $epic->id }})">
                                                        {{ $epic->title }}
                                                    </h4>
                                                </div>
                                                <p class="text-sm text-gray-600 mt-2">Decription: {{ $epic->description }}</p>
                                            </div>

                                            <div class="ml-4 text-right">
                                                <div class="text-sm text-gray-500">{{ $epic->userStories->count() }} stories</div>
                                                <div class="text-sm text-gray-700 font-medium">{{ $epic->userStories->sum('storyPoints') }} pts</div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- HIỂN THỊ USERSTORIES THÔNG QUA VÒNG LẶP ĐƯỢC LẤY RA TIẾP TỪ EPIC --}}
                                    <div id="stories-{{ $epic->id }}" class="hidden border-t">
                                        <div class="p-3 space-y-2 bg-gray-50">
                                            @foreach($epic->userStories as $story)
                                            {{-- CLICK VÀO SẼ MỞ PANEL CỦA USERSTORIES --}}
                                                <div class="flex items-center justify-between px-3 py-2 bg-white rounded shadow-sm cursor-pointer"
                                                     onclick="openStoryPanel({{ $story->id }})">
                                                    <div>
                                                        <div class="text-sm text-gray-800">{{ $story->title }}</div>
                                                        <div class="text-xs text-gray-500">Decription: {{ $story->description }}</div>
                                                    </div>
                                                    <div class="text-sm text-gray-600">{{ $story->storyPoints ?? 0 }} pts</div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                    {{-- PHẦN HIỂN THỊ CÁC USERSTORIES CHƯA ĐƯỢC GÁN VÀO EPIC NÀO --}}
                    <section class="mt-6 bg-white rounded-lg p-4 shadow">
                        @if($tasksWithoutEpic->count() > 0)
                            <h4 class="text-sm font-medium text-gray-700 mb-3">UNASSIGNED STORIES</h4>
                            <div class="space-y-3">
                                {{-- CHẠY VÒNG LẶP TASKWITHOUTEPIC ĐÃ ĐƯỢC GỌI TRONG CONTROLLER --}}
                                @foreach($tasksWithoutEpic as $task)
                                    <div class="flex items-center justify-between bg-gray-50 rounded px-3 py-2 cursor-pointer"
                                         onclick="openStoryPanel({{ $task->id }})">
                                        <div>
                                            <div class="text-sm font-medium text-gray-800">{{ $task->title }}</div>
                                            <div class="text-xs text-gray-500">{{ $task->description }}</div>
                                        </div>
                                        <div class="text-sm text-gray-600">{{ $task->storyPoints ?? 0 }} pts</div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <h4 class="text-sm font-medium text-gray-500 mb-3">No Unassigned Stories</h4>
                        @endif
                    </section>
                </div>
            </main>

            <!-- Right spacer (for centered layout) -->
            <div class="col-span-3"></div>
        </div>
    </div>
    {{-- MODAL CREATE EPIC KHI ĐƯỢC BẬT. TẠM THỜI LÀ HIDDEN --}}
    <!-- Create Epic Modal -->
    <div id="create-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
        <!-- Backdrop -->
        {{-- NHẤN VÀO VÙNG NGOÀI SẼ ĐÓNG MODAL --}}
        <div class="fixed inset-0 bg-black bg-opacity-50" onclick="closeCreateModal()"></div>

        <!-- Modal Content -->
        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4 z-10">
            <div class="flex justify-between items-center p-6 border-b">
                <h3 class="text-xl font-semibold text-gray-800">Create New Epic</h3>
                {{-- HOẶC DẤU X ĐỂ ĐÓNG --}}
                <button onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
            </div>
            {{-- FORM SUBMIT SAU KHI ĐÃ ĐIỀN THÔNG TIN VÀO INPUT --}}
            <form onsubmit="submitCreateEpic(event)">
                <div class="p-6 space-y-4">
                    <!-- Epic Title -->
                    <div>
                        <label for="epic-title" class="block text-sm font-medium text-gray-700 mb-1">
                            Epic Title <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               id="epic-title"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="e.g., User Authentication & Authorization"
                               required>
                    </div>

                    <!-- Full Description -->
                    <div>
                        <label for="epic-description" class="block text-sm font-medium text-gray-700 mb-1">
                            Full Description
                        </label>
                        <textarea id="epic-description"
                                  rows="4"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                  placeholder="Detailed description of the epic..."></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 p-6 border-t bg-gray-50">
                    {{-- HOẶC LÀ NHẤN NÚT CANCEL SẼ ĐÓNG MODAL --}}
                    <button type="button"
                            onclick="closeCreateModal()"
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-100">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        Create Epic
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Detail Panel cho từng Epic  -->
    @foreach($getEpics as $epic)
    <div id="epic-panel-{{ $epic->id }}" class="hidden fixed inset-0 z-[60]" style="pointer-events: auto;">
        <!-- Overlay -->
        {{-- DÒNG CODE DƯỚI LÀ TẠO BACKDROP VỚI HÀM ĐÓNG EPICPANEL --}}
        <div class="fixed inset-0 bg-black bg-opacity-40" onclick="closeEpicPanel({{ $epic->id }})"></div>

        <!-- Panel Content -->
        <aside class="ml-auto w-96 bg-white h-full shadow-xl z-[70] flex flex-col relative transform transition-transform duration-300">
            <div class="p-4 flex items-start justify-between border-b">
                <h3 class="text-lg font-semibold text-gray-800">Epic Details</h3>
                <button class="text-gray-600 hover:text-gray-900 text-2xl leading-none" onclick="closeEpicPanel({{ $epic->id }})">&times;</button>
            </div>

            <div class="p-4 overflow-y-auto h-full">
                <!-- Epic Information - VIEW MODE -->
                <div id="epic-view-{{ $epic->id }}">
                    <h4 class="text-xl font-bold text-gray-800">{{ $epic->title }}</h4>
                    <div class="mt-2 text-sm text-gray-600">{{ $epic->description }}</div>
                </div>

                <!-- Epic Information - EDIT MODE (hidden by default) -->
                <div id="epic-edit-{{ $epic->id }}" class="hidden space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Epic Title</label>
                        <input type="text"
                               id="epic-title-edit-{{ $epic->id }}"
                               value="{{ $epic->title }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea id="epic-desc-edit-{{ $epic->id }}"
                                  rows="4"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">{{ $epic->description }}</textarea>
                    </div>
                </div>

                <div class="mt-6">
                    <div class="flex items-center justify-between mb-2">
                        <div class="text-sm font-medium text-gray-700">User Stories ({{ $epic->userStories->count() }})</div>
                        {{-- BUTTON THÊM USERSTORIES SAU KHI ĐÃ TẠO EPIC CÒN TRỐNG HOẶC MUỐN TẠO THÊM USERSTORIES CHO EPIC CŨ --}}
                        <button onclick="openCreateStoryModal({{ $epic->id }}, '{{ addslashes($epic->title) }}')"
                                class="flex items-center justify-center w-6 h-6 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-lg leading-none">
                            +
                        </button>
                    </div>
                    {{-- DÙNG VÒNG LẶP ĐỂ ĐẾMM SỐ LƯỢNG USERSTORIES  --}}
                    @if($epic->userStories->count() > 0)
                        <ul class="space-y-2">
                            @foreach($epic->userStories as $story)
                                <li class="flex items-center justify-between bg-gray-50 rounded px-3 py-2">
                                    <div>
                                        <div class="text-sm text-gray-800">{{ $story->title }}</div>
                                        <div class="text-xs text-gray-500">{{ $story->description }}</div>
                                    </div>
                                    <div class="text-sm text-gray-600">{{ $story->storyPoints ?? 0 }} pts</div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-sm text-gray-500 italic">No user stories yet</div>
                    @endif
                </div>
                {{-- CÁC NÚT CRUD CHO PANEL EPIC --}}
                <div class="mt-6 flex gap-3">
                    <!-- VIEW MODE BUTTONS -->
                    <button id="epic-btn-edit-{{ $epic->id }}"
                            onclick="toggleEditEpicMode({{ $epic->id }})"
                            class="flex-1 px-3 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                        Edit
                    </button>
                    <button id="epic-btn-delete-{{ $epic->id }}"
                            onclick="deleteEpic({{ $epic->id }})"
                            class="flex-1 px-3 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                        Delete
                    </button>

                    <!-- EDIT MODE BUTTONS (hidden by default) -->
                    <button id="epic-btn-cancel-{{ $epic->id }}"
                            onclick="cancelEditEpic({{ $epic->id }})"
                            class="hidden flex-1 px-3 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                        Cancel
                    </button>
                    <button id="epic-btn-save-{{ $epic->id }}"
                            onclick="saveEditEpic({{ $epic->id }})"
                            class="hidden flex-1 px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                        Save
                    </button>
                </div>
            </div>
        </aside>
    </div>
    @endforeach

    <!-- Detail Panel cho từng User Story (cách thủ công) -->
    @foreach($getEpics as $epic)
        @foreach($epic->userStories as $story)
        <div id="story-panel-{{ $story->id }}" class="hidden fixed inset-0 z-[60]" style="pointer-events: auto;">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-black bg-opacity-40" onclick="closeStoryPanel({{ $story->id }})"></div>

            <!-- Panel Content -->
            <aside class="ml-auto w-96 bg-white h-full shadow-xl z-[70] flex flex-col relative transform transition-transform duration-300">
                <div class="p-4 flex items-start justify-between border-b">
                    <h3 class="text-lg font-semibold text-gray-800">User Story Details</h3>
                    <button class="text-gray-600 hover:text-gray-900 text-2xl leading-none" onclick="closeStoryPanel({{ $story->id }})">&times;</button>
                </div>

                <div class="p-4 overflow-y-auto h-full">
                    <!-- Story Information - VIEW MODE -->
                    <div id="story-view-{{ $story->id }}">
                        <h4 class="text-xl font-bold text-gray-800">{{ $story->title }}</h4>
                        <div class="mt-2 text-sm text-gray-600">{{ $story->description }}</div>

                        <div class="mt-4 space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-700">Status:</span>
                                {{-- SỬ DỤNG MATCH ĐỂ GỌN HƠN, NÓ LÀ METHOD TỐI ƯU HƠN DÙNG SWITCH CASE --}}
                                @php
                                    $statusClass = match($story->status) {
                                        'toDo' => 'bg-gray-100 text-gray-800',
                                        'inProgress' => 'bg-yellow-100 text-yellow-800',
                                        'done' => 'bg-green-100 text-green-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                    $statusText = match($story->status) {
                                        'toDo' => 'To Do',
                                        'inProgress' => 'In Progress',
                                        'done' => 'Done',
                                        default => 'Unknown'
                                    };
                                @endphp
                                <span class="px-2 py-1 rounded text-xs {{ $statusClass }}">
                                    {{ $statusText }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-700">Story Points:</span>
                                <span>{{ $story->storyPoints ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-700">Priority:</span>
                                <span class="capitalize">{{ $story->priority ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-700">Assigned To:</span>
                                <span>{{ $story->assignee ? $story->assignee->name : 'Unassigned' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-700">Epic:</span>
                                <span>{{ $epic->title }}</span>
                            </div>
                        </div>
                    </div>
                    {{-- FORM EDIT USERSTORIES --}}
                    <!-- Story Information - EDIT MODE (hidden by default) -->
                    <div id="story-edit-{{ $story->id }}" class="hidden space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                            <input type="text"
                                   id="story-title-edit-{{ $story->id }}"
                                   value="{{ $story->title }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea id="story-desc-edit-{{ $story->id }}"
                                      rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">{{ $story->description }}</textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select id="story-status-edit-{{ $story->id }}"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">
                                    <option value="toDo" {{ $story->status === 'toDo' ? 'selected' : '' }}>To Do</option>
                                    <option value="inProgress" {{ $story->status === 'inProgress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="done" {{ $story->status === 'done' ? 'selected' : '' }}>Done</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Story Points</label>
                                <input type="number"
                                       id="story-points-edit-{{ $story->id }}"
                                       value="{{ $story->storyPoints ?? '' }}"
                                       min="0"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                                <select id="story-priority-edit-{{ $story->id }}"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">
                                    <option value="low" {{ $story->priority === 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ $story->priority === 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="high" {{ $story->priority === 'high' ? 'selected' : '' }}>High</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Assigned To</label>
                                <select id="story-assignee-edit-{{ $story->id }}"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">
                                    <option value="">Unassigned</option>
                                    {{-- LẤY RA TỪ BIẾN TEAM SHOW RA TOÀN BỘ THÀNH VIÊN. SAU ĐÓ SẼ SHOW RA LIST USER CỦA TEAM ĐÓ --}}
                                    @if($team)
                                        @foreach($team->users as $member)
                                            <option value="{{ $member->id }}" {{ $story->assigned_to == $member->id ? 'selected' : '' }}>
                                                {{ $member->name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Epic</label>
                            <input type="text"
                                   value="{{ $epic->title }}"
                                   class="w-full px-3 py-2 border border-gray-300 bg-gray-100 rounded-md cursor-not-allowed"
                                   readonly>
                        </div>
                    </div>
                    {{-- CÁC NÚT CRUD USERSTORIES --}}
                    <div class="mt-6 flex gap-3">
                        <!-- VIEW MODE BUTTONS -->
                        <button id="story-btn-edit-{{ $story->id }}"
                                onclick="toggleEditStoryMode({{ $story->id }})"
                                class="flex-1 px-3 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                            Edit
                        </button>
                        <button id="story-btn-delete-{{ $story->id }}"
                                onclick="deleteStory({{ $story->id }})"
                                class="flex-1 px-3 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                            Delete
                        </button>

                        <!-- EDIT MODE BUTTONS (hidden by default) -->
                        <button id="story-btn-cancel-{{ $story->id }}"
                                onclick="cancelEditStory({{ $story->id }})"
                                class="hidden flex-1 px-3 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                            Cancel
                        </button>
                        <button id="story-btn-save-{{ $story->id }}"
                                onclick="saveEditStory({{ $story->id }})"
                                class="hidden flex-1 px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                            Save
                        </button>
                    </div>
                </div>
            </aside>
        </div>
        @endforeach
    @endforeach

    <!-- DETAIL PANEL UNASSIGNED -->
    @foreach($tasksWithoutEpic as $task)
    <div id="story-panel-{{ $task->id }}" class="hidden fixed inset-0 z-[60]" style="pointer-events: auto;">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-black bg-opacity-40" onclick="closeStoryPanel({{ $task->id }})"></div>

        <!-- Panel Content -->
        <aside class="ml-auto w-96 bg-white h-full shadow-xl z-[70] flex flex-col relative transform transition-transform duration-300">
            <div class="p-4 flex items-start justify-between border-b">
                <h3 class="text-lg font-semibold text-gray-800">User Story Details</h3>
                <button class="text-gray-600 hover:text-gray-900 text-2xl leading-none" onclick="closeStoryPanel({{ $task->id }})">&times;</button>
            </div>

            <div class="p-4 overflow-y-auto h-full">
                <!-- Story Information - VIEW MODE -->
                <div id="story-view-{{ $task->id }}">
                    <h4 class="text-xl font-bold text-gray-800">{{ $task->title }}</h4>
                    <div class="mt-2 text-sm text-gray-600">{{ $task->description }}</div>

                    <div class="mt-4 space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-700">Status:</span>
                            @php
                                $statusClass = match($task->status) {
                                    'toDo' => 'bg-gray-100 text-gray-800',
                                    'inProgress' => 'bg-yellow-100 text-yellow-800',
                                    'done' => 'bg-green-100 text-green-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                                $statusText = match($task->status) {
                                    'toDo' => 'To Do',
                                    'inProgress' => 'In Progress',
                                    'done' => 'Done',
                                    default => 'Unknown'
                                };
                            @endphp
                            <span class="px-2 py-1 rounded text-xs {{ $statusClass }}">
                                {{ $statusText }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-700">Story Points:</span>
                            <span>{{ $task->storyPoints ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-700">Priority:</span>
                            <span class="capitalize">{{ $task->priority ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-700">Assigned To:</span>
                            <span>{{ $task->assignee ? $task->assignee->name : 'Unassigned' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-700">Epic:</span>
                            <span class="text-gray-500 italic">No Epic</span>
                        </div>
                    </div>
                </div>

                <!-- Story Information - EDIT MODE (hidden by default) -->
                <div id="story-edit-{{ $task->id }}" class="hidden space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                        <input type="text"
                               id="story-title-edit-{{ $task->id }}"
                               value="{{ $task->title }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea id="story-desc-edit-{{ $task->id }}"
                                  rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">{{ $task->description }}</textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select id="story-status-edit-{{ $task->id }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">
                                <option value="toDo" {{ $task->status === 'toDo' ? 'selected' : '' }}>To Do</option>
                                <option value="inProgress" {{ $task->status === 'inProgress' ? 'selected' : '' }}>In Progress</option>
                                <option value="done" {{ $task->status === 'done' ? 'selected' : '' }}>Done</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Story Points</label>
                            <input type="number"
                                   id="story-points-edit-{{ $task->id }}"
                                   value="{{ $task->storyPoints ?? '' }}"
                                   min="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                            <select id="story-priority-edit-{{ $task->id }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">
                                <option value="low" {{ $task->priority === 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ $task->priority === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ $task->priority === 'high' ? 'selected' : '' }}>High</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Assigned To</label>
                            <select id="story-assignee-edit-{{ $task->id }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">
                                <option value="">Unassigned</option>
                                @if($team)
                                    @foreach($team->users as $member)
                                        <option value="{{ $member->id }}" {{ $task->assigned_to == $member->id ? 'selected' : '' }}>
                                            {{ $member->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Epic</label>
                        <input type="text"
                               value="No Epic"
                               class="w-full px-3 py-2 border border-gray-300 bg-gray-100 rounded-md cursor-not-allowed"
                               readonly>
                    </div>
                </div>

                <div class="mt-6 flex gap-3">
                    <!-- VIEW MODE BUTTONS -->
                    <button id="story-btn-edit-{{ $task->id }}"
                            onclick="toggleEditStoryMode({{ $task->id }})"
                            class="flex-1 px-3 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                        Edit
                    </button>
                    <button id="story-btn-delete-{{ $task->id }}"
                            onclick="deleteStory({{ $task->id }})"
                            class="flex-1 px-3 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                        Delete
                    </button>

                    <!-- EDIT MODE BUTTONS (hidden by default) -->
                    <button id="story-btn-cancel-{{ $task->id }}"
                            onclick="cancelEditStory({{ $task->id }})"
                            class="hidden flex-1 px-3 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                        Cancel
                    </button>
                    <button id="story-btn-save-{{ $task->id }}"
                            onclick="saveEditStory({{ $task->id }})"
                            class="hidden flex-1 px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                        Save
                    </button>
                </div>
            </div>
        </aside>
    </div>
    @endforeach

    <!-- Create User Story Modal -->
    <div id="create-story-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50" onclick="closeCreateStoryModal()"></div>

        <!-- Modal Content -->
        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4 z-10 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center p-6 border-b sticky top-0 bg-white z-10">
                <h3 class="text-xl font-semibold text-gray-800">Create New User Story</h3>
                <button onclick="closeCreateStoryModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
            </div>

            <form onsubmit="submitCreateStory(event)">
                <div class="p-6 space-y-4">
                    <!-- Story Title -->
                    <div>
                        <label for="story-title" class="block text-sm font-medium text-gray-700 mb-1">
                            User Story Title <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               id="story-title"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="e.g., As a user, I want to login with email"
                               required>
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="story-description" class="block text-sm font-medium text-gray-700 mb-1">
                            Description
                        </label>
                        <textarea id="story-description"
                                  rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                  placeholder="Detailed description..."></textarea>
                    </div>

                    <!-- Row 1: Status and Story Points -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="story-status" class="block text-sm font-medium text-gray-700 mb-1">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select id="story-status"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    required>
                                <option value="toDo">To Do</option>
                                <option value="inProgress">In Progress</option>
                                <option value="done">Done</option>
                            </select>
                        </div>

                        <div>
                            <label for="story-points" class="block text-sm font-medium text-gray-700 mb-1">
                                Story Points
                            </label>
                            <input type="number"
                                   id="story-points"
                                   min="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="e.g., 5">
                        </div>
                    </div>

                    <!-- Row 2: Priority and Assigned To -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="story-priority" class="block text-sm font-medium text-gray-700 mb-1">
                                Priority <span class="text-red-500">*</span>
                            </label>
                            <select id="story-priority"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    required>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>

                        <div>
                            <label for="story-assignee" class="block text-sm font-medium text-gray-700 mb-1">
                                Assigned To
                            </label>
                            <select id="story-assignee"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Unassigned</option>
                                @if($team)
                                    @foreach($team->users as $member)
                                        <option value="{{ $member->id }}">{{ $member->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>

                    <!-- Epic (Read-only) -->
                    <div>
                        <label for="story-epic-display" class="block text-sm font-medium text-gray-700 mb-1">
                            Epic
                        </label>
                        <input type="text"
                               id="story-epic-display"
                               class="w-full px-3 py-2 border border-gray-300 bg-gray-100 rounded-md cursor-not-allowed"
                               readonly>
                        <input type="hidden" id="story-epic-id">
                    </div>
                </div>

                <div class="flex justify-end gap-3 p-6 border-t bg-gray-50">
                    <button type="button"
                            onclick="closeCreateStoryModal()"
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-100">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        Create User Story
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
    // ==========================================
    // JAVASCRIPT THUẦN - KHÔNG DÙNG ALPINE.JS
    // ==========================================

    // --- 1. DETAIL PANEL FUNCTIONS ---
    // Mở Epic Panel
    function openEpicPanel(epicId) {
        closeAllPanels(); // Đóng tất cả panel khác trước
        const panel = document.getElementById('epic-panel-' + epicId);
        panel.classList.remove('hidden');
        // panel.classList.add('flex'); // Không cần vì <aside> bên trong đã có flex sẵn
    }

    // Đóng Epic Panel
    function closeEpicPanel(epicId) {
        const panel = document.getElementById('epic-panel-' + epicId);
        panel.classList.add('hidden');
        // panel.classList.remove('flex'); // Không cần vì chỉ toggle hidden là đủ
    }

    // Mở Story Panel
    function openStoryPanel(storyId) {
        closeAllPanels(); // Đóng tất cả panel khác trước
        const panel = document.getElementById('story-panel-' + storyId);
        panel.classList.remove('hidden');
        // panel.classList.add('flex'); // Không cần vì <aside> bên trong đã có flex sẵn
    }

    // Đóng Story Panel
    function closeStoryPanel(storyId) {
        const panel = document.getElementById('story-panel-' + storyId);
        panel.classList.add('hidden');
        // panel.classList.remove('flex'); // Không cần vì chỉ toggle hidden là đủ
    }

    // Đóng tất cả panel (Epic và Story) trước khi mở một panel mới đã sử dụng trong các hàm open
    function closeAllPanels() {
        // Đóng tất cả Epic panels, công thức CSS [id^="epic-panel-"] là một CSS Attribute Selector, có nghĩa là
        // epic-panel- bắt đầu từ id NÀO ĐÓ
        document.querySelectorAll('[id^="epic-panel-"]').forEach(panel => {
            panel.classList.add('hidden');
            // panel.classList.remove('flex'); // Không cần
        });
        // Đóng tất cả Story panels
        document.querySelectorAll('[id^="story-panel-"]').forEach(panel => {
            panel.classList.add('hidden');
            // panel.classList.remove('flex'); // Không cần
        });
    }

    // --- 2. CREATE MODAL FUNCTIONS ---
    // Mở Create Epic Modal
    function openCreateModal() {
        const modal = document.getElementById('create-modal');// Lấy phần tử modal
        modal.classList.remove('hidden');
        // modal.classList.add('flex'); // Không cần, modal content bên trong đã có flex để căn giữa

        // Reset form về trống
        document.getElementById('epic-title').value = '';
        document.getElementById('epic-description').value = '';
    }

    // Đóng Create Epic Modal
    function closeCreateModal() {
        const modal = document.getElementById('create-modal');
        modal.classList.add('hidden');
        // modal.classList.remove('flex'); // Không cần
    }

    // Submit Create Epic Form
    function submitCreateEpic(event) {
        // Chặn hành vi submit mặc định (không reload trang)
        event.preventDefault();

        // Lấy dữ liệu từ form
        const title = document.getElementById('epic-title').value;
        const description = document.getElementById('epic-description').value;

        // Validate
        if (!title.trim()) {
            alert('Please fill in the Epic title');
            return;
        }

        // Chuẩn bị dữ liệu để gửi
        const epicData = {
            title: title,
            description: description
        };

        console.log('Submitting epic:', epicData);

        // Gửi AJAX request đến backend
        fetch("{{ route('epics.store') }}", {  // URL = route đã tạo ở Bước 1
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',  // Gửi dạng JSON
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')  // CSRF token bắt buộc
            },
            body: JSON.stringify({  // Chuyển dữ liệu thành JSON
                title: title,
                description: description
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Epic created successfully:', data);
            closeCreateModal(); // Đóng modal
            alert('Epic created successfully!');
            window.location.reload(); // Reload trang để hiển thị epic mới
        })
        .catch(error => {
            console.error('Error creating epic:', error);
            alert('Failed to create epic. Please try again.');
        });
    }

    // --- 2B. CREATE USER STORY MODAL FUNCTIONS ---
    let currentEpicId = null; // Biến lưu epic_id hiện tại
    let epicTitles = {}; // Object lưu tên Epic theo ID
    //truyền title bằng null để tránh lỗi epicTitles undefined
    // Mở Create User Story Modal
    function openCreateStoryModal(epicId, epicTitle = null) {
        currentEpicId = epicId;
        const modal = document.getElementById('create-story-modal');
        modal.classList.remove('hidden');
        // modal.classList.add('flex'); // Không cần, modal content đã có flex để căn giữa

        // Reset form về trống
        document.getElementById('story-title').value = '';
        document.getElementById('story-description').value = '';
        document.getElementById('story-status').value = 'toDo';
        document.getElementById('story-points').value = '';
        document.getElementById('story-priority').value = 'medium';
        document.getElementById('story-assignee').value = '';

    // Hiển thị tên Epic (read-only)
    document.getElementById('story-epic-id').value = epicId;
    const displayTitle = epicTitle != null ? epicTitle : (epicTitles[epicId] || ('Epic #' + epicId));
    document.getElementById('story-epic-display').value = displayTitle;
    }

    // Đóng Create User Story Modal
    function closeCreateStoryModal() {
        const modal = document.getElementById('create-story-modal');
        modal.classList.add('hidden');
        // modal.classList.remove('flex'); // Không cần
        currentEpicId = null;
    }

    // Submit Create User Story Form
    function submitCreateStory(event) {
        event.preventDefault();

        // Lấy dữ liệu từ form
        const storyData = {
            title: document.getElementById('story-title').value,
            description: document.getElementById('story-description').value,
            status: document.getElementById('story-status').value,
            storyPoints: document.getElementById('story-points').value || null,
            priority: document.getElementById('story-priority').value,
            assigned_to: document.getElementById('story-assignee').value || null,
            epic_id: currentEpicId
        };

        console.log('Submitting story:', storyData);

        // Gửi AJAX request đến backend
        fetch("{{ route('user-stories.store') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(storyData)
        })
        .then(response => response.json())
        .then(data => {
            console.log('User Story created successfully:', data);
            closeCreateStoryModal();
            alert('User Story created successfully!');
            window.location.reload();
        })
        .catch(error => {
            console.error('Error creating story:', error);
            alert('Failed to create user story. Please try again.');
        });
    }

    // --- 2C. EPIC EDIT MODE FUNCTIONS ---
    // Toggle sang Edit Mode
    function toggleEditEpicMode(epicId) {
        // Ẩn View Mode, hiện Edit Mode
        document.getElementById('epic-view-' + epicId).classList.add('hidden');
        document.getElementById('epic-edit-' + epicId).classList.remove('hidden');

        // Đổi buttons: Ẩn Edit + Delete, Hiện Cancel + Save
        document.getElementById('epic-btn-edit-' + epicId).classList.add('hidden');
        document.getElementById('epic-btn-delete-' + epicId).classList.add('hidden');
        document.getElementById('epic-btn-cancel-' + epicId).classList.remove('hidden');
        document.getElementById('epic-btn-save-' + epicId).classList.remove('hidden');
    }

    // Hủy Edit Mode, quay về View Mode
    function cancelEditEpic(epicId) {
        // Hiện View Mode, ẩn Edit Mode
        document.getElementById('epic-view-' + epicId).classList.remove('hidden');
        document.getElementById('epic-edit-' + epicId).classList.add('hidden');

        // Đổi buttons: Hiện Edit + Delete, Ẩn Cancel + Save
        document.getElementById('epic-btn-edit-' + epicId).classList.remove('hidden');
        document.getElementById('epic-btn-delete-' + epicId).classList.remove('hidden');
        document.getElementById('epic-btn-cancel-' + epicId).classList.add('hidden');
        document.getElementById('epic-btn-save-' + epicId).classList.add('hidden');
    }

    // Lưu thay đổi Epic
    function saveEditEpic(epicId) {
        // Lấy dữ liệu từ input
        const title = document.getElementById('epic-title-edit-' + epicId).value;
        const description = document.getElementById('epic-desc-edit-' + epicId).value;

        // Validate
        if (!title.trim()) {
            alert('Epic title is required');
            return;
        }

        console.log('Updating epic:', { epicId, title, description });

        // Gửi PATCH request đến server
        fetch('/epics/' + epicId, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                title: title,
                description: description
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Epic updated successfully:', data);
            alert('Epic updated successfully!');
            window.location.reload();
        })
        .catch(error => {
            console.error('Error updating epic:', error);
            alert('Failed to update epic. Please try again.');
        });
    }

    // Xóa Epic
    function deleteEpic(epicId) {
        if (!confirm('Are you sure you want to delete this Epic? This action cannot be undone.')) {
            return;
        }

        console.log('Deleting epic:', epicId);

        fetch('/epics/' + epicId, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('Epic deleted successfully:', data);
            alert('Epic deleted successfully!');
            window.location.reload();
        })
        .catch(error => {
            console.error('Error deleting epic:', error);
            alert('Failed to delete epic. Please try again.');
        });
    }

    // --- 2D. USER STORY EDIT MODE FUNCTIONS ---
    // Toggle sang Edit Mode
    function toggleEditStoryMode(storyId) {
        // Ẩn View Mode, hiện Edit Mode
        document.getElementById('story-view-' + storyId).classList.add('hidden');
        document.getElementById('story-edit-' + storyId).classList.remove('hidden');

        // Đổi buttons: Ẩn Edit + Delete, Hiện Cancel + Save
        document.getElementById('story-btn-edit-' + storyId).classList.add('hidden');
        document.getElementById('story-btn-delete-' + storyId).classList.add('hidden');
        document.getElementById('story-btn-cancel-' + storyId).classList.remove('hidden');
        document.getElementById('story-btn-save-' + storyId).classList.remove('hidden');
    }

    // Hủy Edit Mode, quay về View Mode
    function cancelEditStory(storyId) {
        // Hiện View Mode, ẩn Edit Mode
        document.getElementById('story-view-' + storyId).classList.remove('hidden');
        document.getElementById('story-edit-' + storyId).classList.add('hidden');

        // Đổi buttons: Hiện Edit + Delete, Ẩn Cancel + Save
        document.getElementById('story-btn-edit-' + storyId).classList.remove('hidden');
        document.getElementById('story-btn-delete-' + storyId).classList.remove('hidden');
        document.getElementById('story-btn-cancel-' + storyId).classList.add('hidden');
        document.getElementById('story-btn-save-' + storyId).classList.add('hidden');
    }

    // Lưu thay đổi User Story
    function saveEditStory(storyId) {
        // Lấy dữ liệu từ input
        const storyData = {
            title: document.getElementById('story-title-edit-' + storyId).value,
            description: document.getElementById('story-desc-edit-' + storyId).value,
            status: document.getElementById('story-status-edit-' + storyId).value,
            storyPoints: document.getElementById('story-points-edit-' + storyId).value || null,
            priority: document.getElementById('story-priority-edit-' + storyId).value,
            assigned_to: document.getElementById('story-assignee-edit-' + storyId).value || null
        };

        // Validate
        if (!storyData.title.trim()) {
            alert('User Story title is required');
            return;
        }

        console.log('Updating story:', { storyId, ...storyData });

        // Gửi PATCH request đến server
        fetch('/user-stories/' + storyId, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(storyData)
        })
        .then(response => response.json())
        .then(data => {
            console.log('User Story updated successfully:', data);
            alert('User Story updated successfully!');
            window.location.reload();
        })
        .catch(error => {
            console.error('Error updating story:', error);
            alert('Failed to update user story. Please try again.');
        });
    }

    // Xóa User Story
    function deleteStory(storyId) {
        if (!confirm('Are you sure you want to delete this User Story? This action cannot be undone.')) {
            return;
        }

        console.log('Deleting story:', storyId);

        fetch('/user-stories/' + storyId, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('User Story deleted successfully:', data);
            alert('User Story deleted successfully!');
            window.location.reload();
        })
        .catch(error => {
            console.error('Error deleting story:', error);
            alert('Failed to delete user story. Please try again.');
        });
    }

    // --- 3. EXPAND/COLLAPSE FUNCTIONS ---
    // Toggle hiển thị User Stories trong Epic
    function toggleExpand(epicId) {
        const storiesDiv = document.getElementById('stories-' + epicId);
        const expandBtn = document.getElementById('expand-btn-' + epicId);

        // Toggle class 'hidden' để hiện/ẩn
        storiesDiv.classList.toggle('hidden');

        // Xoay icon mũi tên
        expandBtn.classList.toggle('rotate-90');
    }

    // --- 4. FILTER FUNCTIONS ---
    // Toggle tất cả status checkboxes
    // function toggleStatusAll() {
    //     const statusAll = document.getElementById('status-all');
    //     const isChecked = statusAll.checked;

    //     // Set tất cả checkbox khác theo trạng thái của "All"
    //     document.getElementById('status-todo').checked = isChecked;
    //     document.getElementById('status-inprogress').checked = isChecked;
    //     document.getElementById('status-done').checked = isChecked;
    // }

    // // Toggle từng status checkbox
    // function toggleStatus(statusType) {
    //     // Kiểm tra xem tất cả checkbox có được check không
    //     const allChecked =
    //         document.getElementById('status-todo').checked &&
    //         document.getElementById('status-inprogress').checked &&
    //         document.getElementById('status-done').checked;

    //     // Cập nhật checkbox "All"
    //     document.getElementById('status-all').checked = allChecked;
    // }

    // // --- 5. KHỞI TẠO KHI TRANG LOAD ---
    // document.addEventListener('DOMContentLoaded', function() {
    //     console.log('Page loaded - JavaScript initialized');

    //     // Load Epic titles vào object
    //     @foreach($getEpics as $epic)
    //         epicTitles[{{ $epic->id }}] = "{{ $epic->title }}";
    //     @endforeach

    //     // Set mặc định: tất cả status được check
    //     document.getElementById('status-all').checked = true;
    //     document.getElementById('status-todo').checked = true;
    //     document.getElementById('status-inprogress').checked = true;
    //     document.getElementById('status-done').checked = true;

    //     // Đảm bảo tất cả modals đều đóng khi load trang
    //     closeAllPanels();
    //     closeCreateModal();
    // });
</script>
@endpush

@endsection

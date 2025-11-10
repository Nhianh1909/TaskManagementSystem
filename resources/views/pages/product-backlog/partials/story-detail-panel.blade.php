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
                    {{--
                    // =================================================================================
                    //******************************************************************************** *
                    //*
                    //*                       EDIT USER STORIES FORM
                    //*
                    //******************************************************************************** *
                    //==================================================================================
                    --}}
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
                    {{--
                    // =================================================================================
                    //******************************************************************************** *
                    //*
                    //*                       EDIT AND DELETE BUTTONS
                    //*
                    //******************************************************************************** *
                    //==================================================================================
                    --}}
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

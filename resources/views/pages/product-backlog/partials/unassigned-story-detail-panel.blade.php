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

            {{-- COMMENTS SECTION (Added for unassigned story) --}}
            <div class="mt-8 border-t pt-6">
                <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    Discussion
                </h4>

                {{-- Comments List --}}
                <div id="comments-list-{{ $task->id }}" class="space-y-4 mb-4 max-h-64 overflow-y-auto pr-1">
                    <div class="text-center text-gray-500 text-sm py-4">Loading comments...</div>
                </div>

                {{-- Add Comment Form --}}
                <div class="border-t pt-4">
                    <form id="add-comment-form-{{ $task->id }}" onsubmit="addComment(event, {{ $task->id }})">
                        <textarea id="comment-input-{{ $task->id }}"
                                  placeholder="Add a comment..."
                                  rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 resize-none"
                                  required></textarea>
                        <div class="flex justify-end gap-2 mt-2">
                            <button type="submit"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm">
                                Post Comment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </aside>
    </div>

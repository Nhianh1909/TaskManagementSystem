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

<div id="edit-future-sprint-modal" class="hidden fixed inset-0 z-50" style="pointer-events: auto;">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-40" onclick="closeEditFutureSprintModal()"></div>

    <!-- Modal Content -->
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md transform transition-all">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b">
                <h3 class="text-xl font-semibold text-gray-800">Edit Future Sprint</h3>
                <button type="button"
                        onclick="closeEditFutureSprintModal()"
                        class="text-gray-400 hover:text-gray-600 text-2xl leading-none">
                    &times;
                </button>
            </div>

            <!-- Form -->
            <form id="edit-future-sprint-form" class="p-6 space-y-4">
                <input type="hidden" id="edit-sprint-id" name="sprint_id">

                <!-- Sprint Name -->
                <div>
                    <label for="edit-sprint-name" class="block text-sm font-medium text-gray-700 mb-1">
                        Sprint Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="edit-sprint-name"
                           name="name"
                           required
                           placeholder="e.g., Sprint 5"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Sprint Goal (Optional) -->
                <div>
                    <label for="edit-sprint-goal" class="block text-sm font-medium text-gray-700 mb-1">
                        Sprint Goal <span class="text-gray-400">(Optional)</span>
                    </label>
                    <textarea id="edit-sprint-goal"
                              name="goal"
                              rows="3"
                              placeholder="Describe the sprint goal..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>

                <!-- Start Date (Optional) -->
                <div>
                    <label for="edit-sprint-start-date" class="block text-sm font-medium text-gray-700 mb-1">
                        Start Date <span class="text-gray-400">(Optional)</span>
                    </label>
                    <input type="date"
                           id="edit-sprint-start-date"
                           name="start_date"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- End Date (Optional) -->
                <div>
                    <label for="edit-sprint-end-date" class="block text-sm font-medium text-gray-700 mb-1">
                        End Date <span class="text-gray-400">(Optional)</span>
                    </label>
                    <input type="date"
                           id="edit-sprint-end-date"
                           name="end_date"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Buttons -->
                <div class="flex items-center justify-end space-x-3 pt-4">
                    <button type="button"
                            onclick="closeEditFutureSprintModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Update Sprint
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

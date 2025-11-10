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

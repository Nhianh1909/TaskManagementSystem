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
                {{--
                // =================================================================================
                //******************************************************************************** *
                //*
                //*                       CRUD PANEL EPIC
                //*
                //******************************************************************************** *
                //==================================================================================
                --}}
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

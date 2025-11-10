
@extends('layouts.app')

@section('content')
<style>
    /* Hiệu ứng highlight cho story vừa được reorder */
    .story-reordered {
        background-color: #dbeafe !important; /* blue-100 */
        border-left: 4px solid #3b82f6 !important; /* blue-500 */
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3) !important;
    }
    
    /* Fade out animation */
    .story-item {
        transition: background-color 0.5s ease, border 0.5s ease, box-shadow 0.5s ease;
    }
</style>

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


        {{--
        // =================================================================================
        //******************************************************************************** *
        //*
        //*                       FILTER SIDEBAR
        //*
        //******************************************************************************** *
        //==================================================================================
        --}}
        <div class="grid grid-cols-12 gap-6">
            <!-- Sidebar -->
            @include('pages.product-backlog.partials.filters')
            <!-- MAIN CONTENT -->
            <main class="col-span-6">
                <div class="space-y-4">
                    <!-- FUTURE SPRINTS SECTION -->
                    <section>
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm text-gray-500">Future Sprints</h3>
                            <button onclick="openCreateFutureSprintModal()"
                                    class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-500">
                                + Future Sprint
                            </button>
                        </div>

                        <div class="space-y-4">
                            {{-- Loop qua từng Future Sprint --}}
                            @foreach($futureSprints as $sprint)
                                <div class="bg-purple-50 rounded-lg shadow hover:shadow-md">
                                    <div class="p-4">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-3">
                                                    {{-- Button expand/collapse --}}
                                                    <button type="button"
                                                            class="flex items-center justify-center w-8 h-8 rounded hover:bg-purple-200 expand-btn"
                                                            onclick="toggleFutureSprint({{ $sprint->id }})"
                                                            id="expand-btn-sprint-{{ $sprint->id }}"
                                                            aria-label="Toggle expand">
                                                        <svg class="w-4 h-4 text-purple-700 transform transition-transform"
                                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                        </svg>
                                                    </button>
                                                    {{-- Sprint Title --}}
                                                    <h4 class="text-lg font-semibold text-gray-800 truncate">
                                                        {{ $sprint->name }}
                                                    </h4>
                                                </div>
                                                <p class="text-sm text-gray-600 mt-2">Goal: {{ $sprint->goal ?? 'No goal set' }}</p>
                                            </div>

                                            {{-- Stats --}}
                                            <div class="ml-4 text-right">
                                                <div class="text-sm text-gray-500">{{ $sprint->tasks->count() }} stories</div>
                                                <div class="text-sm text-gray-700 font-medium">{{ $sprint->tasks->sum('storyPoints') }} pts</div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- User Stories trong Sprint (hidden by default) --}}
                                    <div id="sprint-stories-{{ $sprint->id }}" class="hidden border-t">
                                        <div class="p-3 space-y-2 bg-purple-100 story-drop-zone"
                                            ondrop = "dropStory(event)"
                                            ondragover="allowDropStory(event)"
                                            ondragleave="dragLeaveStory(event)"
                                            data-scope ="sprint"
                                            data-scope-id="{{ $sprint->id }}"
                                        >
                                            @foreach($sprint->tasks as $story)
                                                <div class="flex items-center justify-between px-3 py-2 bg-white rounded shadow-sm story-item"
                                                     draggable="true"
                                                     ondragstart="dragStory(event)"
                                                     data-story-id="{{ $story->id }}"
                                                     data-sprint-id="{{ $sprint->id }}">
                                                    <div class="cursor-pointer flex-1" onclick="openStoryPanel({{ $story->id }})">
                                                        <div class="text-sm text-gray-800">{{ $story->title }}</div>
                                                        <div class="text-xs text-gray-500">{{ $story->description }}</div>
                                                    </div>
                                                    <div class="text-sm text-gray-600">{{ $story->storyPoints ?? 0 }} pts</div>
                                                </div>
                                            @endforeach

                                            @if($sprint->tasks->count() === 0)
                                                <div class="text-sm text-gray-500 text-center py-4">
                                                    No stories in this sprint yet
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            {{-- Thông báo nếu chưa có Future Sprint nào --}}
                            @if($futureSprints->count() === 0)
                                <div class="text-center py-8 text-gray-500">
                                    <p>No future sprints yet. Click "+ Future Sprint" to create one.</p>
                                </div>
                            @endif
                        </div>
                    </section>
                    <section>
                        <h3 class="text-sm text-gray-500 mb-3">Product Backlog</h3>
                        <div class="space-y-4">
                            {{--
                            // =================================================================================
                            //******************************************************************************** *
                            //*
                            //*                       GET EPIC AND SHOW IT
                            //*
                            //******************************************************************************** *
                            //==================================================================================
                            --}}
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


                                    {{--
                                    // =================================================================================
                                    //******************************************************************************** *
                                    //*
                                    //*                 SHOW USER STORIES UNDER EPIC AND UNASSIGNED STORIES
                                    //*
                                    //******************************************************************************** *
                                    //==================================================================================
                                    --}}



                                    <div id="stories-{{ $epic->id }}" class="hidden border-t">
                                        <div class="p-3 space-y-2 bg-gray-50 story-drop-zone"
                                            ondrop ="dropStory(event)"
                                            ondragover="allowDropStory(event)"
                                            ondragleave="dragLeaveStory(event)"
                                            data-scope="epic"
                                            data-scope-id="{{ $epic->id }}"
                                        >
                                            @foreach($epic->userStories as $story)
                                            {{-- CLICK VÀO SẼ MỞ PANEL CỦA USERSTORIES --}}
                                                <div class="flex items-center justify-between px-3 py-2 bg-white rounded shadow-sm story-item"
                                                    draggable = "true"
                                                    ondragstart = "dragStory(event)"
                                                    data-story-id="{{ $story->id }}"
                                                    data-epic-id="{{ $epic->id }}"
                                                    data-sprint-id="{{ $story->sprint_id }}"
                                                >
                                                    <div class="cursor-pointer flex-1" onclick="openStoryPanel({{ $story->id }})">
                                                        <div class="text-sm text-gray-800">{{ $story->title }}</div>
                                                        <div class="text-xs text-gray-500">Decription: {{ $story->description }}</div>
                                                    </div>
                                                     <div class="flex items-center gap-2">
                                                        <div class="text-sm text-gray-600">{{ $story->storyPoints ?? 0 }} pts</div>

                                                        {{-- Nút + mở form gán sprint --}}
                                                        <button type="button"
                                                            class="px-2 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-500"
                                                            onclick="event.stopPropagation(); toggleAssignSprintForm({{ $story->id }})">+ Sprint</button>
                                                    </div>
                                                </div>
                                                {{-- Form mini (ẩn mặc định) --}}
                                                <div id="assign-sprint-form-{{ $story->id }}" class="hidden mt-2 p-2 bg-blue-50 border border-blue-200 rounded">
                                                    <div class="flex items-center gap-2">
                                                        <select id="assign-sprint-select-{{ $story->id }}" class="border rounded px-2 py-1 text-sm">
                                                        <option value="">-- Select Future Sprint --</option>
                                                        @foreach($futureSprints as $fs)
                                                            <option value="{{ $fs->id }}">{{ $fs->name }}</option>
                                                        @endforeach
                                                        </select>
                                                        <button type="button"
                                                                onclick="event.stopPropagation(); assignStoryToFutureSprint({{ $story->id }})">Assign</button>
                                                        <button type="button"
                                                                onclick="event.stopPropagation(); toggleAssignSprintForm({{ $story->id }})">Cancel</button>
                                                    </div>
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

    {{-- // =================================================================================
    //******************************************************************************** *
    //*
    //*                       CREATE EPIC MODAL
    //*
    //******************************************************************************** *
    //================================================================================== --}}
    {{-- MODALS (kept at root for overlay) --}}
    @include('pages.product-backlog.partials.create-epic-modal')
    @include('pages.product-backlog.partials.create-future-sprint-modal')



    {{-- // =================================================================================
    //******************************************************************************** *
    //*
    //*                       DETAIL PANEL FOR EPIC
    //*
    //******************************************************************************** *
    //================================================================================== --}}
    <!-- Detail Panel cho từng Epic  -->
    @foreach($getEpics as $epic)
        @include('pages.product-backlog.partials.epic-detail-panel', ['epic' => $epic])
    @endforeach

    <!--
    // =================================================================================
    //******************************************************************************** *
    //*
    //*                       DETAIL PANEL USERSTORIES
    //*
    //******************************************************************************** *
    //==================================================================================
    -->
    @foreach($getEpics as $epic)
        @foreach($epic->userStories as $story)
            @include('pages.product-backlog.partials.story-detail-panel', ['story' => $story, 'epic' => $epic])
        @endforeach
    @endforeach

    <!--
    // =================================================================================
    //******************************************************************************** *
    //*
    //*                       DETAIL PANEL UNASSIGNED USERSTORIES
    //*
    //******************************************************************************** *
    //==================================================================================
    -->
    @foreach($tasksWithoutEpic as $task)
        @include('pages.product-backlog.partials.unassigned-story-detail-panel', ['task' => $task])
    @endforeach

    <!--
    // =================================================================================
    //******************************************************************************** *
    //*
    //*                       CREATE USER STORY MODAL
    //*
    //******************************************************************************** *
    //==================================================================================
    -->
    @include('pages.product-backlog.partials.create-story-modal')

</div>

@push('scripts')
@include('pages.product-backlog.partials.scripts')
@endpush

@endsection

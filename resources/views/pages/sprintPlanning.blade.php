
@extends('layouts.app')

@section('content')
<div id="sprint" class="page">
    <div class="max-w-4xl mx-auto px-4 py-8">

        {{-- TRƯỜNG HỢP 1: ĐÃ CÓ SPRINT ĐANG CHẠY --}}
        @if($activeSprint)
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Sprint in Progress</h1>
            <p class="text-gray-600 mb-8">Một sprint đang được thực hiện. Bạn không thể tạo sprint mới cho đến khi sprint này kết thúc hoặc bị hủy.</p>

            <div class="bg-white rounded-2xl shadow-lg p-8">
                <div class="space-y-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">{{ $activeSprint->name }}</h3>
                        <p class="text-sm text-gray-500">{{ $activeSprint->goal }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4 border-t pt-4">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Start Date</p>
                            <p class="font-semibold">{{ \Carbon\Carbon::parse($activeSprint->start_date)->format('d M, Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600">End Date</p>
                            <p class="font-semibold">{{ \Carbon\Carbon::parse($activeSprint->end_date)->format('d M, Y') }}</p>
                        </div>
                    </div>
                </div>
                <div class="mt-8 border-t pt-6 flex justify-end">
                    <form action="{{ route('sprint.cancel') }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn hủy Sprint này không? Các task chưa hoàn thành sẽ được trả về Backlog.')">
                        @csrf
                        <button type="submit" class="bg-red-500 text-white px-6 py-3 rounded-lg font-semibold hover:bg-red-600 transition-colors">
                            <i class="fas fa-times-circle mr-2"></i>Cancel Current Sprint
                        </button>
                    </form>
                </div>
            </div>

        {{-- TRƯỜNG HỢP 2: CHƯA CÓ SPRINT NÀO -> LIỆT KÊ FUTURE SPRINTS ĐỂ CHỌN BẮT ĐẦU --}}
        @else
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Sprint Planning</h1>
            <div class="bg-white rounded-2xl shadow-lg p-6">
                @if($futureSprints->isEmpty())
                    <div class="text-center py-12 text-gray-500">
                        <p>No Future Sprints yet. Please create one in Product Backlog and assign stories.</p>
                    </div>
                @else
                    <div class="space-y-6">
                        @foreach($futureSprints as $fs)
                            <div class="border rounded-xl p-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1">
                                        <h3 class="text-lg font-semibold text-gray-800">{{ $fs->name }}</h3>
                                        <p class="text-sm text-gray-500">{{ $fs->goal ?? 'No goal set' }}</p>
                                        <div class="mt-3 text-sm text-gray-600 flex items-center gap-6">
                                            <span>{{ $fs->tasks->count() }} stories</span>
                                            <span>{{ $fs->tasks->sum('storyPoints') }} pts</span>
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <form action="{{ route('future-sprints.activate', $fs->id) }}" method="POST" onsubmit="return confirm('Bắt đầu sprint này? Các sprint khác sẽ được tắt.');">
                                            @csrf
                                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Start this Sprint</button>
                                        </form>
                                    </div>
                                </div>
                                @if($fs->tasks->count() > 0)
                                <div class="mt-4 bg-gray-50 rounded-lg p-3 max-h-56 overflow-y-auto">
                                    <ul class="space-y-2">
                                        @foreach($fs->tasks as $t)
                                            <li class="flex items-center justify-between text-sm">
                                                <div class="truncate pr-2">
                                                    <span class="font-medium text-gray-800">{{ $t->title }}</span>
                                                    <span class="text-gray-500">— {{ $t->description }}</span>
                                                </div>
                                                <span class="text-xs text-gray-600 whitespace-nowrap">SP: {{ $t->storyPoints ?? 0 }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                @else
                                    <div class="mt-3 text-sm text-gray-500">No stories assigned yet.</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>


@endsection

@push('scripts')
@endpush

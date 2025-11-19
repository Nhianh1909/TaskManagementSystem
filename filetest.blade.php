
{{--
    ============================================
    TRANG RETROSPECTIVE MEETING (BUỔI HỌP HỒI CỐ)
    ============================================

    Mục đích:
    - Cho phép team nhìn lại sprint vừa qua
    - Thu thập feedback từ thành viên về 3 khía cạnh:
      1. Went Well (Điều tốt) 👍
      2. To Improve (Cần cải thiện) ⚙️
      3. Action Items (Hành động cụ thể) 🚀

    Tính năng:
    - Mỗi thành viên có thể thêm/sửa/xóa item
    - Vote (đánh giá) cho các item
    - Scrum Master có thể chuyển Action Items vào Product Backlog
    - Kết thúc buổi họp và lưu kết quả

    Công nghệ sử dụng:
    - Laravel Blade
    - TailwindCSS (responsive, 3 cột)
    - Alpine.js (modal, interactions)
--}}

@extends('layouts.app')

@section('content')
{{-- Container chính sử dụng Alpine.js để quản lý state --}}
<div class="min-h-screen bg-gray-50 p-6" x-data="retrospectiveMeeting()">
    <div class="max-w-7xl mx-auto">
        {{-- ===== HEADER ===== --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Retrospectives</h1>
            <p class="text-gray-600">{{ $team->name ?? 'My Test Project' }} / Software project</p>
        </div>

        {{-- ===== BỐ CỤC 3 CỘT (RESPONSIVE) =====
             - Mobile: stack 3 hàng (grid-cols-1)
             - Desktop: 3 cột ngang (md:grid-cols-3)
        --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            {{-- ===== CỘT 1: WENT WELL (ĐIỀU TỐT) 👍 ===== --}}
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                {{-- Header màu xanh lá --}}
                <div class="bg-green-500 text-white p-4 text-center">
                    <h2 class="text-xl font-semibold flex items-center justify-center gap-2">
                        <span>Went Well</span>
                        <span class="text-2xl">👍</span>
                    </h2>
                    {{-- Nút thêm item mới với icon --}}
                    <button @click="showAddForm('liked')" class="mt-3 bg-white text-green-600 px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-green-50 hover:shadow-md transition-all duration-200 flex items-center gap-2 mx-auto">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Add Item</span>
                    </button>
                </div>

                {{-- Danh sách các item (card) --}}
                <div class="p-4 space-y-3 min-h-[400px]">
                    @foreach($likedItems as $item)
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 hover:shadow-md transition-shadow">
                        <p class="text-gray-800 text-sm mb-3">{{ $item['content'] }}</p>
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>Creator: {{ $item['creator'] }}</span>
                            <div class="flex items-center gap-3">
                                {{-- Số votes --}}
                                <span class="flex items-center gap-1">
                                    <span>{{ $item['votes'] }}</span>
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                    </svg>
                                </span>
                                {{-- Nút Edit --}}
                                <button @click="editItem({{ $item['id'] }}, 'liked', '{{ addslashes($item['content']) }}')" class="text-blue-500 hover:text-blue-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                {{-- Nút Delete --}}
                                <button @click="deleteItem({{ $item['id'] }}, 'liked')" class="text-red-500 hover:text-red-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- ===== CỘT 2: TO IMPROVE (CẦN CẢI THIỆN) ⚙️ ===== --}}
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                {{-- Header màu cam --}}
                <div class="bg-orange-500 text-white p-4 text-center">
                    <h2 class="text-xl font-semibold flex items-center justify-center gap-2">
                        <span>To Improve</span>
                        <span class="text-2xl">⚙️</span>
                    </h2>
                    {{-- Nút thêm item mới với icon --}}
                    <button @click="showAddForm('improve')" class="mt-3 bg-white text-orange-600 px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-orange-50 hover:shadow-md transition-all duration-200 flex items-center gap-2 mx-auto">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Add Item</span>
                    </button>
                </div>

                {{-- Danh sách các item (card) --}}
                <div class="p-4 space-y-3 min-h-[400px]">
                    @foreach($toImproveItems as $item)
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 hover:shadow-md transition-shadow">
                        <p class="text-gray-800 text-sm mb-3">{{ $item['content'] }}</p>
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>Creator: {{ $item['creator'] }}</span>
                            <div class="flex items-center gap-3">
                                {{-- Số votes --}}
                                <span class="flex items-center gap-1">
                                    <span>{{ $item['votes'] }}</span>
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                    </svg>
                                </span>
                                {{-- Nút Edit --}}
                                <button @click="editItem({{ $item['id'] }}, 'improve', '{{ addslashes($item['content']) }}')" class="text-blue-500 hover:text-blue-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                {{-- Nút Delete --}}
                                <button @click="deleteItem({{ $item['id'] }}, 'improve')" class="text-red-500 hover:text-red-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- ===== CỘT 3: ACTION ITEMS (HÀNH ĐỘNG CẦN LÀM) 🚀 ===== --}}
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                {{-- Header màu xanh dương --}}
                <div class="bg-blue-500 text-white p-4 text-center">
                    <h2 class="text-xl font-semibold flex items-center justify-center gap-2">
                        <span>Action Items</span>
                        <span class="text-2xl">🚀</span>
                    </h2>
                    {{-- Nút thêm item mới với icon --}}
                    <button @click="showAddForm('action')" class="mt-3 bg-white text-blue-600 px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-blue-50 hover:shadow-md transition-all duration-200 flex items-center gap-2 mx-auto">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Add Item</span>
                    </button>
                </div>

                {{-- Danh sách các item (card) --}}
                <div class="p-4 space-y-3 min-h-[400px]">
                    @foreach($actionItems as $item)
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 hover:shadow-md transition-shadow">
                        <p class="text-gray-800 text-sm mb-3">{{ $item['content'] }}</p>
                        <div class="flex items-center justify-between text-xs text-gray-500 mb-3">
                            <span>{{ $item['creator'] }}</span>
                            <div class="flex items-center gap-3">
                                {{-- Số votes --}}
                                <span class="flex items-center gap-1">
                                    <span>{{ $item['votes'] }}</span>
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                    </svg>
                                </span>
                                {{-- Nút Edit --}}
                                <button @click="editItem({{ $item['id'] }}, 'action', '{{ addslashes($item['content']) }}')" class="text-blue-500 hover:text-blue-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                {{-- Nút Delete --}}
                                <button @click="deleteItem({{ $item['id'] }}, 'action')" class="text-red-500 hover:text-red-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        {{-- Nút thêm vào Product Backlog (chỉ Scrum Master mới thấy) --}}
                        @if($userRoleInTeam === 'scrum_master')
                        <button @click="addToBacklog({{ $item['id'] }})" class="w-full bg-blue-500 text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-600 transition-colors">
                            Add to Backlog
                        </button>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ===== NÚT KẾT THÚC CUỘC HỌP ===== --}}
        {{-- Chỉ Scrum Master mới có quyền kết thúc cuộc họp --}}
        <div class="text-center">
            <form action="{{ route('retrospective.end') }}" method="POST" onsubmit="return confirm('Are you sure you want to end this retrospective meeting?')">
                @csrf
                <button type="submit" class="bg-red-500 text-white px-8 py-3 rounded-md text-lg font-semibold hover:bg-red-600 transition-colors shadow-lg">
                    End Meeting
                </button>
            </form>
        </div>
    </div>

    {{-- ===== MODAL THÊM/CHỈNH SỬA ITEM ===== --}}
    {{-- Modal hiển thị khi người dùng nhấn Add Item hoặc Edit --}}
    {{-- x-cloak ẩn modal trước khi Alpine.js khởi tạo --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" style="display: none;">
        {{-- Overlay nền đen mờ, click để đóng modal --}}
        <div class="fixed inset-0 bg-black bg-opacity-40" @click="closeModal()"></div>

        {{-- Nội dung modal với hiệu ứng chuyển động --}}
        <div x-show="showModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-90"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-90"
             class="bg-white rounded-lg shadow-xl z-50 w-full max-w-md mx-4 p-6"
             @click.stop>

            {{-- Tiêu đề modal (động) --}}
            <h3 class="text-xl font-semibold text-gray-800 mb-4" x-text="modalTitle"></h3>

            {{-- Form nhập liệu --}}
            <form @submit.prevent="submitForm()">
                <textarea
                    x-model="formData.content"
                    rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Enter your feedback or action item..."
                    required
                ></textarea>

                {{-- Nút Submit và Cancel --}}
                <div class="flex gap-3 mt-4">
                    <button type="submit" class="flex-1 bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition-colors">
                        <span x-text="formData.id ? 'Update' : 'Add'"></span>
                    </button>
                    <button type="button" @click="closeModal()" class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    /**
     * ===== ALPINE.JS COMPONENT: RETROSPECTIVE MEETING =====
     * Quản lý trạng thái và logic cho trang Retrospective Meeting
     *
     * Chức năng chính:
     * - Quản lý modal thêm/chỉnh sửa item
     * - Submit form thêm/cập nhật item (TODO: tích hợp AJAX)
     * - Xóa item với xác nhận
     * - Thêm Action Item vào Product Backlog (chỉ Scrum Master)
     */
    function retrospectiveMeeting() {
        return {
            // ===== STATE =====
            showModal: false,          // Trạng thái hiển thị modal
            modalTitle: '',            // Tiêu đề động của modal
            formData: {                // Dữ liệu form
                id: null,              // null = thêm mới, có giá trị = chỉnh sửa
                content: '',           // Nội dung item
                type: ''               // Loại: 'liked', 'improve', 'action'
            },

            /**
             * ===== KHỞI TẠO =====
             * Chạy khi component được mount
             */
            init() {
                // Đảm bảo modal ẩn khi trang load
                this.showModal = false;
            },

            /**
             * ===== HIỂN THỊ FORM THÊM MỚI =====
             * @param {string} type - Loại item ('liked', 'improve', 'action')
             */
            showAddForm(type) {
                this.modalTitle = 'Add New Item';
                this.formData = { id: null, content: '', type: type };
                this.showModal = true;
            },

            /**
             * ===== HIỂN THỊ FORM CHỈNH SỬA =====
             * @param {number} id - ID của item
             * @param {string} type - Loại item
             * @param {string} content - Nội dung hiện tại
             */
            editItem(id, type, content) {
                this.modalTitle = 'Edit Item';
                this.formData = { id: id, content: content, type: type };
                this.showModal = true;
            },

            /**
             * ===== ĐÓNG MODAL =====
             * Reset form về trạng thái ban đầu
             */
            closeModal() {
                this.showModal = false;
                this.formData = { id: null, content: '', type: '' };
            },

            /**
             * ===== SUBMIT FORM (THÊM/CẬP NHẬT) =====
             * TODO: Thay thế bằng AJAX call đến route('retrospective.items.store') hoặc route('retrospective.items.update')
             * TODO: Cập nhật danh sách item động thay vì reload trang
             */
            async submitForm() {
                // TODO: Replace with actual AJAX call
                console.log('Submitting:', this.formData);
                alert(this.formData.id ? 'Item updated!' : 'Item added!');
                this.closeModal();
                // Reload page or update data dynamically
                // location.reload();
            },

            /**
             * ===== XÓA ITEM =====
             * @param {number} id - ID của item cần xóa
             * @param {string} type - Loại item
             * TODO: Thay thế bằng AJAX call đến route('retrospective.items.delete')
             */
            async deleteItem(id, type) {
                if (!confirm('Are you sure you want to delete this item?')) return;

                // TODO: Replace with actual AJAX call
                console.log('Deleting item:', id, type);
                alert('Item deleted!');
                // location.reload();
            },

            /**
             * ===== THÊM VÀO PRODUCT BACKLOG =====
             * @param {number} id - ID của Action Item
             * Chỉ Scrum Master mới có quyền thực hiện
             * TODO: Thay thế bằng AJAX call đến route('retrospective.addToBacklog')
             */
            async addToBacklog(id) {
                if (!confirm('Add this action item to the Product Backlog?')) return;

                // TODO: Replace with actual AJAX call
                console.log('Adding to backlog:', id);
                alert('Action item added to Product Backlog!');
                // location.reload();
            }
        }
    }
</script>
@endpush

@endsection

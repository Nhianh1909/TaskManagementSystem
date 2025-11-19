{{--
    ============================================
    TRANG RETROSPECTIVE MEETING (BUỔI HỌP HỒI CỐ)
    ============================================
    ... (Các comment giải thích của bạn giữ nguyên) ...
--}}

@extends('layouts.app')

@section('content')
{{-- Code bắt thông báo từ Controller --}}
    @if(session('success'))
        <div class="flash-message bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Thành công!</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="flash-message bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Lỗi!</strong>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif
{{-- Container chính (ĐÃ XÓA x-data="retrospectiveMeeting()") --}}
<div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto">
        {{-- ===== HEADER ===== --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Retrospectives</h1>
            {{-- Giả sử $retro và $retro->team tồn tại --}}
            <p class="text-gray-600">{{ $retro->team->name ?? 'My Test Project' }} / Software project</p>
        </div>

        {{-- ===== DROPDOWN LỌC SPRINT (Giữ nguyên) ===== --}}
        <form action="{{ route('retrospective.index') }}" method="GET" class="mb-4">
            <label for="sprint_id" class="block text-sm font-medium text-gray-700">Select Sprint:</label>
            <select name="sprint_id" id="sprint_id" onchange="this.form.submit()" class="mt-1 block w-full md:w-1/3 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @if($allSprints->isEmpty())
                    <option value="">No completed sprints found</option>
                @endif
                @foreach($allSprints as $sprint)
                    <option value="{{ $sprint->id }}"
                            @if($activeSprint && $activeSprint->id == $sprint->id) selected @endif>
                        {{-- Sửa lỗi $sprint->end_date có thể là string --}}
                        {{ $sprint->name }} (Ended: {{ $sprint->end_date ? \Carbon\Carbon::parse($sprint->end_date)->format('M d') : 'N/A' }})
                    </option>
                @endforeach
            </select>
        </form>

    @if($retro)
        {{-- ===== BỐ CỤC 3 CỘT (Giữ nguyên) ===== --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

            {{-- ===== CỘT 1: WENT WELL (ĐIỀU TỐT) 👍 ===== --}}
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-green-500 text-white p-4 text-center">
                    <h2 class="text-xl font-semibold ...">
                        <span>Went Well</span>
                        <span class="text-2xl">👍</span>
                    </h2>

                    {{-- THAY ĐỔI: Xóa @click, thêm ID và data-type --}}
                    @if(!$retro->is_locked)
                    <form action="{{ route('retrospective.items.store', $retro->id) }}" method="POST" class="mt-3">
                        @csrf
                        {{-- Gửi 'type' một cách bí mật --}}
                        <input type="hidden" name="type" value="good">

                        <textarea name="content" rows="2" class="w-full rounded text-gray-900 p-2" placeholder="Add new item..." required></textarea>
                        <button type="submit" class="mt-2 bg-white text-green-600 px-6 py-2 rounded-lg text-sm font-semibold hover:bg-green-50 ...">
                            Add Item
                        </button>
                    </form>
                    @endif
                </div>

                <div class="p-4 space-y-3 min-h-[400px]">
                    <div id="liked-items-list" class="p-4 space-y-3 min-h-[400px]">
                    {{-- JS sẽ "vẽ" các item vào đây --}}
                </div>
                </div>
            </div>

            {{-- ===== CỘT 2: TO IMPROVE (CẦN CẢI THIỆN) ⚙️ ===== --}}
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-orange-500 text-white p-4 text-center">
                    <h2 class="text-xl font-semibold ...">
                        <span>To Improve</span>
                        <span class="text-2xl">⚙️</span>
                    </h2>

                    {{-- THAY ĐỔI: Xóa @click, thêm ID và data-type --}}
                     {{-- FORM CHO CỘT 2 --}}
                    @if(!$retro->is_locked)
                    <form action="{{ route('retrospective.items.store', $retro->id) }}" method="POST" class="mt-3">
                        @csrf
                        <input type="hidden" name="type" value="bad">
                        <textarea name="content" rows="2" class="w-full rounded text-gray-900 p-2" placeholder="Add new item..." required></textarea>
                        <button type="submit" class="mt-2 bg-white text-orange-600 px-6 py-2 ...">
                            Add Item
                        </button>
                    </form>
                    @endif
                </div>

                <div class="p-4 space-y-3 min-h-[400px]">
                    {{-- XÓA @forelse VÀ THÊM ID --}}
                <div id="improve-items-list" class="p-4 space-y-3 min-h-[400px]">
                    {{-- JS sẽ "vẽ" các item vào đây --}}
                </div>
                </div>
            </div>

            {{-- ===== CỘT 3: ACTION ITEMS (HÀNH ĐỘNG CẦN LÀM) 🚀 ===== --}}
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-blue-500 text-white p-4 text-center">
                    <h2 class="text-xl font-semibold ...">
                        <span>Action Items</span>
                        <span class="text-2xl">🚀</span>
                    </h2>

                    {{-- THAY ĐỔI: Xóa @click, thêm ID và data-type --}}
                    {{-- FORM CHO CỘT 3 --}}
                    @if(!$retro->is_locked)
                    <form action="{{ route('retrospective.items.store', $retro->id) }}" method="POST" class="mt-3">
                        @csrf
                        <input type="hidden" name="type" value="action">
                        <textarea name="content" rows="2" class="w-full rounded text-gray-900 p-2" placeholder="Add new item..." required></textarea>
                        <button type="submit" class="mt-2 bg-white text-blue-600 px-6 py-2 ...">
                            Add Item
                        </button>
                    </form>
                    @endif
                </div>

                <div class="p-4 space-y-3 min-h-[400px]">
                    {{-- XÓA @forelse VÀ THÊM ID --}}
                <div id="action-items-list" class="p-4 space-y-3 min-h-[400px]">
                    {{-- JS sẽ "vẽ" các item vào đây --}}
                </div>
                </div>
            </div>
        </div>

        {{-- ===== NÚT KẾT THÚC / MỞ LẠI CUỘC HỌP ===== --}}
        {{-- Chỉ hiển thị cho PO và SM --}}
        @if(in_array($userRoleInTeam, ['product_owner', 'scrum_master']))
        <div class="text-center mt-8 mb-12">

            @if(!$retro->is_locked)
                {{-- TRƯỜNG HỢP 1: CHƯA KHÓA -> HIỆN NÚT "END MEETING" --}}
                <form action="{{ route('retrospective.lock', $retro->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn kết thúc buổi họp này? Mọi người sẽ không thể thêm/sửa item nữa.')">
                    @csrf
                    <button type="submit" class="bg-red-500 text-white px-8 py-3 rounded-md text-lg font-semibold hover:bg-red-600 transition-colors shadow-lg flex items-center gap-2 mx-auto">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        End Meeting
                    </button>
                </form>
            @else
                {{-- TRƯỜNG HỢP 2: ĐÃ KHÓA -> HIỆN NÚT "RE-OPEN MEETING" --}}
                <div class="space-y-3">
                    <div class="inline-block bg-gray-100 text-gray-600 px-4 py-2 rounded-full text-sm font-medium">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            Meeting is Locked
                        </span>
                    </div>

                    <form action="{{ route('retrospective.unlock', $retro->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="text-gray-500 hover:text-gray-700 underline text-sm">
                            Re-open Meeting (Unlock)
                        </button>
                    </form>
                </div>
            @endif

        </div>
        @endif

    @else
        {{-- ===== THÔNG BÁO NẾU KHÔNG CÓ SPRINT (Giữ nguyên) ===== --}}
        <div class="text-center p-12 bg-white rounded-lg shadow">
            <h2 class="text-xl font-semibold text-gray-700">No completed sprints found.</h2>
            <p class="text-gray-500 mt-2">The retrospective board will appear here once your first sprint is completed.</p>
        </div>
    @endif

    </div>
    {{-- ===== EDIT MODAL (FORM TRUYỀN THỐNG) ===== --}}
    <div id="edit-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
        {{-- Overlay --}}
        <div class="fixed inset-0 bg-black bg-opacity-40" onclick="closeEditModal()"></div>

        <div class="bg-white rounded-lg shadow-xl z-50 w-full max-w-md mx-4 p-6 relative">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Edit Item</h3>

            {{-- Form này sẽ được JS cập nhật 'action' --}}
            <form id="edit-form" method="POST">
                @csrf
                @method('PATCH') {{-- Giả lập phương thức PATCH --}}

                <textarea
                    id="edit-content"
                    name="content"
                    rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    required
                ></textarea>

                <div class="flex gap-3 mt-4">
                    <button type="submit" class="flex-1 bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition-colors">
                        Update
                    </button>
                    <button type="button" onclick="closeEditModal()" class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

{{-- ===== BẮT ĐẦU SỬA TỪ ĐÂY ===== --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {

        @if($retro)

            // --- 1. BIẾN TOÀN CỤC ---
            const RETRO_ID = {{ $retro->id }};
            const IS_LOCKED = {{ $retro->is_locked ? 'true' : 'false' }};
            const CURRENT_USER_ID = {{ Auth::id() }};
            const USER_ROLE = '{{ $userRoleInTeam }}';
            // Lấy CSRF token (rất quan trọng) từ thẻ meta trong layout
            const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const likedList = document.getElementById('liked-items-list');
            const improveList = document.getElementById('improve-items-list');
            const actionList = document.getElementById('action-items-list');

            let lastItemsKey = null;

            // --- 2. HÀM TÍNH "CHỮ KÝ" (KEY) ---
            function computeItemsKey(items) {
                if (!items || items.length === 0) return 'empty:0:0';

                let totalCount = items.length;
                let newestTs = 0;
                let newestId = 0;
                const toTs = (d) => (d ? new Date(d).getTime() : 0);

                items.forEach(item => {
                    const ts = toTs(item.updated_at);
                    if (ts > newestTs) {
                        newestTs = ts;
                        newestId = item.id;
                    } else if (ts === newestTs && item.id > newestId) {
                        newestId = item.id;
                    }
                });
                return `${totalCount}:${newestTs}:${newestId}`;
            }

            // --- 3. HÀM "VẼ" 1 ITEM HTML (ĐÃ SỬA) ---
            function createItemElement(item) {
                const div = document.createElement('div');
                div.className = 'bg-gray-50 rounded-lg p-4 border border-gray-200 hover:shadow-md transition-shadow';

                const isOwner = (item.user_id === CURRENT_USER_ID);
                const isAdmin = (USER_ROLE === 'product_owner' || USER_ROLE === 'scrum_master');

                // Nút Edit (chỉ chủ sở hữu)
                const editBtn = (isOwner && !IS_LOCKED) ? `
                    <button class="text-blue-500 hover:text-blue-700"
                            onclick="openEditModal(${item.id}, '${item.type}', \`${escapeHTML(item.content)}\`)">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </button>` : '';

                // === SỬA LỖI: THAY THẾ FORM BẰNG NÚT ONCLICK ===
                // Nút Delete (chủ sở hữu HOẶC admin)
                const deleteBtn = (isOwner || isAdmin) && !IS_LOCKED ? `
                    <button class="text-red-500 hover:text-red-700"
                            onclick="deleteItem(${item.id})">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>` : '';

                div.innerHTML = `
                    <p class="text-gray-800 text-sm mb-3">${escapeHTML(item.content)}</p>
                    <div class="flex items-center justify-between text-xs text-gray-500">
                        <span>Creator: ${escapeHTML(item.user.name)}</span>
                        <div class="flex items-center gap-3">
                            ${editBtn}
                            ${deleteBtn}
                        </div>
                    </div>
                `;
                return div;
            }

            // Hàm chống lỗi XSS
            function escapeHTML(str) {
                if (!str) return '';
                // Dùng ` (backtick) để bọc chuỗi có thể chứa dấu nháy đơn
                return str.replace(/\\/g, '\\\\').replace(/`/g, '\\`').replace(/\${/g, '\\${');
            }

            // --- 4. HÀM TẢI DỮ LIỆU CHÍNH (GỌI API) ---
            async function loadAllItems() {
                try {
                    const response = await fetch(`/retrospective/${RETRO_ID}/items`);
                    if (!response.ok) throw new Error('Network error');

                    const items = await response.json();

                    const currentKey = computeItemsKey(items);

                    if (currentKey === lastItemsKey) {
                        return;
                    }

                    lastItemsKey = currentKey;

                    likedList.innerHTML = '';
                    improveList.innerHTML = '';
                    actionList.innerHTML = '';

                    items.forEach(item => {
                        const itemElement = createItemElement(item);
                        // Sửa 'good'/'bad' theo database
                        if (item.type === 'good') {
                            likedList.appendChild(itemElement);
                        } else if (item.type === 'bad') {
                            improveList.appendChild(itemElement);
                        } else if (item.type === 'action') {
                            actionList.appendChild(itemElement);
                        }
                    });

                } catch (error) {
                    console.error('Error polling items:', error);
                }
            }

            // --- 5. HÀM BẮT ĐẦU POLLING ---
            function startPolling() {
                if (!IS_LOCKED) {
                    setInterval(loadAllItems, 3000); // 3 giây
                }
            }

            // === 6. HÀM DELETE ITEM (MỚI) ===
            // (Phải đặt hàm này ở phạm vi 'window' để onclick HTML có thể thấy)
            window.deleteItem = async function(itemId) {
                if (IS_LOCKED) return;
                if (!confirm('Bạn có chắc muốn xóa item này?')) return;

                try {
                    // Dùng fetch với method DELETE
                    const response = await fetch(`/retrospective/items/${itemId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': CSRF_TOKEN, // Gửi CSRF token
                            'Accept': 'application/json'
                        }
                    });

                    const result = await response.json();

                    if (!response.ok) {
                        throw new Error(result.message || 'Không thể xóa item.');
                    }
                    alert(result.message || 'Xóa item thành công!');
                    // Xóa thành công, tải lại list ngay lập tức
                    loadAllItems();

                } catch (error) {
                    console.error('Error deleting item:', error);
                    alert('Lỗi: ' + error.message);
                }
            }

            // === 7. HÀM MỞ MODAL (MỚI) ===
            // (Đặt ở 'window' để onclick HTML có thể thấy)
            // (Chúng ta sẽ code hàm openEditModal và submit form sau)
            window.openEditModal = function(id, type, content) {
                if (IS_LOCKED) return;
                console.log('Mở modal để sửa:', id, type, content);
                // 1. Lấy các phần tử Modal
                const modal = document.getElementById('edit-modal');
                const form = document.getElementById('edit-form');
                const textarea = document.getElementById('edit-content');

                if (!modal || !form || !textarea) return console.error('Modal elements not found');

                // 2. Điền dữ liệu cũ
                textarea.value = content;

                // 3. Cập nhật action của form
                // URL update: /retrospective/items/{id}
                form.action = `/retrospective/items/${id}`;

                // 4. Hiển thị modal
                modal.classList.remove('hidden');
                modal.style.display = 'flex';
            }


            // --- 8. CHẠY LẦN ĐẦU TIÊN ---
            loadAllItems();
            startPolling();

        @endif
        // Tìm tất cả các thông báo có class 'flash-message'
        const alerts = document.querySelectorAll('.flash-message');

        if (alerts.length > 0) {
            // Đợi 2 giây (2000ms)
            setTimeout(() => {
                alerts.forEach(alert => {
                    // Tạo hiệu ứng mờ dần (Fade out)
                    alert.style.transition = "opacity 0.5s ease";
                    alert.style.opacity = "0";

                    // Sau khi mờ xong (0.5s) thì xóa hẳn khỏi DOM
                    setTimeout(() => {
                        alert.remove();
                    }, 500);
                });
            }, 2000); // <-- Thời gian chờ trước khi tắt (2000ms = 2s)
        }

    });
</script>
@endpush

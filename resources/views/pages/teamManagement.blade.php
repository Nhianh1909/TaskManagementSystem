@extends('layouts.app')
@section('content')
{{ Auth::user()->role }}

@if($errors->any())
    <div class="bg-red-500 text-white px-4 py-2 rounded mt-2">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif
<div id="team" class="page">
    <div class="max-w-6xl mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Team Management</h1>
            @if(Auth::user()->role === 'admin')
            <button onclick="openTeamModal()" class="gradient-btn text-white px-6 py-3 rounded-lg font-semibold">
                <i class="fas fa-user-plus mr-2"></i>Add Member
            </button>
            @endif
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($teamMembers as $team)
                <div class="card-3d bg-white rounded-2xl shadow-lg p-6 glow-effect">
                    <div class="text-center">
                        {{-- Avatar leader hoặc placeholder --}}
                        <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAiIGhlaWdodD0iODAiIHZpZXdCb3g9IjAgMCA4MCA4MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iNDAiIGN5PSI0MCIgcj0iNDAiIGZpbGw9IiMwMDdCRkYiLz4KPHN2ZyB3aWR0aD0iNTAiIGhlaWdodD0iNTAiIHZpZXdCb3g9IjAgMCA1MCA1MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4PSIxNSIgeT0iMTUiPgo8cGF0aCBkPSJNMjUgNUMzMC41MjI4IDUgMzUgOS40NzcxNSAzNSAxNUMzNSAyMC41MjI4IDMwLjUyMjggMjUgMjUgMjVDMTkuNDc3MiAyNSAxNSAyMC41MjI4IDE1IDE1QzE1IDkuNDc3MTUgMTkuNDc3MiA1IDI1IDVaIiBmaWxsPSJ3aGl0ZSIvPgo8cGF0aCBkPSJNMjUgMzBDMzMuMjg0MyAzMCA0MCAzNi43MTU3IDQwIDQ1QzQwIDUzLjI4NDMgMzMuMjg0MyA2MCAyNSA2MEMxNi43MTU3IDYwIDEwIDUzLjI4NDMgMTAgNDVDMTAgMzYuNzE1NyAxNi43MTU3IDMwIDI1IDMwWiIgZmlsbD0id2hpdGUiLz4KPC9zdmc+Cjwvc3ZnPgo="
                            alt="{{ $team['leader']['name'] ?? 'No leader' }}"
                            class="w-20 h-20 rounded-full mx-auto mb-4">

                        {{-- Tên team --}}
                        @php
                            $displayName = match($team['team_name']) {
                                'product_owner' => 'Product Owner',
                                'scrum_master' => 'Scrum Master',
                                'leadDeveloper' => 'Lead Developer',
                                'developer' => 'Developer',
                                default => ucfirst($team['team_name'])
                            };
                        @endphp

                        <h2 class="text-2xl font-bold text-gray-800 mb-4">{{ $displayName }}</h2>

                        {{-- Leader info --}}
                        @if($team['leader'])
                            <h3 class="text-xl font-semibold text-gray-800">{{ $team['leader']['name'] }}</h3>
                            <p class="font-medium mb-2" style="color: {{ $team['leader']['role'] === 'product_owner' ? '#00f' : ($team['leader']['role'] === 'scrum_master' ? '#0f0' : '#a0f') }}">
                                {{ ucfirst(str_replace('_', ' ', $team['leader']['role'])) }}
                            </p>
                        @else
                            <p class="text-gray-500 italic">No leader assigned</p>
                        @endif

                        {{-- Mô tả team (nếu có) --}}
                        @if(!empty($team['team_description']))
                            <p class="text-sm text-gray-600 mt-4">{{ $team['team_description'] }}</p>
                        @endif
                    </div>
                   @if(Auth::user()->role === 'admin' && !empty($team['leader']['id']))
                    <div class="flex justify-center space-x-2">
                        <button
                            onclick="openEditModal(this)"
                            data-id="{{ $team['team_id'] }}"
                            data-name="{{ $team['leader']['name'] ?? '' }}"
                            data-role="{{ $team['leader']['role'] ?? '' }}"
                            data-email="{{ $team['leader']['email'] ?? '' }}"
                            data-description="{{ $team['team_description'] ?? '' }}"
                            class="text-blue-600 hover:text-blue-800 p-2">
                            <i class="fas fa-edit"></i>
                        </button>

                        <button onclick="openDeleteModal('{{ $team['leader']['id'] }}')"
                            class="text-red-600 hover:text-red-800 p-2">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    @endif

                </div>
            @endforeach
        </div>
    </div>
</div>
<!-- Add Team Member Modal -->
<div id="addTeamModal" class="modal fixed inset-0 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="modal-content bg-white rounded-2xl shadow-2xl w-full max-w-md mx-auto">
            <div class="modal-header text-white p-6 rounded-t-2xl">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-bold">Add Team Member</h2>
                    <button onclick="closeModal('addTeamModal')" class="text-white hover:text-gray-200 text-2xl" aria-label="Close modal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <form id="addTeamForm" action="{{ route('addTeam') }}" method="POST" class="p-6 space-y-6">
                @csrf
                @foreach($teamMembers as $team)
                    <input type="hidden" name="team_id[]" value="{{ $team['team_id'] }}">
                @endforeach

                <!-- Full Name -->
                <div>
                    <label for="addName" class="block text-sm font-semibold text-gray-700 mb-2">Full Name</label>
                    <input type="text" id="addName" name="name" required
                           class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-transparent"
                           placeholder="Enter full name" aria-label="Team member full name">
                </div>

                <!-- Role -->
                <div>
                    <label for="addRole" class="block text-sm font-semibold text-gray-700 mb-2">Role</label>
                    <select id="addRole" name="role" required
                            class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-transparent"
                            aria-label="Select team member role">
                        <option value="">Select a role</option>
                        <option value="product_owner">Product Owner</option>
                        <option value="scrum_master">Scrum Master</option>
                        <option value="leadDeveloper">Lead Developer</option>
                        <option value="developer">Developer</option>
                    </select>
                </div>

                <!-- Email -->
                <div>
                    <label for="addEmail" class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                    <input type="email" id="addEmail" name="email" required
                           class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-transparent"
                           placeholder="Enter email address" aria-label="Team member email">
                </div>

                <!-- Buttons -->
                <div class="flex space-x-4 pt-4">
                    <button type="button" onclick="closeModal('addTeamModal')"
                            class="flex-1 px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-semibold transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 gradient-btn text-white px-6 py-3 rounded-lg font-semibold">
                        Add Member
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


    <!-- Edit Team Member Modal -->
    <div id="editTeamModal" class="fixed inset-0 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="modal-content bg-white rounded-2xl shadow-2xl w-full max-w-md mx-auto">
                <div class="modal-header text-white p-6 rounded-t-2xl">
                    <div class="flex justify-between items-center">
                        <h2 class="text-2xl font-bold">Edit Team Member</h2>
                        <button onclick="closeModal('editTeamModal')" class="text-white hover:text-gray-200 text-2xl" aria-label="Close modal">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <form id="editTeamForm" class="p-6 space-y-6">
                    <input type="hidden" id="editMemberId" name="memberId">

                    <div>
                        <label for="editName" class="block text-sm font-semibold text-gray-700 mb-2">Full Name</label>
                        <input type="text" id="editName" name="name" required
                               class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-transparent"
                               placeholder="Enter full name" aria-label="Team member full name">
                    </div>

                    <div>
                        <label for="editRole" class="block text-sm font-semibold text-gray-700 mb-2">Role</label>
                        <select id="editRole" name="role" required
                                class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-transparent"
                                aria-label="Select team member role">
                            <option value="">Select a role</option>
                            <option value="product_owner">Product Owner</option>
                            <option value="scrum_master">Scrum Master</option>
                            <option value="lead_developer">Lead Developer</option>
                            <option value="developer">Developer</option>
                        </select>
                    </div>

                    <div>
                        <label for="editEmail" class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                        <input type="email" id="editEmail" name="email" required
                               class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-transparent"
                               placeholder="Enter email address" aria-label="Team member email">
                    </div>

                    <div>
                        <label for="editDescription" class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                        <textarea id="editDescription" name="description" rows="3"
                                  class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-transparent resize-none"
                                  placeholder="Brief description of responsibilities" aria-label="Team member description"></textarea>
                    </div>

                    <div class="flex space-x-4 pt-4">
                        <button type="button" onclick="closeModal('editTeamModal')"
                                class="flex-1 px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-semibold transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="flex-1 gradient-btn text-white px-6 py-3 rounded-lg font-semibold">
                            Update Member
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Delete Confirmation Modal -->
    <div id="deleteConfirmModal" class="fixed inset-0 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Xác nhận xóa</h2>
                <p class="text-gray-600 mb-6">Bạn có chắc chắn muốn xóa user này không?</p>
                <div class="flex justify-end space-x-4">
                    <button onclick="closeModal('deleteConfirmModal')"
                            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Hủy
                    </button>
                    <button onclick="confirmDelete()"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Xóa
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection
<script>
    // Hàm đóng tất cả modal trước khi mở modal mới
    function closeAllModals() {
        document.querySelectorAll('.modal').forEach(modal => {
            modal.classList.add('hidden');
        });
        document.body.style.overflow = 'auto';
    }

    // Hàm mở modal chung
    function openModal(modalId) {
        closeAllModals(); // luôn đóng modal khác trước
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    // Hàm mở modal Add Team Member
    function openTeamModal() {
        openModal('addTeamModal');
        document.getElementById('addTeamForm').reset();
        setTimeout(() => {
            document.getElementById('addName').focus();
        }, 100);
    }

    // Hàm mở modal Edit Team Member
    function openEditModal(button) {
        const memberId = button.dataset.id;
        const name = button.dataset.name;
        const role = button.dataset.role;
        const email = button.dataset.email;
        const description = button.dataset.description;

        // Gán vào form
        document.getElementById('editMemberId').value = memberId;
        document.getElementById('editName').value = name;
        document.getElementById('editRole').value = role;
        document.getElementById('editEmail').value = email;
        document.getElementById('editDescription').value = description;

        openModal('editTeamModal');

        setTimeout(() => {
            document.getElementById('editName').focus();
        }, 100);
    }

    // Hàm đóng modal theo id
    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    }

    /// DELETE
    let deleteMemberId = null;

    function openDeleteModal(memberId) {
        deleteMemberId = memberId;
        openModal('deleteConfirmModal');
        // đảm bảo modal delete luôn nổi lên
        const modal = document.getElementById('deleteConfirmModal');
        if (modal) {
            modal.style.zIndex = 9999;
        }
    }

    function confirmDelete() {
        if (!deleteMemberId) return;

        fetch(`/team/${deleteMemberId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(async res => {
            if (res.ok) {
                const data = await res.json();
                console.log(data.message);
                location.reload();
            } else {
                const errorData = await res.json();
                alert(errorData.error || 'Xóa thất bại!');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Có lỗi xảy ra!');
        });
    }
</script>



<script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'96e4917d27cc099b',t:'MTc1NTA0OTg5Ni4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>

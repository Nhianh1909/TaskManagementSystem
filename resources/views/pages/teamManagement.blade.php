@extends('layouts.app')
@section('content')
<div id="team" class="page">
    <div class="max-w-6xl mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Team Management</h1>
            <button onclick="openAddMemberModal()" class="gradient-btn text-white px-6 py-3 rounded-lg font-semibold">
                <i class="fas fa-user-plus mr-2"></i>Add Member
            </button>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <div class="grid md:grid-cols-1 lg:grid-cols-2 gap-8">
            @foreach($teams as $team)
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2">{{ $team->name }}</h2>
                    <p class="text-sm text-gray-500 mb-4">{{ $team->description }}</p>

                    <div class="space-y-3">
                        @forelse($team->users as $member)
                            <div class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50">
                                <div class="flex items-center">
                                    <img src="[https://ui-avatars.com/api/?name=](https://ui-avatars.com/api/?name=){{ urlencode($member->name) }}&background=0D8ABC&color=fff" class="w-10 h-10 rounded-full mr-4">
                                    <div>
                                        <p class="font-semibold">{{ $member->name }}</p>
                                        <p class="text-sm text-gray-600">{{ $member->pivot->roleInTeam }}</p>
                                    </div>
                                </div>
                                <form action="{{ route('team.removeMember', ['team' => $team->id, 'user' => $member->id]) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa thành viên này khỏi team?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        @empty
                            <p class="text-center text-gray-500 py-4">Chưa có thành viên nào trong team này.</p>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Add Team Member Modal -->
<div id="addTeamModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white p-8 rounded-xl shadow-xl max-w-md w-full mx-4">
        <h3 class="text-xl font-bold mb-6">Add Member to Team</h3>
        <form action="{{ route('team.addMember') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="team_id" class="block text-sm font-medium text-gray-700">Team</label>
                <select name="team_id" id="team_id" required class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    @foreach($teams as $team)
                        <option value="{{ $team->id }}">{{ $team->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="user_id" class="block text-sm font-medium text-gray-700">User</label>
                <select name="user_id" id="user_id" required class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    @foreach($allUsers as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="roleInTeam" class="block text-sm font-medium text-gray-700">Role in Team</label>
                <select name="roleInTeam" id="roleInTeam" required class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="product_owner">Product Owner</option>
                    <option value="scrum_master">Scrum Master</option>
                    <option value="leadDeveloper">Lead Developer</option>
                    <option value="developer">Developer</option>
                </select>
            </div>
            <div class="flex justify-end space-x-4 pt-4">
                <button type="button" onclick="closeAddMemberModal()" class="px-4 py-2 bg-gray-200 rounded-lg">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Add Member</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openAddMemberModal() {
        document.getElementById('addTeamModal').classList.remove('hidden');
    }
    function closeAddMemberModal() {
        document.getElementById('addTeamModal').classList.add('hidden');
    }
</script>
@endpush

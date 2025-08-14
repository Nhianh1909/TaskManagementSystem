@extends('layouts.app')
@section('content')
@if(session('success'))
    <div id="toast-success"
         class="fixed top-5 right-5 bg-green-500 text-white px-4 py-2 rounded shadow-lg z-50">
        {{ session('success') }}
    </div>
@endif
<div id="dashboard" class="page">
        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Dashboard</h1>
                <p class="text-gray-600">Welcome back! Here's what's happening with your projects.</p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="card-3d bg-white p-6 rounded-2xl shadow-lg glow-effect">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Active Sprints</p>
                            <p class="text-3xl font-bold text-blue-600">{{ $SprintActive }}</p>
                        </div>
                        <div class="text-3xl text-blue-600">
                            <i class="fas fa-running"></i>
                        </div>
                    </div>
                </div>
                <div class="card-3d bg-white p-6 rounded-2xl shadow-lg glow-effect">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Tasks in Progress</p>
                            <p class="text-3xl font-bold text-yellow-500">{{ $tasksInProgress }}</p>
                        </div>
                        <div class="text-3xl text-yellow-500">
                            <i class="fas fa-tasks"></i>
                        </div>
                    </div>
                </div>
                <div class="card-3d bg-white p-6 rounded-2xl shadow-lg glow-effect">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Completed Today</p>
                        <p class="text-3xl font-bold text-green-500">{{ $tasksCompletedToday ?? 0 }}</p>
                        </div>
                        <div class="text-3xl text-green-500">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="card-3d bg-white p-6 rounded-2xl shadow-lg glow-effect">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Team Members</p>
                            <p class="text-3xl font-bold text-purple-500">{{ $members }}</p>
                        </div>
                        <div class="text-3xl text-purple-500">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="grid lg:grid-cols-2 gap-8">
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h3 class="text-xl font-semibold mb-4 flex items-center">
                        <i class="fas fa-clock text-blue-600 mr-2"></i>Recent Activity
                    </h3>
                    <div class="space-y-4">
                        @foreach($recentActivities as $activity)
                            <div class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="w-2 h-2 rounded-full
                                @if($activity['type'] == 'task') bg-green-500
                                @elseif($activity['type'] == 'sprint') bg-blue-500
                                @else bg-yellow-500
                                @endif">
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium">{{ $activity['description'] }}</p>
                                <p class="text-xs text-gray-500">{{ $activity['time']->diffForHumans() }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h3 class="text-xl font-semibold mb-4 flex items-center">
                        <i class="fas fa-chart-pie text-blue-600 mr-2"></i>Sprint Progress
                    </h3>
                    <canvas id="sprintChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
@endsection

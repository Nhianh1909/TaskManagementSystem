@extends('layouts.app')

@section('content')

<div id="taskboard" class="page">
        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800">Task Board</h1>
                <button onclick="openTaskModal()" class="gradient-btn text-white px-6 py-3 rounded-lg font-semibold">
                    <i class="fas fa-plus mr-2"></i>Add Task
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Backlog Column -->
                <div class="bg-gray-100 rounded-2xl p-4">
                    <h3 class="font-semibold text-gray-700 mb-4 flex items-center">
                        <i class="fas fa-inbox text-gray-500 mr-2"></i>Backlog
                        <span class="ml-auto bg-gray-500 text-white text-xs px-2 py-1 rounded-full">5</span>
                    </h3>
                    <div class="space-y-3" ondrop="drop(event)" ondragover="allowDrop(event)" data-column="backlog">
                        <div class="task-card priority-medium bg-white p-4 rounded-lg shadow-sm cursor-move glow-effect" draggable="true" ondragstart="drag(event)" data-task-id="1">
                            <h4 class="font-medium text-gray-800 mb-2">Design System Setup</h4>
                            <p class="text-sm text-gray-600 mb-3">Create comprehensive design system</p>
                            <div class="flex items-center justify-between">
                                <span class="text-xs bg-orange-100 text-orange-800 px-2 py-1 rounded-full">Medium</span>
                                <div class="flex items-center space-x-1">
                                    <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMTIiIGN5PSIxMiIgcj0iMTIiIGZpbGw9IiNGRjQ3NTciLz4KPHN2ZyB3aWR0aD0iMTYiIGhlaWdodD0iMTYiIHZpZXdCb3g9IjAgMCAxNiAxNiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4PSI0IiB5PSI0Ij4KPHBhdGggZD0iTTggMkM5LjEgMiAxMCAyLjkgMTAgNEMxMCA1LjEgOS4xIDYgOCA2QzYuOSA2IDYgNS4xIDYgNEM2IDIuOSA2LjkgMiA4IDJaIiBmaWxsPSJ3aGl0ZSIvPgo8cGF0aCBkPSJNOCA4QzEwLjIxIDggMTIgOS43OSAxMiAxMkMxMiAxNC4yMSAxMC4yMSAxNiA4IDE2QzUuNzkgMTYgNCAxNC4yMSA0IDEyQzQgOS43OSA1Ljc5IDggOCA4WiIgZmlsbD0id2hpdGUiLz4KPC9zdmc+Cjwvc3ZnPgo=" alt="Assignee" class="w-6 h-6 rounded-full">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- To Do Column -->
                <div class="bg-blue-50 rounded-2xl p-4">
                    <h3 class="font-semibold text-gray-700 mb-4 flex items-center">
                        <i class="fas fa-list text-blue-500 mr-2"></i>To Do
                        <span class="ml-auto bg-blue-500 text-white text-xs px-2 py-1 rounded-full">3</span>
                    </h3>
                    <div class="space-y-3" ondrop="drop(event)" ondragover="allowDrop(event)" data-column="todo">
                        <div class="task-card priority-high bg-white p-4 rounded-lg shadow-sm cursor-move glow-effect" draggable="true" ondragstart="drag(event)" data-task-id="2">
                            <h4 class="font-medium text-gray-800 mb-2">User Authentication</h4>
                            <p class="text-sm text-gray-600 mb-3">Implement login and signup functionality</p>
                            <div class="flex items-center justify-between">
                                <span class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded-full">High</span>
                                <div class="flex items-center space-x-1">
                                    <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMTIiIGN5PSIxMiIgcj0iMTIiIGZpbGw9IiMyRUQ1NzMiLz4KPHN2ZyB3aWR0aD0iMTYiIGhlaWdodD0iMTYiIHZpZXdCb3g9IjAgMCAxNiAxNiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4PSI0IiB5PSI0Ij4KPHBhdGggZD0iTTggMkM5LjEgMiAxMCAyLjkgMTAgNEMxMCA1LjEgOS4xIDYgOCA2QzYuOSA2IDYgNS4xIDYgNEM2IDIuOSA2LjkgMiA4IDJaIiBmaWxsPSJ3aGl0ZSIvPgo8cGF0aCBkPSJNOCA4QzEwLjIxIDggMTIgOS43OSAxMiAxMkMxMiAxNC4yMSAxMC4yMSAxNiA4IDE2QzUuNzkgMTYgNCAxNC4yMSA0IDEyQzQgOS43OSA1Ljc5IDggOCA4WiIgZmlsbD0id2hpdGUiLz4KPC9zdmc+Cjwvc3ZnPgo=" alt="Assignee" class="w-6 h-6 rounded-full">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- In Progress Column -->
                <div class="bg-yellow-50 rounded-2xl p-4">
                    <h3 class="font-semibold text-gray-700 mb-4 flex items-center">
                        <i class="fas fa-spinner text-yellow-500 mr-2"></i>In Progress
                        <span class="ml-auto bg-yellow-500 text-white text-xs px-2 py-1 rounded-full">4</span>
                    </h3>
                    <div class="space-y-3" ondrop="drop(event)" ondragover="allowDrop(event)" data-column="inprogress">
                        <div class="task-card priority-low bg-white p-4 rounded-lg shadow-sm cursor-move glow-effect" draggable="true" ondragstart="drag(event)" data-task-id="3">
                            <h4 class="font-medium text-gray-800 mb-2">Dashboard UI</h4>
                            <p class="text-sm text-gray-600 mb-3">Create responsive dashboard layout</p>
                            <div class="flex items-center justify-between">
                                <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">Low</span>
                                <div class="flex items-center space-x-1">
                                    <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMTIiIGN5PSIxMiIgcj0iMTIiIGZpbGw9IiMwMDdCRkYiLz4KPHN2ZyB3aWR0aD0iMTYiIGhlaWdodD0iMTYiIHZpZXdCb3g9IjAgMCAxNiAxNiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4PSI0IiB5PSI0Ij4KPHBhdGggZD0iTTggMkM5LjEgMiAxMCAyLjkgMTAgNEMxMCA1LjEgOS4xIDYgOCA2QzYuOSA2IDYgNS4xIDYgNEM2IDIuOSA2LjkgMiA4IDJaIiBmaWxsPSJ3aGl0ZSIvPgo8cGF0aCBkPSJNOCA4QzEwLjIxIDggMTIgOS43OSAxMiAxMkMxMiAxNC4yMSAxMC4yMSAxNiA4IDE2QzUuNzkgMTYgNCAxNC4yMSA0IDEyQzQgOS43OSA1Ljc5IDggOCA4WiIgZmlsbD0id2hpdGUiLz4KPC9zdmc+Cjwvc3ZnPgo=" alt="Assignee" class="w-6 h-6 rounded-full">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Done Column -->
                <div class="bg-green-50 rounded-2xl p-4">
                    <h3 class="font-semibold text-gray-700 mb-4 flex items-center">
                        <i class="fas fa-check text-green-500 mr-2"></i>Done
                        <span class="ml-auto bg-green-500 text-white text-xs px-2 py-1 rounded-full">8</span>
                    </h3>
                    <div class="space-y-3" ondrop="drop(event)" ondragover="allowDrop(event)" data-column="done">
                        <div class="task-card priority-high bg-white p-4 rounded-lg shadow-sm cursor-move glow-effect" draggable="true" ondragstart="drag(event)" data-task-id="4">
                            <h4 class="font-medium text-gray-800 mb-2">Project Setup</h4>
                            <p class="text-sm text-gray-600 mb-3">Initialize project structure and dependencies</p>
                            <div class="flex items-center justify-between">
                                <span class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded-full">High</span>
                                <div class="flex items-center space-x-1">
                                    <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMTIiIGN5PSIxMiIgcj0iMTIiIGZpbGw9IiNGRkE1MDIiLz4KPHN2ZyB3aWR0aD0iMTYiIGhlaWdodD0iMTYiIHZpZXdCb3g9IjAgMCAxNiAxNiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4PSI0IiB5PSI0Ij4KPHBhdGggZD0iTTggMkM5LjEgMiAxMCAyLjkgMTAgNEMxMCA1LjEgOS4xIDYgOCA2QzYuOSA2IDYgNS4xIDYgNEM2IDIuOSA2LjkgMiA4IDJaIiBmaWxsPSJ3aGl0ZSIvPgo8cGF0aCBkPSJNOCA4QzEwLjIxIDggMTIgOS43OSAxMiAxMkMxMiAxNC4yMSAxMC4yMSAxNiA4IDE2QzUuNzkgMTYgNCAxNC4yMSA0IDEyQzQgOS43OSA1Ljc5IDggOCA4WiIgZmlsbD0id2hpdGUiLz4KPC9zdmc+Cjwvc3ZnPgo=" alt="Assignee" class="w-6 h-6 rounded-full">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Task Modal -->
    <div id="taskModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white p-6 rounded-xl shadow-xl max-w-md w-full mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Add New Task</h3>
                <button onclick="closeTaskModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Task Title</label>
                    <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Enter task title">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Enter task description"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option>Low</option>
                            <option selected>Medium</option>
                            <option>High</option>
                            <option>Critical</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Story Points</label>
                        <input type="number" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="5">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Assignee</label>
                    <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option>Unassigned</option>
                        <option>Sarah Mitchell</option>
                        <option>Mike Johnson</option>
                        <option>Alex Lee</option>
                        <option>John Doe</option>
                    </select>
                </div>
                <div class="flex space-x-3 pt-4">
                    <button type="submit" class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                        Create Task
                    </button>
                    <button type="button" onclick="closeTaskModal()" class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400 transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
<script>
    function openTaskModal() {
            document.getElementById('taskModal').classList.remove('hidden');
            document.getElementById('taskModal').classList.add('flex');
        }

        function closeTaskModal() {
            document.getElementById('taskModal').classList.add('hidden');
            document.getElementById('taskModal').classList.remove('flex');
        }
</script>

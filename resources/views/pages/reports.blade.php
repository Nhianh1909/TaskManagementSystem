@extends('layouts.app')

@section('content')
<div id="reports" class="page">
        <div class="max-w-7xl mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-8">Reports & Analytics</h1>

            <div class="grid lg:grid-cols-2 gap-8 mb-8">
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h3 class="text-xl font-semibold mb-4 flex items-center">
                        <i class="fas fa-chart-line text-blue-600 mr-2"></i>Burndown Chart
                    </h3>
                    <canvas id="burndownChart" width="400" height="200"></canvas>
                </div>

                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h3 class="text-xl font-semibold mb-4 flex items-center">
                        <i class="fas fa-tachometer-alt text-green-600 mr-2"></i>Velocity Chart
                    </h3>
                    <canvas id="velocityChart" width="400" height="200"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h3 class="text-xl font-semibold mb-4 flex items-center">
                    <i class="fas fa-user-chart text-purple-600 mr-2"></i>Team Performance
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-3 px-4">Team Member</th>
                                <th class="text-left py-3 px-4">Tasks Completed</th>
                                <th class="text-left py-3 px-4">Story Points</th>
                                <th class="text-left py-3 px-4">Efficiency</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-3 px-4">John Doe</td>
                                <td class="py-3 px-4">15</td>
                                <td class="py-3 px-4">42</td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center">
                                        <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                                            <div class="bg-green-500 h-2 rounded-full" style="width: 85%"></div>
                                        </div>
                                        <span class="text-sm text-gray-600">85%</span>
                                    </div>
                                </td>
                            </tr>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-3 px-4">Sarah Smith</td>
                                <td class="py-3 px-4">12</td>
                                <td class="py-3 px-4">38</td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center">
                                        <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                                            <div class="bg-blue-500 h-2 rounded-full" style="width: 78%"></div>
                                        </div>
                                        <span class="text-sm text-gray-600">78%</span>
                                    </div>
                                </td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 px-4">Mike Johnson</td>
                                <td class="py-3 px-4">18</td>
                                <td class="py-3 px-4">51</td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center">
                                        <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                                            <div class="bg-yellow-500 h-2 rounded-full" style="width: 92%"></div>
                                        </div>
                                        <span class="text-sm text-gray-600">92%</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

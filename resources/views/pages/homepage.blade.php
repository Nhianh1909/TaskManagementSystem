@extends('layouts.app')

@section('content')
<div id="homepage" class="page">
        <!-- Hero Section with Particles -->
        <section class="relative min-h-screen flex items-center justify-center overflow-hidden parallax" style="background: linear-gradient(135deg, #007BFF 0%, #00BFFF 100%);">
            <div class="particles" id="particles"></div>
            <div class="relative z-10 text-center text-white px-4">
                <h1 class="text-5xl md:text-7xl font-bold mb-6 animate-pulse">
                    Welcome to <span class="text-yellow-300">ScrumSpark</span>
                </h1>
                <p class="text-xl md:text-2xl mb-8 max-w-3xl mx-auto">
                    Transform your team's productivity with our modern Scrum-based task management system
                </p>
                <div class="space-x-4">
                    <a href="{{ route('login.auth') }}">
                        <button onclick="showPage('login')" class="gradient-btn text-white px-8 py-4 rounded-full text-lg font-semibold">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login
                    </button>
                    </a>

                    <a href="{{ route('signup.auth') }}">
                        <button onclick="showPage('signup')" class="gradient-gold text-white px-8 py-4 rounded-full text-lg font-semibold">
                            <i class="fas fa-user-plus mr-2"></i>Sign Up
                        </button>
                    </a>

                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4">
                <h2 class="text-4xl font-bold text-center mb-16 text-gray-800">Why Choose ScrumSpark?</h2>
                <div class="grid md:grid-cols-3 gap-8">
                    <div class="card-3d bg-white p-8 rounded-2xl shadow-lg glow-effect">
                        <div class="text-4xl text-blue-600 mb-4">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <h3 class="text-xl font-semibold mb-4">Intuitive Task Management</h3>
                        <p class="text-gray-600">Drag-and-drop Kanban boards with real-time collaboration and smart notifications.</p>
                    </div>
                    <div class="card-3d bg-white p-8 rounded-2xl shadow-lg glow-effect">
                        <div class="text-4xl text-yellow-500 mb-4">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3 class="text-xl font-semibold mb-4">Advanced Analytics</h3>
                        <p class="text-gray-600">Burndown charts, velocity tracking, and comprehensive team performance insights.</p>
                    </div>
                    <div class="card-3d bg-white p-8 rounded-2xl shadow-lg glow-effect">
                        <div class="text-4xl text-green-500 mb-4">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="text-xl font-semibold mb-4">Team Collaboration</h3>
                        <p class="text-gray-600">Seamless communication tools with role-based permissions and real-time updates.</p>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

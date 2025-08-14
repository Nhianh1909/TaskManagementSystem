@extends('layouts.app')

@section('content')

<div id="signup" class="page">
        <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-cyan-50 py-12 px-4">
            <div class="max-w-md w-full space-y-8">
                <div class="text-center">
                    <i class="fas fa-rocket text-5xl text-blue-600 mb-4"></i>
                    <h2 class="text-3xl font-bold text-gray-900">Join ScrumSpark</h2>
                    <p class="mt-2 text-gray-600">Create your account and start managing tasks</p>
                </div>
                <form class="mt-8 space-y-6" onsubmit="handleSignup(event)" method="POST" action="{{ route('signup.post') }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                            <input type="text" name='name' required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" placeholder="Enter your full name">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                            <input type="email" name="email" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" placeholder="Enter your email">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                            <input type="password" namme="password" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" placeholder="Create a password">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                            <input type="password" name="password" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" placeholder="Confirm your password">
                        </div>
                    </div>
                    <button type="submit" class="w-full gradient-gold text-white py-3 rounded-lg font-semibold text-lg glow-effect">
                        <i class="fas fa-user-plus mr-2"></i>Create Account
                    </button>
                    <div class="text-center">
                        <span class="text-gray-600">Already have an account? </span>
                        <a href="{{ route('login.auth') }}"><button type="button" onclick="showPage('login')" class="text-blue-600 hover:text-blue-500 font-medium">Sign in</button></a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

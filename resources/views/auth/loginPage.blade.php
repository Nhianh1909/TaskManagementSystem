@extends('layouts.app')

@section('content')

@if($errors->any())
    <div class="bg-red-500 text-white px-4 py-2 rounded mt-2">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

<div id="login" class="page">
        <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-cyan-50 py-12 px-4">
            <div class="max-w-md w-full space-y-8">
                <div class="text-center">
                    <i class="fas fa-rocket text-5xl text-blue-600 mb-4"></i>
                    <h2 class="text-3xl font-bold text-gray-900">Welcome Back</h2>
                    <p class="mt-2 text-gray-600">Sign in to your ScrumSpark account</p>
                </div>
                {{-- handleLogin có thể dư --}}
                <form class="mt-8 space-y-6" onsubmit="handleLogin(event)" method="POST" action="{{ route('login.post') }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                            <input type="email" name="email"  value="{{ old('email') }}" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" placeholder="Enter your email">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                            <input type="password" name="password" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" placeholder="Enter your password">
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input type="checkbox" name='remember' class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-600">Remember me</span>
                        </label>
                        <a href="#" class="text-sm text-blue-600 hover:text-blue-500">Forgot password?</a>
                    </div>
                    <button type="submit" class="w-full gradient-btn text-white py-3 rounded-lg font-semibold text-lg">
                        <i class="fas fa-sign-in-alt mr-2"></i>Sign In
                    </button>
                    <div class="text-center">
                        <span class="text-gray-600">Don't have an account? </span>
                        <a href="{{ route('signup.auth') }}"><button type="button" class="text-blue-600 hover:text-blue-500 font-medium">Sign up</button></a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
<script>

</script>

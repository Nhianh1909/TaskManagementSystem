<nav class="bg-white shadow-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <div class="flex-shrink-0 flex items-center">
                    <i class="fas fa-rocket text-2xl text-blue-600 mr-2"></i>
                    <span class="text-xl font-bold text-gray-800">ScrumSpark</span>
                </div>
                <div class="md:ml-6 md:flex md:space-x-8">
                    <a href="{{ route('dashboard') }}" class="nav-link text-gray-700 hover:text-blue-600 px-3 py-2 text-sm font-medium transition-colors">Dashboard</a>
                    <a href="{{ route('tasksboard') }}" class="nav-link text-gray-700 hover:text-blue-600 px-3 py-2 text-sm font-medium transition-colors">Task Board</a>
                    <a href="{{ route('sprint.create') }}" class="nav-link text-gray-700 hover:text-blue-600 px-3 py-2 text-sm font-medium transition-colors">Sprint Planning</a>
                    <a href="{{ route('team.management') }}" class="nav-link text-gray-700 hover:text-blue-600 px-3 py-2 text-sm font-medium transition-colors">Team</a>
                    <a href="{{ route('reports') }}" class="nav-link text-gray-700 hover:text-blue-600 px-3 py-2 text-sm font-medium transition-colors">Reports</a>
                    <a href="{{ route('product.backlog') }}" class="nav-link text-gray-700 hover:text-blue-600 px-3 py-2 text-sm font-medium transition-colors">Product Backlog</a>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                <button onclick="toggleDarkMode()" class="p-2 rounded-full hover:bg-gray-100 transition-colors">
                    <i class="fas fa-moon text-gray-600"></i>
                </button>

                @auth
                    <div class="relative flex items-center space-x-2">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=007BFF&color=fff"
                             alt="User Avatar"
                             class="w-8 h-8 rounded-full">
                        <span class="text-sm font-medium text-gray-700 mobile-hidden">
                            {{ Auth::user()->name }}
                        </span>

                        {{-- Form logout --}}
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-sm text-red-500 hover:text-red-700 ml-3">
                                Logout
                            </button>
                        </form>
                    </div>
                        @endauth

                        @guest
                            <a href="{{ route('login.auth') }}" class="text-sm text-blue-500 hover:text-blue-700">
                                Login
                            </a>
                        @endguest
                    </div>
            </div>
    </div>
</nav>

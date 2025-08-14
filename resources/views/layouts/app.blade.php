<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ScrumSpark - Modern Task Management System</title>
    <meta name="description" content="Professional Scrum-based task management with modern UI, drag-and-drop boards, and team collaboration tools">

    <!-- Schema Markup for Tasks -->
    @verbatim
    <script type="application/ld+json">
    {
    "@context": "https://schema.org",
    "@type": "SoftwareApplication",
    "name": "ScrumSpark",
    "description": "Modern Scrum task management system",
    "applicationCategory": "ProductivityApplication"
    }
    </script>
    @endverbatim
    {{-- Tải css và link gán css --}}
    @include('particals.scripts')

</head>
<body class="bg-gray-50 transition-all duration-300" id="body">



    <!-- Nav -->
    @include('particals.navbar')


{{-- ----------------------------------- --}}
{{---------------- PAGE -------------------}}
{{-- ----------------------------------- --}}
    @yield('content')
    {{-- @include('pages.settings') --}}
{{-- ----------------------------------- --}}

    <script>
        // Initialize particles
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            if (!particlesContainer) return;

            for (let i = 0; i < 50; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.top = Math.random() * 100 + '%';
                particle.style.width = Math.random() * 4 + 2 + 'px';
                particle.style.height = particle.style.width;
                particle.style.animationDelay = Math.random() * 6 + 's';
                particle.style.animationDuration = (Math.random() * 3 + 3) + 's';
                particlesContainer.appendChild(particle);
            }
        }

        // Page navigation
        // function showPage(pageId) {
        //     // Hide all pages
        //     document.querySelectorAll('.page').forEach(page => {
        //         // page.classList.add('hidden');
        //     });

        //     // Show selected page
        //     document.getElementById(pageId).classList.remove('hidden');

        //     // Update nav links
        //     // document.querySelectorAll('.nav-link').forEach(link => {
        //     //     link.classList.remove('text-blue-600', 'font-semibold');
        //     //     link.classList.add('text-gray-700');
        //     // });

        //     // Initialize charts if reports page
        //     if (pageId === 'reports') {
        //         setTimeout(initializeCharts, 100);
        //     }

        //     // Initialize dashboard chart
        //     if (pageId === 'dashboard') {
        //         setTimeout(initializeDashboardChart, 100);
        //     }
        // }

        // Form handlers
        // function handleLogin(event) {
        //     event.preventDefault();
        //     // Add loading animation
        //     const button = event.target.querySelector('button[type="submit"]');
        //     const originalText = button.innerHTML;
        //     button.innerHTML = '<div class="loading-spinner inline-block mr-2"></div>Signing In...';
        //     button.disabled = true;

        //     setTimeout(() => {
        //         showPage('dashboard');
        //         button.innerHTML = originalText;
        //         button.disabled = false;
        //     }, 2000);
        // }

        // function handleSignup(event) {
        //     event.preventDefault();
        //     const button = event.target.querySelector('button[type="submit"]');
        //     const originalText = button.innerHTML;
        //     button.innerHTML = '<div class="loading-spinner inline-block mr-2"></div>Creating Account...';
        //     button.disabled = true;

        //     setTimeout(() => {
        //         showPage('dashboard');
        //         button.innerHTML = originalText;
        //         button.disabled = false;
        //     }, 2000);
        // }



        function createSprint(event) {
            event.preventDefault();
            const button = event.target.querySelector('button[type="submit"]');
            const originalText = button.innerHTML;
            button.innerHTML = '<div class="loading-spinner inline-block mr-2"></div>Creating Sprint...';
            button.disabled = true;

            setTimeout(() => {
                alert('Sprint created successfully!');
                button.innerHTML = originalText;
                button.disabled = false;
                event.target.reset();
            }, 1500);
        }

        // Drag and drop functionality
        function allowDrop(ev) {
            ev.preventDefault();
            ev.currentTarget.classList.add('drag-over');
        }

        function drag(ev) {
            ev.dataTransfer.setData("text", ev.target.dataset.taskId);
        }

        function drop(ev) {
            ev.preventDefault();
            ev.currentTarget.classList.remove('drag-over');
            const data = ev.dataTransfer.getData("text");
            const draggedElement = document.querySelector(`[data-task-id="${data}"]`);
            const targetColumn = ev.currentTarget.dataset.column;

            if (draggedElement && targetColumn) {
                ev.currentTarget.appendChild(draggedElement);

                // Add particle effect
                createDropEffect(ev.currentTarget);

                // Update column counts
                updateColumnCounts();
            }
        }

        function createDropEffect(element) {
            const effect = document.createElement('div');
            effect.style.position = 'absolute';
            effect.style.width = '20px';
            effect.style.height = '20px';
            effect.style.background = 'radial-gradient(circle, #FFD700, transparent)';
            effect.style.borderRadius = '50%';
            effect.style.pointerEvents = 'none';
            effect.style.animation = 'float 1s ease-out forwards';
            element.appendChild(effect);

            setTimeout(() => effect.remove(), 1000);
        }

        function updateColumnCounts() {
            document.querySelectorAll('[data-column]').forEach(column => {
                const count = column.querySelectorAll('.task-card').length;
                const badge = column.parentElement.querySelector('span');
                if (badge) badge.textContent = count;
            });
        }

        // Dark mode toggle
        function toggleDarkMode() {
            document.getElementById('body').classList.toggle('dark');
            const isDark = document.getElementById('body').classList.contains('dark');
            localStorage.setItem('darkMode', isDark);
        }

        // Settings tabs
        function showSettingsTab(tabName) {
            // Hide all content
            document.querySelectorAll('.settings-content').forEach(content => {
                content.classList.add('hidden');
            });

            // Show selected content
            document.getElementById(tabName + '-tab').classList.remove('hidden');

            // Update tab buttons
            document.querySelectorAll('.settings-tab').forEach(tab => {
                tab.classList.remove('active', 'border-blue-500', 'text-blue-600');
                tab.classList.add('border-transparent', 'text-gray-500');
            });

            event.target.classList.add('active', 'border-blue-500', 'text-blue-600');
            event.target.classList.remove('border-transparent', 'text-gray-500');
        }

        // Chart initialization
        function initializeCharts() {
            // Burndown Chart
            const burndownCtx = document.getElementById('burndownChart');
            if (burndownCtx) {
                new Chart(burndownCtx, {
                    type: 'line',
                    data: {
                        labels: ['Day 1', 'Day 2', 'Day 3', 'Day 4', 'Day 5', 'Day 6', 'Day 7'],
                        datasets: [{
                            label: 'Remaining Work',
                            data: [50, 45, 38, 32, 25, 15, 8],
                            borderColor: '#007BFF',
                            backgroundColor: 'rgba(0, 123, 255, 0.1)',
                            tension: 0.4
                        }, {
                            label: 'Ideal Burndown',
                            data: [50, 43, 36, 29, 22, 15, 8],
                            borderColor: '#FFD700',
                            borderDash: [5, 5],
                            backgroundColor: 'transparent'
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }

            // Velocity Chart
            const velocityCtx = document.getElementById('velocityChart');
            if (velocityCtx) {
                new Chart(velocityCtx, {
                    type: 'bar',
                    data: {
                        labels: ['Sprint 1', 'Sprint 2', 'Sprint 3', 'Sprint 4', 'Sprint 5'],
                        datasets: [{
                            label: 'Story Points Completed',
                            data: [23, 28, 31, 26, 35],
                            backgroundColor: 'linear-gradient(135deg, #007BFF, #00BFFF)',
                            borderColor: '#007BFF',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        }

        function initializeDashboardChart() {
            const sprintCtx = document.getElementById('sprintChart');
            if (sprintCtx) {
                new Chart(sprintCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Completed', 'In Progress', 'To Do'],
                        datasets: [{
                            data: [65, 25, 10],
                            backgroundColor: ['#2ED573', '#FFA502', '#007BFF'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom',
                            }
                        }
                    }
                });
            }
        }

        // Modal functions
        // function openTaskModal() {
        //     alert('Task creation modal would open here. This is a demo interface.');
        // }

        // function openTeamModal() {
        //     alert('Team member addition modal would open here. This is a demo interface.');
        // }

        // Initialize app
        document.addEventListener('DOMContentLoaded', function() {
            createParticles();
            showPage('homepage');

            // Load dark mode preference
            if (localStorage.getItem('darkMode') === 'true') {
                document.getElementById('body').classList.add('dark');
                const toggle = document.getElementById('darkModeToggle');
                if (toggle) toggle.checked = true;
            }
        });

        // Add event listeners for drag and drop
        document.addEventListener('dragend', function(e) {
            document.querySelectorAll('[data-column]').forEach(column => {
                column.classList.remove('drag-over');
            });
        });
    </script>
<script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'96a2af5b32ef0655',t:'MTc1NDM1OTA2MC4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>

{{-- </html>
      <div class="flex items-center mt-2">
                                            <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-medium mr-2">Medium</span>
                                            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 20 20'%3E%3Ccircle cx='10' cy='10' r='10' fill='%2310B981'/%3E%3Ctext x='10' y='14' text-anchor='middle' fill='white' font-family='Arial' font-size='8' font-weight='bold'%3EMJ%3C/text%3E%3C/svg%3E" alt="Assignee" class="w-5 h-5 rounded-full mr-1">
                                            <span class="text-xs text-gray-600">Mike J.</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer" onclick="toggleTaskSelection(this)">
                                    <input type="checkbox" class="mr-3">
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <h4 class="font-medium text-gray-800">API rate limiting</h4>
                                            <span class="text-sm text-gray-600">13 SP</span>
                                        </div>
                                        <p class="text-sm text-gray-600">Implement rate limiting for API endpoints</p>
                                        <div class="flex items-center mt-2">
                                            <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-medium mr-2">Medium</span>
                                            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 20 20'%3E%3Ccircle cx='10' cy='10' r='10' fill='%236366F1'/%3E%3Ctext x='10' y='14' text-anchor='middle' fill='white' font-family='Arial' font-size='8' font-weight='bold'%3EAL%3C/text%3E%3C/svg%3E" alt="Assignee" class="w-5 h-5 rounded-full mr-1">
                                            <span class="text-xs text-gray-600">Alex L.</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer" onclick="toggleTaskSelection(this)">
                                    <input type="checkbox" class="mr-3">
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <h4 class="font-medium text-gray-800">Mobile app testing</h4>
                                            <span class="text-sm text-gray-600">5 SP</span>
                                        </div>
                                        <p class="text-sm text-gray-600">Comprehensive testing on mobile devices</p>
                                        <div class="flex items-center mt-2">
                                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-medium mr-2">Low</span>
                                            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 20 20'%3E%3Ccircle cx='10' cy='10' r='10' fill='%23F59E0B'/%3E%3Ctext x='10' y='14' text-anchor='middle' fill='white' font-family='Arial' font-size='8' font-weight='bold'%3EJD%3C/text%3E%3C/svg%3E" alt="Assignee" class="w-5 h-5 rounded-full mr-1">
                                            <span class="text-xs text-gray-600">John D.</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Team Management Page -->
        <div id="team" class="page hidden fade-in">
            <div class="p-6">
                <div class="mb-8 flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800 mb-2">Team Management</h1>
                        <p class="text-gray-600">Manage your team members and their roles</p>
                    </div>
                    <button onclick="openAddMemberModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-plus mr-2"></i>Add Member
                    </button>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tasks</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40' viewBox='0 0 40 40'%3E%3Ccircle cx='20' cy='20' r='20' fill='%23EF4444'/%3E%3Ctext x='20' y='26' text-anchor='middle' fill='white' font-family='Arial' font-size='14' font-weight='bold'%3ESM%3C/text%3E%3C/svg%3E" alt="Profile" class="w-10 h-10 rounded-full">
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">Sarah Mitchell</div>
                                                <div class="text-sm text-gray-500">sarah@example.com</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            Scrum Master
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        3 active
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                                        <button class="text-red-600 hover:text-red-900">Remove</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40' viewBox='0 0 40 40'%3E%3Ccircle cx='20' cy='20' r='20' fill='%2310B981'/%3E%3Ctext x='20' y='26' text-anchor='middle' fill='white' font-family='Arial' font-size='14' font-weight='bold'%3EMJ%3C/text%3E%3C/svg%3E" alt="Profile" class="w-10 h-10 rounded-full">
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">Mike Johnson</div>
                                                <div class="text-sm text-gray-500">mike@example.com</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            Developer
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        5 active
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                                        <button class="text-red-600 hover:text-red-900">Remove</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40' viewBox='0 0 40 40'%3E%3Ccircle cx='20' cy='20' r='20' fill='%236366F1'/%3E%3Ctext x='20' y='26' text-anchor='middle' fill='white' font-family='Arial' font-size='14' font-weight='bold'%3EAL%3C/text%3E%3C/svg%3E" alt="Profile" class="w-10 h-10 rounded-full">
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">Alex Lee</div>
                                                <div class="text-sm text-gray-500">alex@example.com</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            Developer
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        2 active
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                                        <button class="text-red-600 hover:text-red-900">Remove</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40' viewBox='0 0 40 40'%3E%3Ccircle cx='20' cy='20' r='20' fill='%23F59E0B'/%3E%3Ctext x='20' y='26' text-anchor='middle' fill='white' font-family='Arial' font-size='14' font-weight='bold'%3EJD%3C/text%3E%3C/svg%3E" alt="Profile" class="w-10 h-10 rounded-full">
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">John Doe</div>
                                                <div class="text-sm text-gray-500">john@example.com</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                            Product Owner
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        1 active
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                                        <button class="text-red-600 hover:text-red-900">Remove</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Task Details Page -->
        <div id="task-details" class="page hidden fade-in">
            <div class="p-6">
                <div class="mb-6 flex items-center justify-between">
                    <button onclick="showPage('taskboard')" class="flex items-center text-gray-600 hover:text-gray-800">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Task Board
                    </button>
                    <div class="flex space-x-2">
                        <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                            Save Changes
                        </button>
                        <button class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors">
                            Delete Task
                        </button>
                    </div>
                </div>

                <div class="grid lg:grid-cols-3 gap-6">
                    <!-- Main Task Details -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Task Title</label>
                                <input type="text" value="Implement user roles" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                <textarea rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">Add role-based access control for different user types including admin, manager, and regular users. This should include proper permission checks and UI restrictions based on user roles.</textarea>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                                    <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option>Low</option>
                                        <option>Medium</option>
                                        <option selected>High</option>
                                        <option>Critical</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Story Points</label>
                                    <input type="number" value="5" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>
                        </div>

                        <!-- Comments Section -->
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Comments</h3>
                            <div class="space-y-4 mb-4">
                                <div class="flex space-x-3">
                                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='32' height='32' viewBox='0 0 32 32'%3E%3Ccircle cx='16' cy='16' r='16' fill='%23F59E0B'/%3E%3Ctext x='16' y='21' text-anchor='middle' fill='white' font-family='Arial' font-size='12' font-weight='bold'%3EJD%3C/text%3E%3C/svg%3E" alt="Profile" class="w-8 h-8 rounded-full">
                                    <div class="flex-1">
                                        <div class="bg-gray-50 p-3 rounded-lg">
                                            <div class="flex items-center justify-between mb-1">
                                                <span class="text-sm font-medium text-gray-800">John Doe</span>
                                                <span class="text-xs text-gray-500">2 hours ago</span>
                                            </div>
                                            <p class="text-sm text-gray-600">We should consider using a middleware approach for role checking to keep the code DRY.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex space-x-3">
                                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='32' height='32' viewBox='0 0 32 32'%3E%3Ccircle cx='16' cy='16' r='16' fill='%23EF4444'/%3E%3Ctext x='16' y='21' text-anchor='middle' fill='white' font-family='Arial' font-size='12' font-weight='bold'%3ESM%3C/text%3E%3C/svg%3E" alt="Profile" class="w-8 h-8 rounded-full">
                                    <div class="flex-1">
                                        <div class="bg-gray-50 p-3 rounded-lg">
                                            <div class="flex items-center justify-between mb-1">
                                                <span class="text-sm font-medium text-gray-800">Sarah Mitchell</span>
                                                <span class="text-xs text-gray-500">1 hour ago</span>
                                            </div>
                                            <p class="text-sm text-gray-600">Good point! I'll implement it using decorators for the API endpoints and a HOC for React components.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex space-x-3">
                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='32' height='32' viewBox='0 0 32 32'%3E%3Ccircle cx='16' cy='16' r='16' fill='%234F46E5'/%3E%3Ctext x='16' y='21' text-anchor='middle' fill='white' font-family='Arial' font-size='12' font-weight='bold'%3EYou%3C/text%3E%3C/svg%3E" alt="Profile" class="w-8 h-8 rounded-full">
                                <div class="flex-1">
                                    <textarea placeholder="Add a comment..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" rows="2"></textarea>
                                    <button class="mt-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm">
                                        Post Comment
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Task Metadata -->
                    <div class="space-y-6">
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Task Details</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                    <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option selected>Backlog</option>
                                        <option>To Do</option>
                                        <option>In Progress</option>
                                        <option>Done</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Assignee</label>
                                    <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option>Unassigned</option>
                                        <option selected>Sarah Mitchell</option>
                                        <option>Mike Johnson</option>
                                        <option>Alex Lee</option>
                                        <option>John Doe</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Due Date</label>
                                    <input type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Sprint</label>
                                    <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option>No Sprint</option>
                                        <option>Sprint 23</option>
                                        <option selected>Sprint 24</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Attachments</h3>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <i class="fas fa-file-pdf text-red-500"></i>
                                        <span class="text-sm text-gray-700">requirements.pdf</span>
                                    </div>
                                    <button class="text-gray-400 hover:text-gray-600">
                                        <i class="fas fa-download"></i>
                                    </button>
                                </div>
                                <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <i class="fas fa-image text-blue-500"></i>
                                        <span class="text-sm text-gray-700">mockup.png</span>
                                    </div>
                                    <button class="text-gray-400 hover:text-gray-600">
                                        <i class="fas fa-download"></i>
                                    </button>
                                </div>
                                <button class="w-full p-3 border-2 border-dashed border-gray-300 rounded-lg text-gray-500 hover:border-gray-400 hover:text-gray-600 transition-colors">
                                    <i class="fas fa-plus mr-2"></i>
                                    Add Attachment
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reports Page -->
        <div id="reports" class="page hidden fade-in">
            <div class="p-6">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">Reports & Analytics</h1>
                    <p class="text-gray-600">Track your team's progress and performance</p>
                </div>

                <div class="grid lg:grid-cols-2 gap-6 mb-6">
                    <!-- Burndown Chart -->
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Sprint Burndown Chart</h3>
                        <canvas id="burndownChart" width="400" height="200"></canvas>
                    </div>

                    <!-- Velocity Chart -->
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Team Velocity</h3>
                        <canvas id="velocityChart" width="400" height="200"></canvas>
                    </div>
                </div>

                <div class="grid lg:grid-cols-3 gap-6">
                    <!-- Task Completion by Member -->
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Task Completion by Member</h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='32' height='32' viewBox='0 0 32 32'%3E%3Ccircle cx='16' cy='16' r='16' fill='%23EF4444'/%3E%3Ctext x='16' y='21' text-anchor='middle' fill='white' font-family='Arial' font-size='12' font-weight='bold'%3ESM%3C/text%3E%3C/svg%3E" alt="Profile" class="w-8 h-8 rounded-full">
                                    <span class="text-sm font-medium text-gray-800">Sarah M.</span>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-medium text-gray-800">12 tasks</div>
                                    <div class="text-xs text-gray-500">85% complete</div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='32' height='32' viewBox='0 0 32 32'%3E%3Ccircle cx='16' cy='16' r='16' fill='%2310B981'/%3E%3Ctext x='16' y='21' text-anchor='middle' fill='white' font-family='Arial' font-size='12' font-weight='bold'%3EMJ%3C/text%3E%3C/svg%3E" alt="Profile" class="w-8 h-8 rounded-full">
                                    <span class="text-sm font-medium text-gray-800">Mike J.</span>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-medium text-gray-800">8 tasks</div>
                                    <div class="text-xs text-gray-500">75% complete</div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='32' height='32' viewBox='0 0 32 32'%3E%3Ccircle cx='16' cy='16' r='16' fill='%236366F1'/%3E%3Ctext x='16' y='21' text-anchor='middle' fill='white' font-family='Arial' font-size='12' font-weight='bold'%3EAL%3C/text%3E%3C/svg%3E" alt="Profile" class="w-8 h-8 rounded-full">
                                    <span class="text-sm font-medium text-gray-800">Alex L.</span>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-medium text-gray-800">6 tasks</div>
                                    <div class="text-xs text-gray-500">90% complete</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sprint Summary -->
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Current Sprint Summary</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Total Story Points</span>
                                <span class="text-sm font-medium text-gray-800">45</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Completed Points</span>
                                <span class="text-sm font-medium text-green-600">32</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Remaining Points</span>
                                <span class="text-sm font-medium text-orange-600">13</span>
                            </div>
                            <div class="pt-2 border-t border-gray-200">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Sprint Progress</span>
                                    <span class="text-sm font-medium text-blue-600">71%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: 71%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Metrics -->
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Key Metrics</h3>
                        <div class="space-y-4">
                            <div class="text-center p-3 bg-blue-50 rounded-lg">
                                <div class="text-2xl font-bold text-blue-600">2.3</div>
                                <div class="text-sm text-gray-600">Avg Cycle Time (days)</div>
                            </div>
                            <div class="text-center p-3 bg-green-50 rounded-lg">
                                <div class="text-2xl font-bold text-green-600">94%</div>
                                <div class="text-sm text-gray-600">Sprint Goal Success</div>
                            </div>
                            <div class="text-center p-3 bg-purple-50 rounded-lg">
                                <div class="text-2xl font-bold text-purple-600">38</div>
                                <div class="text-sm text-gray-600">Avg Velocity (SP)</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings Page -->
        <div id="settings" class="page hidden fade-in">
            <div class="p-6">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">Settings</h1>
                    <p class="text-gray-600">Manage your account and preferences</p>
                </div>

                <div class="grid lg:grid-cols-3 gap-6">
                    <!-- Settings Navigation -->
                    <div class="lg:col-span-1">
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                            <nav class="space-y-2">
                                <a href="#" onclick="showSettingsTab('profile')" class="settings-tab-link block px-3 py-2 rounded-lg text-blue-600 bg-blue-50 font-medium">
                                    <i class="fas fa-user mr-2"></i>Profile
                                </a>
                                <a href="#" onclick="showSettingsTab('notifications')" class="settings-tab-link block px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50">
                                    <i class="fas fa-bell mr-2"></i>Notifications
                                </a>
                                <a href="#" onclick="showSettingsTab('security')" class="settings-tab-link block px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50">
                                    <i class="fas fa-shield-alt mr-2"></i>Security
                                </a>
                                <a href="#" onclick="showSettingsTab('preferences')" class="settings-tab-link block px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50">
                                    <i class="fas fa-cog mr-2"></i>Preferences
                                </a>
                            </nav>
                        </div>
                    </div>

                    <!-- Settings Content -->
                    <div class="lg:col-span-2">
                        <!-- Profile Tab -->
                        <div id="profile-tab" class="settings-tab bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                            <h3 class="text-lg font-semibold text-gray-800 mb-6">Profile Information</h3>
                            <form class="space-y-6">
                                <div class="flex items-center space-x-6">
                                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='80' height='80' viewBox='0 0 80 80'%3E%3Ccircle cx='40' cy='40' r='40' fill='%234F46E5'/%3E%3Ctext x='40' y='50' text-anchor='middle' fill='white' font-family='Arial' font-size='24' font-weight='bold'%3EJD%3C/text%3E%3C/svg%3E" alt="Profile" class="w-20 h-20 rounded-full">
                                    <div>
                                        <button type="button" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm">
                                            Change Photo
                                        </button>
                                        <p class="text-xs text-gray-500 mt-1">JPG, PNG up to 2MB</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                                        <input type="text" value="John" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                                        <input type="text" value="Doe" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                    <input type="email" value="john@example.com" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Job Title</label>
                                    <input type="text" value="Product Owner" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Bio</label>
                                    <textarea rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Tell us about yourself..."></textarea>
                                </div>
                                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                    Save Changes
                                </button>
                            </form>
                        </div>

                        <!-- Notifications Tab -->
                        <div id="notifications-tab" class="settings-tab bg-white p-6 rounded-xl shadow-sm border border-gray-100 hidden">
                            <h3 class="text-lg font-semibold text-gray-800 mb-6">Notification Preferences</h3>
                            <div class="space-y-6">
                                <div>
                                    <h4 class="text-md font-medium text-gray-800 mb-3">Email Notifications</h4>
                                    <div class="space-y-3">
                                        <label class="flex items-center">
                                            <input type="checkbox" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="ml-2 text-sm text-gray-700">Task assignments</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="ml-2 text-sm text-gray-700">Sprint updates</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="ml-2 text-sm text-gray-700">Daily digest</span>
                                        </label>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-md font-medium text-gray-800 mb-3">Push Notifications</h4>
                                    <div class="space-y-3">
                                        <label class="flex items-center">
                                            <input type="checkbox" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="ml-2 text-sm text-gray-700">Task comments</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="ml-2 text-sm text-gray-700">Mentions</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="ml-2 text-sm text-gray-700">Due date reminders</span>
                                        </label>
                                    </div>
                                </div>
                                <button type="button" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                    Save Preferences
                                </button>
                            </div>
                        </div>

                        <!-- Security Tab -->
                        <div id="security-tab" class="settings-tab bg-white p-6 rounded-xl shadow-sm border border-gray-100 hidden">
                            <h3 class="text-lg font-semibold text-gray-800 mb-6">Security Settings</h3>
                            <div class="space-y-6">
                                <div>
                                    <h4 class="text-md font-medium text-gray-800 mb-3">Change Password</h4>
                                    <form class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                                            <input type="password" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                                            <input type="password" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                                            <input type="password" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        </div>
                                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                            Update Password
                                        </button>
                                    </form>
                                </div>
                                <div>
                                    <h4 class="text-md font-medium text-gray-800 mb-3">Two-Factor Authentication</h4>
                                    <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                        <div>
                                            <p class="text-sm font-medium text-gray-800">Enable 2FA</p>
                                            <p class="text-xs text-gray-500">Add an extra layer of security to your account</p>
                                        </div>
                                        <button class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors text-sm">
                                            Enable
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Preferences Tab -->
                        <div id="preferences-tab" class="settings-tab bg-white p-6 rounded-xl shadow-sm border border-gray-100 hidden">
                            <h3 class="text-lg font-semibold text-gray-800 mb-6">Application Preferences</h3>
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Theme</label>
                                    <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option selected>Light</option>
                                        <option>Dark</option>
                                        <option>Auto</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Language</label>
                                    <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option selected>English</option>
                                        <option>Spanish</option>
                                        <option>French</option>
                                        <option>German</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Timezone</label>
                                    <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option>UTC-8 (Pacific)</option>
                                        <option>UTC-5 (Eastern)</option>
                                        <option selected>UTC+0 (GMT)</option>
                                        <option>UTC+1 (CET)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Default Sprint Duration</label>
                                    <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option>1 week</option>
                                        <option selected>2 weeks</option>
                                        <option>3 weeks</option>
                                        <option>4 weeks</option>
                                    </select>
                                </div>
                                <button type="button" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                    Save Preferences
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

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

    <script>
        // Global state
        let currentPage = 'homepage';
        let isLoggedIn = false;

        // Page navigation
        function showPage(pageId) {
            // Hide all pages
            document.querySelectorAll('.page').forEach(page => {
                // page.classList.add('hidden');
            });

            // Show selected page
            document.getElementById(pageId).classList.remove('hidden');
            currentPage = pageId;

            // Show/hide navigation based on login status and page
            const navbar = document.getElementById('navbar');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');

            // if (pageId === 'homepage' || pageId === 'login' || pageId === 'signup') {
            //     navbar.classList.add('hidden');
            //     sidebar.classList.add('hidden');
            //     mainContent.classList.remove('lg:ml-64', 'pt-16');
            // } else {
            //     navbar.classList.remove('hidden');
            //     sidebar.classList.remove('hidden');
            //     mainContent.classList.add('lg:ml-64', 'pt-16');
            // }

            // Update active nav item
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('bg-blue-50', 'text-blue-600');
                item.classList.add('text-gray-700');
            });

            const activeNavItem = document.querySelector(`[onclick="showPage('${pageId}')"]`);
            if (activeNavItem && activeNavItem.classList.contains('nav-item')) {
                activeNavItem.classList.add('bg-blue-50', 'text-blue-600');
                activeNavItem.classList.remove('text-gray-700');
            }
        }

        // Authentication
        function handleLogin(event) {
            event.preventDefault();
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            if (email && password) {
                isLoggedIn = true;
                showPage('dashboard');
                // Show success message
                showNotification('Login successful! Welcome back.', 'success');
            } else {
                showNotification('Please fill in all fields.', 'error');
            }
        }

        function handleSignup(event) {
            event.preventDefault();
            const firstName = document.getElementById('firstName').value;
            const lastName = document.getElementById('lastName').value;
            const email = document.getElementById('signupEmail').value;
            const password = document.getElementById('signupPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (password !== confirmPassword) {
                showNotification('Passwords do not match.', 'error');
                return;
            }

            if (firstName && lastName && email && password) {
                isLoggedIn = true;
                showPage('dashboard');
                showNotification('Account created successfully! Welcome to ScrumFlow.', 'success');
            } else {
                showNotification('Please fill in all fields.', 'error');
            }
        }

        function logout() {
            isLoggedIn = false;
            showPage('homepage');
            showNotification('You have been logged out.', 'info');
        }

        // Sidebar toggle
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('-translate-x-full');
        });

        // Drag and drop functionality
        function allowDrop(ev) {
            ev.preventDefault();
            ev.currentTarget.classList.add('drag-over');
        }

        function drag(ev) {
            ev.dataTransfer.setData("text", ev.target.dataset.taskId);
            ev.target.classList.add('dragging');
        }

        function drop(ev) {
            ev.preventDefault();
            const taskId = ev.dataTransfer.getData("text");
            const taskElement = document.querySelector(`[data-task-id="${taskId}"]`);
            const column = ev.currentTarget.dataset.column;

            if (taskElement && column) {
                ev.currentTarget.appendChild(taskElement);
                taskElement.classList.remove('dragging');
                showNotification(`Task moved to ${column.replace(/([A-Z])/g, ' $1').toLowerCase()}`, 'success');
            }

            ev.currentTarget.classList.remove('drag-over');
        }

        // Remove drag-over class when dragging leaves
        document.addEventListener('dragleave', function(ev) {
            if (ev.target.hasAttribute('data-column')) {
                ev.target.classList.remove('drag-over');
            }
        });

        // Task modal functions


        function openTaskDetails(taskId) {
            showPage('task-details');
        }

        function openAddMemberModal() {
            showNotification('Add member functionality would open here.', 'info');
        }

        // Sprint planning functions
        function toggleTaskSelection(element) {
            const checkbox = element.querySelector('input[type="checkbox"]');
            checkbox.checked = !checkbox.checked;

            const points = parseInt(element.querySelector('.text-sm.text-gray-600').textContent.split(' ')[0]);
            const selectedPointsElement = document.getElementById('selectedPoints');
            let currentPoints = parseInt(selectedPointsElement.textContent);

            if (checkbox.checked) {
                currentPoints += points;
                element.classList.add('bg-blue-50', 'border-blue-200');
            } else {
                currentPoints -= points;
                element.classList.remove('bg-blue-50', 'border-blue-200');
            }

            selectedPointsElement.textContent = currentPoints;
        }

        // Settings tabs
        function showSettingsTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.settings-tab').forEach(tab => {
                tab.classList.add('hidden');
            });

            // Show selected tab
            document.getElementById(`${tabName}-tab`).classList.remove('hidden');

            // Update active tab link
            document.querySelectorAll('.settings-tab-link').forEach(link => {
                link.classList.remove('text-blue-600', 'bg-blue-50');
                link.classList.add('text-gray-700');
            });

            const activeLink = document.querySelector(`[onclick="showSettingsTab('${tabName}')"]`);
            if (activeLink) {
                activeLink.classList.add('text-blue-600', 'bg-blue-50');
                activeLink.classList.remove('text-gray-700');
            }
        }

        // Notification system
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            const colors = {
                success: 'bg-green-500',
                error: 'bg-red-500',
                info: 'bg-blue-500',
                warning: 'bg-yellow-500'
            };

            notification.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300`;
            notification.textContent = message;

            document.body.appendChild(notification);

            // Slide in
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);

            // Slide out and remove
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }

        // Initialize charts
        function initializeCharts() {
            // Burndown Chart
            const burndownCtx = document.getElementById('burndownChart');
            if (burndownCtx) {
                new Chart(burndownCtx, {
                    type: 'line',
                    data: {
                        labels: ['Day 1', 'Day 2', 'Day 3', 'Day 4', 'Day 5', 'Day 6', 'Day 7'],
                        datasets: [{
                            label: 'Ideal Burndown',
                            data: [45, 38, 32, 25, 19, 12, 0],
                            borderColor: '#94A3B8',
                            backgroundColor: 'transparent',
                            borderDash: [5, 5]
                        }, {
                            label: 'Actual Burndown',
                            data: [45, 42, 35, 28, 22, 13, 8],
                            borderColor: '#3B82F6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Story Points'
                                }
                            }
                        }
                    }
                });
            }

            // Velocity Chart
            const velocityCtx = document.getElementById('velocityChart');
            if (velocityCtx) {
                new Chart(velocityCtx, {
                    type: 'bar',
                    data: {
                        labels: ['Sprint 19', 'Sprint 20', 'Sprint 21', 'Sprint 22', 'Sprint 23'],
                        datasets: [{
                            label: 'Completed Story Points',
                            data: [32, 28, 35, 42, 38],
                            backgroundColor: '#10B981',
                            borderRadius: 4
                        }, {
                            label: 'Planned Story Points',
                            data: [35, 35, 40, 45, 40],
                            backgroundColor: '#E5E7EB',
                            borderRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Story Points'
                                }
                            }
                        }
                    }
                });
            }
        }

        // Initialize the application
        document.addEventListener('DOMContentLoaded', function() {
            showPage('homepage');

            // Initialize charts when reports page is shown
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                        const target = mutation.target;
                        if (target.id === 'reports' && !target.classList.contains('hidden')) {
                            setTimeout(initializeCharts, 100);
                        }
                    }
                });
            });

            const reportsPage = document.getElementById('reports');
            if (reportsPage) {
                observer.observe(reportsPage, { attributes: true });
            }
        });

        // Close modal when clicking outside
        document.getElementById('taskModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeTaskModal();
            }
        });

        // Handle form submissions
        document.addEventListener('submit', function(e) {
            e.preventDefault();
            showNotification('Form submitted successfully!', 'success');
        });
    </script>
<script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'969ea3bce46d5097',t:'MTc1NDMxNjY0MS4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html> --}}

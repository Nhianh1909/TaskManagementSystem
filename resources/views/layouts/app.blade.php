<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ScrumSpark - Modern Task Management System</title>
    <meta name="description" content="Professional Scrum-based task management with modern UI, drag-and-drop boards, and team collaboration tools">

    <meta name="csrf-token" content="{{ csrf_token() }}">
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

        // Initialize app
        document.addEventListener('DOMContentLoaded', function() {
            createParticles();
            // call showPage only if defined (some pages don't include the definition)
            if (typeof showPage === 'function') {
                showPage('homepage');
            }

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
     @stack('scripts')
</body>



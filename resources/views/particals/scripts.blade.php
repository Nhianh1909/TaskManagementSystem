<!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        /* Custom CSS Variables */
        :root {
            --primary-blue: #007BFF;
            --accent-gold: #FFD700;
            --neutral-gray: #F8F9FA;
            --dark-blue: #001F3F;
        }

        /* Particle Background Animation */
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }

        .particle {
            position: absolute;
            background: linear-gradient(45deg, var(--primary-blue), #00BFFF);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); opacity: 0.7; }
            50% { transform: translateY(-20px) rotate(180deg); opacity: 1; }
        }

        /* Gradient Buttons */
        .gradient-btn {
            background: linear-gradient(135deg, var(--primary-blue), #00BFFF);
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
        }

        .gradient-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 123, 255, 0.4);
            filter: brightness(1.1);
        }

        .gradient-gold {
            background: linear-gradient(135deg, var(--accent-gold), #FFA500);
        }

        /* Glow Effects */
        .glow-effect {
            transition: all 0.3s ease;
        }

        .glow-effect:hover {
            box-shadow: 0 0 20px rgba(255, 215, 0, 0.6);
            transform: scale(1.02);
        }

        /* Card Animations */
        .card-3d {
            transition: all 0.3s ease;
            transform-style: preserve-3d;
        }

        .card-3d:hover {
            transform: rotateY(5deg) rotateX(5deg);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        /* Parallax Effect */
        .parallax {
            background-attachment: fixed;
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
        }

        /* Task Priority Colors */
        .priority-high { border-left: 4px solid #FF4757; }
        .priority-medium { border-left: 4px solid #FFA502; }
        .priority-low { border-left: 4px solid #2ED573; }

        /* Drag and Drop Styles */
        .drag-over {
            background: linear-gradient(135deg, rgba(0, 123, 255, 0.1), rgba(0, 191, 255, 0.1));
            border: 2px dashed var(--primary-blue);
        }

        /* Dark Mode */
        .dark {
            background: var(--dark-blue);
            color: white;
        }

        .dark .bg-white {
            background: #1a1a2e !important;
        }

        /* Loading Animation */
        .loading-spinner {
            border: 3px solid rgba(0, 123, 255, 0.3);
            border-top: 3px solid var(--primary-blue);
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive Utilities */
        @media (max-width: 768px) {
            .mobile-hidden { display: none; }
            .mobile-full { width: 100%; }
        }
    </style>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель управления</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/tabulator-tables@5.5.2/dist/css/tabulator.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f0f2f5; /* Светло-серый фон */
            padding-left: 10px;
            padding-right: 10px;
        }
        .header {
            background-color: #ffffff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .tabulator {
            border: none;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden; /* для скругления углов */
        }
        .tabulator-header {
            background-color: #fafafa;
            border-bottom: 1px solid #e8e8e8;
            font-weight: 500;
            font-size: 0.75rem; /* Уменьшаем шрифт заголовков */
        }
        .tabulator-row {
            border-bottom: 1px solid #f0f0f0;
        }
        .tabulator-row:hover {
            background-color: #f5f5f5;
        }
        .tabulator-cell {
            max-width: 100%;
        }
        .btn-logout {
            background-color: #ef4444; /* red-500 */
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        .btn-logout:hover {
            background-color: #dc2626; /* red-600 */
        }
    </style>
</head>
<body>
<?php if (isset($_SESSION['user_login'])): ?>
    <header class="header p-4">
        <nav class="w-full mx-auto flex justify-between items-center">
            <div class="text-xl font-bold text-gray-800">
                База контактов
            </div>
            <div class="flex items-center space-x-4">
                <div class="hidden md:flex items-center space-x-2">
                    <div>
                        <span class="block text-right text-sm font-medium text-gray-700"><?php echo htmlspecialchars($_SESSION['user_login']); ?></span>
                        <span class="block text-right text-xs text-gray-500"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                    </div>
                    <a href="logout.php" class="btn-logout">Выйти</a>
                </div>
                <div class="relative md:hidden">
                    <button id="burger-btn" class="text-gray-600 focus:outline-none p-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                    </button>
                    <div id="mobile-menu" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-20 hidden">
                        <a href="help.php" class="block py-2 px-4 text-sm text-gray-700 hover:bg-gray-100">Справка</a>
                        <div class="border-t border-gray-100"></div>
                        <div class="py-2 px-4 text-sm text-gray-500"><?php echo htmlspecialchars($_SESSION['user_login']); ?></div>
                        <div class="py-2 px-4 text-xs text-gray-500"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
                        <a href="logout.php" class="block py-2 px-4 text-sm text-red-500 hover:bg-gray-100">Выйти</a>
                    </div>
                </div>
            </div>
        </nav>
    </header>
<?php endif; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const burgerBtn = document.getElementById('burger-btn');
            const mobileMenu = document.getElementById('mobile-menu');

            if (burgerBtn) {
                burgerBtn.addEventListener('click', function () {
                    mobileMenu.classList.toggle('hidden');
                });
            }

            // Close menu if clicking outside
            document.addEventListener('click', function (event) {
                if (mobileMenu && !mobileMenu.contains(event.target) && !burgerBtn.contains(event.target)) {
                    mobileMenu.classList.add('hidden');
                }
            });
        });
    </script>
    
    <main class="w-full mx-auto mt-6">
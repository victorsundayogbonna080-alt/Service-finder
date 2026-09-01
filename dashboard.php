<?php
session_start();

// Security Check: Kick them out if they aren't logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Check if the logged-in user is a normal customer or a service provider
$isCustomer = ($_SESSION['category'] === 'Customer');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - FixIt Direct</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { brand: { dark: '#0f5279', blue: '#1a73e8', light: '#e8f0fe', cyan: '#0ea5e9' } } } }
        }
    </script>
</head>
<body class="bg-gray-50 text-gray-800 font-sans min-h-screen flex flex-col">

    <header class="bg-brand-dark text-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-4 flex flex-col sm:flex-row justify-between items-center gap-4">
            
            <div class="flex items-center space-x-2">
                <span class="text-2xl">🛠️</span>
                <h1 class="text-xl font-bold tracking-tight">FixIt Direct</h1>
            </div>

            <nav class="flex items-center gap-3 text-sm font-bold">
                <a href="index.php" class="bg-white/10 hover:bg-white/20 text-white px-5 py-2.5 rounded-xl transition-all shadow-sm flex items-center gap-2">
                    🏠 Home
                </a>
                <a href="index.php?search=&location=All" class="bg-brand-cyan hover:bg-sky-400 text-white px-5 py-2.5 rounded-xl transition-all shadow-sm flex items-center gap-2">
                    🔍 Find Artisans
                </a>
                <a href="dashboard.php?logout=1" class="ml-2 text-gray-300 hover:text-white transition-colors underline-offset-4 hover:underline">
                    Log Out
                </a>
            </nav>

        </div>
    </header>

    <main class="flex-grow max-w-5xl mx-auto w-full mt-8 p-4">
        
        <div class="bg-white rounded-[2rem] shadow-xl border border-gray-100 p-8 sm:p-12 text-center animate-[fadeInUp_0.5s_ease-out]">
            
            <div class="text-6xl mb-6 p-5 bg-brand-light rounded-full inline-block border-4 border-blue-50 shadow-inner">
                <?= htmlspecialchars($_SESSION['icon']) ?>
            </div>
            
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-3 tracking-tight">
                Welcome back, <?= htmlspecialchars($_SESSION['full_name']) ?>!
            </h2>

            <?php if ($isCustomer): ?>
                <p class="text-gray-500 font-medium mb-10 text-lg max-w-2xl mx-auto">
                    Ready to get things fixed? Search our network of verified professionals across Nigeria.
                </p>
                
                <div class="bg-gray-50 rounded-2xl border border-gray-100 p-8 max-w-2xl mx-auto">
                    <h3 class="font-bold text-gray-800 mb-4 text-lg">Quick Actions</h3>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="index.php?category=Plumber&location=All" class="bg-white border border-gray-200 hover:border-brand-blue hover:shadow-md px-6 py-4 rounded-xl font-bold text-brand-dark transition-all flex items-center gap-2 justify-center">
                            🚰 Find a Plumber
                        </a>
                        <a href="index.php?category=Electrician&location=All" class="bg-white border border-gray-200 hover:border-brand-blue hover:shadow-md px-6 py-4 rounded-xl font-bold text-brand-dark transition-all flex items-center gap-2 justify-center">
                            ⚡ Find an Electrician
                        </a>
                    </div>
                </div>

            <?php else: ?>
                <p class="text-gray-500 font-medium mb-10 text-lg">
                    Your <span class="text-brand-blue font-bold"><?= htmlspecialchars($_SESSION['category']) ?></span> profile is active and visible in search results.
                </p>
                
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 border-t border-gray-100 pt-10">
                    <div class="p-6 bg-gray-50 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                        <div class="text-gray-400 text-xs font-extrabold uppercase tracking-wider mb-2">Profile Views</div>
                        <div class="text-4xl font-extrabold text-brand-dark">142</div>
                    </div>
                    <div class="p-6 bg-gray-50 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                        <div class="text-gray-400 text-xs font-extrabold uppercase tracking-wider mb-2">Current Rating</div>
                        <div class="text-4xl font-extrabold text-amber-500">⭐ 5.0</div>
                    </div>
                    <div class="p-6 bg-gray-50 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                        <div class="text-gray-400 text-xs font-extrabold uppercase tracking-wider mb-2">Verification Status</div>
                        <div class="text-2xl font-extrabold text-emerald-500 mt-2">Verified ✅</div>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </main>

</body>
</html>
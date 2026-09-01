<?php
session_start();
require 'db.php'; // Connect to the database

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $username  = trim($_POST['username']);
    $password  = trim($_POST['password']);
    $phone     = trim($_POST['phone']);
    $location  = trim($_POST['location']);
    $category  = trim($_POST['category']);

    // 1. Strict Validation Rules
    if (empty($full_name) || empty($username) || empty($password) || empty($phone) || empty($location) || empty($category)) {
        $error = "All fields are required.";
    } 
    // Block gibberish names (Must be letters and spaces only, at least 5 chars)
    elseif (!preg_match('/^[a-zA-Z\s]{5,50}$/', $full_name)) {
        $error = "Please enter a valid full name (letters only).";
    }
    // Block gibberish usernames (Alphanumeric only, 4 to 20 chars, no weird symbols)
    elseif (!preg_match('/^[a-zA-Z0-9]{4,20}$/', $username)) {
        $error = "Username must be 4-20 characters long and contain only letters and numbers.";
    }
    // Strictly validate Nigerian Phone Numbers (e.g. 080, 081, 090, 070 followed by 8 digits)
    elseif (!preg_match('/^(080|081|090|091|070)\d{8}$/', $phone)) {
        $error = "Please enter a valid 11-digit Nigerian phone number.";
    } 
    else {
        // 2. Check for Duplicate Username or Phone in Database
        $stmt_check = $conn->prepare("SELECT id FROM users WHERE username = ? OR phone = ?");
        $stmt_check->bind_param("ss", $username, $phone);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            $error = "That username or phone number is already registered.";
        } else {
            // 3. Assign Icon based on Category selection
            $icon = '👤'; // Default for Customer
            if ($category === 'Plumber') $icon = '🚰';
            if ($category === 'Electrician') $icon = '⚡';
            if ($category === 'Carpenter') $icon = '🔨';
            if ($category === 'Auto Mechanic') $icon = '🚗';
            if ($category === 'Generator Repair') $icon = '🔧';
            if ($category === 'Painter') $icon = '🎨';

            // 4. Insert into Database
            // Note: For MVP we are storing plain text passwords. In production, use password_hash()
            $stmt_insert = $conn->prepare("INSERT INTO users (full_name, username, password_hash, category, icon, location, phone, is_verified) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
            $stmt_insert->bind_param("sssssss", $full_name, $username, $password, $category, $icon, $location, $phone);
            
            if ($stmt_insert->execute()) {
                $success = "Account created successfully! Redirecting to login...";
                header("refresh:2;url=login.php"); // Send to login after 2 seconds
            } else {
                $error = "Database error. Please try again.";
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - FixIt Direct</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { brand: { dark: '#0f5279', blue: '#1a73e8', light: '#e8f0fe', cyan: '#0ea5e9' } } } }
        }
    </script>
    <style>
        .animate-fade-in-up { animation: fadeInUp 0.5s ease-out forwards; }
        @keyframes fadeInUp {
            0% { opacity: 0; transform: translateY(15px); }
            100% { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen font-sans selection:bg-brand-blue selection:text-white relative overflow-hidden py-10">

    <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-brand-blue rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse pointer-events-none"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-96 h-96 bg-brand-cyan rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse pointer-events-none" style="animation-delay: 2s;"></div>

    <div class="w-full max-w-xl p-4 relative z-10 animate-fade-in-up">
        
        <div class="text-center mb-6">
            <a href="index.php" class="inline-flex items-center space-x-2 text-brand-dark hover:opacity-80 transition-opacity">
                <span class="text-4xl drop-shadow-md">🛠️</span>
                <h1 class="text-3xl font-extrabold tracking-tight">FixIt Direct</h1>
            </a>
            <p class="text-gray-500 mt-2 text-sm font-medium">Join as a Customer or Service Provider.</p>
        </div>

        <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/50 p-8 sm:p-10">
            
            <?php if ($error): ?>
                <div class="bg-red-50 text-red-600 p-4 rounded-xl text-sm font-bold mb-6 border border-red-100 flex items-center gap-2">
                    <span>⚠️</span> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-emerald-50 text-emerald-600 p-4 rounded-xl text-sm font-bold mb-6 border border-emerald-100 flex items-center gap-2">
                    <span>✅</span> <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="register.php" class="space-y-5">
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-[10px] font-extrabold text-gray-500 uppercase tracking-wider mb-2">Full Name</label>
                        <input type="text" name="full_name" required placeholder="e.g. David Okorie" 
                               class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-blue font-medium shadow-sm">
                    </div>
                    <div>
                        <label class="block text-[10px] font-extrabold text-gray-500 uppercase tracking-wider mb-2">Unique Username</label>
                        <input type="text" name="username" required placeholder="e.g. davidokorie" 
                               class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-blue font-medium shadow-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-[10px] font-extrabold text-gray-500 uppercase tracking-wider mb-2">Phone Number</label>
                        <input type="tel" name="phone" required placeholder="e.g. 08012345678" 
                               class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-blue font-medium shadow-sm">
                    </div>
                    <div>
                        <label class="block text-[10px] font-extrabold text-gray-500 uppercase tracking-wider mb-2">Password</label>
                        <input type="password" name="password" required placeholder="••••••••" 
                               class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-blue font-medium shadow-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-[10px] font-extrabold text-gray-500 uppercase tracking-wider mb-2">Location</label>
                        <select name="location" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-blue font-medium shadow-sm">
                            <option value="Yenagoa, Bayelsa">Yenagoa, Bayelsa</option>
                            <option value="Umuahia, Abia">Umuahia, Abia</option>
                            <option value="Port Harcourt, Rivers">Port Harcourt, Rivers</option>
                            <option value="Ikeja, Lagos">Ikeja, Lagos</option>
                            <option value="Wuse, Abuja">Wuse, Abuja</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-extrabold text-gray-500 uppercase tracking-wider mb-2">Account Type</label>
                        <select name="category" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-blue font-medium shadow-sm">
                            <option value="Customer">Normal User (Customer)</option>
                            <option value="Plumber">Service: Plumber</option>
                            <option value="Electrician">Service: Electrician</option>
                            <option value="Carpenter">Service: Carpenter</option>
                            <option value="Auto Mechanic">Service: Auto Mechanic</option>
                            <option value="Generator Repair">Service: Generator Repair</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="w-full bg-brand-dark hover:bg-brand-blue text-white font-bold py-4 rounded-xl transition-all shadow-md active:scale-95 mt-4">
                    Create Account
                </button>
            </form>

            <div class="mt-6 text-center text-sm text-gray-500 font-medium border-t border-gray-100 pt-6">
                Already have an account? <a href="login.php" class="text-brand-blue font-bold hover:underline">Log In Here</a>
            </div>
        </div>
    </div>

</body>
</html>
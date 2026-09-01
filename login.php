<?php
session_start();
require 'db.php'; // Pull in our database connection

$error = '';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        // Securely query the database to prevent SQL Injection
        $stmt = $conn->prepare("SELECT id, full_name, username, password_hash, category, icon FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // For this MVP, we are comparing raw passwords. 
            // Later, we will upgrade this to password_verify($password, $user['password_hash'])
            if ($password === $user['password_hash']) {
                // Login successful! Store user data in the session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['category'] = $user['category'];
                $_SESSION['icon'] = $user['icon'];
                
                // Send them to their dashboard
                header("Location: dashboard.php");
                exit();
            } else {
                $error = 'Incorrect password.';
            }
        } else {
            $error = 'Username not found.';
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FixIt Direct</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { brand: { dark: '#0f5279', blue: '#1a73e8', light: '#e8f0fe', cyan: '#0ea5e9' } } } }
        }
    </script>
    <style>
        .animate-fade-in-up { animation: fadeInUp 0.6s ease-out forwards; }
        @keyframes fadeInUp {
            0% { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen font-sans selection:bg-brand-blue selection:text-white relative overflow-hidden">

    <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-brand-blue rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-96 h-96 bg-brand-cyan rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse" style="animation-delay: 2s;"></div>

    <div class="w-full max-w-md p-4 relative z-10 animate-fade-in-up">
        
        <div class="text-center mb-8">
            <a href="index.php" class="inline-flex items-center space-x-2 text-brand-dark hover:opacity-80 transition-opacity">
                <span class="text-4xl drop-shadow-md">🛠️</span>
                <h1 class="text-3xl font-extrabold tracking-tight">FixIt Direct</h1>
            </a>
            <p class="text-gray-500 mt-2 text-sm font-medium">Welcome back, Professional.</p>
        </div>

        <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/50 p-8 sm:p-10">
            
            <?php if ($error): ?>
                <div class="bg-red-50 text-red-600 p-4 rounded-xl text-sm font-bold mb-6 border border-red-100 flex items-center gap-2">
                    <span>⚠️</span> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php" class="space-y-6">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Username</label>
                    <input type="text" name="username" required placeholder="e.g. chineduokafor" 
                           class="w-full px-5 py-3.5 bg-gray-50 border border-gray-200 rounded-xl text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-blue focus:border-transparent transition-all shadow-sm"
                           value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
                </div>

                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Password</label>
                        <a href="#" class="text-xs font-bold text-brand-blue hover:text-blue-800 transition-colors">Forgot?</a>
                    </div>
                    <input type="password" name="password" required placeholder="••••••••" 
                           class="w-full px-5 py-3.5 bg-gray-50 border border-gray-200 rounded-xl text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-blue focus:border-transparent transition-all shadow-sm">
                </div>

                <button type="submit" class="w-full bg-brand-dark hover:bg-brand-blue text-white font-bold py-4 rounded-xl transition-colors shadow-lg hover:shadow-xl active:scale-95 flex justify-center items-center gap-2 mt-2">
                    Sign In <span>→</span>
                </button>
            </form>

            <div class="mt-8 text-center text-sm text-gray-500 font-medium">
                Not registered yet? <a href="register.php" class="text-brand-blue font-bold hover:underline">Create an Account</a>
            </div>
        </div>
    </div>

</body>
</html>
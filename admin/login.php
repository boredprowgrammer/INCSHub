<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

$error = '';
$success = '';

if ($_POST) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $username = sanitizeInput($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $error = 'Please fill in all fields.';
        } else {
            try {
                $stmt = $db->query(
                    "SELECT id, username, password_hash, full_name, is_active FROM admins WHERE username = ? LIMIT 1",
                    [$username]
                );
                $admin = $stmt->fetch();
                
                if ($admin && $admin['is_active'] && verifyPassword($password, $admin['password_hash'])) {
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    $_SESSION['admin_name'] = $admin['full_name'];
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['last_activity'] = time();
                    
                    // Update last login
                    $db->query("UPDATE admins SET last_login = NOW() WHERE id = ?", [$admin['id']]);
                    
                    header('Location: dashboard.php');
                    exit();
                } else {
                    $error = 'Invalid username or password.';
                }
            } catch (Exception $e) {
                error_log("Login error: " . $e->getMessage());
                $error = 'An error occurred. Please try again.';
            }
        }
    }
}

$pageTitle = 'Admin Login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — INCS Hub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] }
                }
            }
        }
    </script>
</head>
<body class="font-sans bg-zinc-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-sm">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="w-12 h-12 bg-zinc-900 rounded-xl flex items-center justify-center mx-auto mb-4">
                <span class="text-white font-bold text-lg">I</span>
            </div>
            <h1 class="text-xl font-semibold text-zinc-900">INCS Hub</h1>
            <p class="text-zinc-500 text-sm mt-1">Admin Panel</p>
        </div>
        
        <!-- Card -->
        <div class="bg-white rounded-xl border border-zinc-200 p-7">
            <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-5 text-[13px]">
                <?php echo escape($error); ?>
            </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg mb-5 text-[13px]">
                <?php echo escape($success); ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-5">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div>
                    <label for="username" class="block text-[13px] font-medium text-zinc-700 mb-1.5">Username</label>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           value="<?php echo escape($_POST['username'] ?? ''); ?>"
                           class="w-full px-3.5 py-2.5 bg-zinc-50 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:border-zinc-900 transition-colors"
                           placeholder="Enter username"
                           required>
                </div>
                
                <div>
                    <label for="password" class="block text-[13px] font-medium text-zinc-700 mb-1.5">Password</label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="w-full px-3.5 py-2.5 bg-zinc-50 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:border-zinc-900 transition-colors"
                           placeholder="Enter password"
                           required>
                </div>
                
                <button type="submit" 
                        class="w-full bg-zinc-900 text-white py-2.5 px-4 rounded-lg text-sm font-medium hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 transition-colors">
                    Sign In
                </button>
            </form>
            
            <div class="mt-5 pt-5 border-t border-zinc-100 text-center">
                <a href="../public/" class="text-zinc-500 hover:text-zinc-900 text-[13px] transition-colors">
                    ← Back to website
                </a>
            </div>
        </div>
        
        <p class="text-center mt-5 text-zinc-400 text-[12px]">
            Default: admin / admin123 — change after first login
        </p>
    </div>
</body>
</html>
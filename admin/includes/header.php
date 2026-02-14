<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? escape($pageTitle) . ' - ' : ''; ?>Admin · INCS Hub</title>
    
    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'sidebar': '#09090b',
                        'sidebar-hover': '#18181b',
                        'sidebar-active': '#27272a',
                        'brand': '#3b82f6'
                    },
                    fontFamily: {
                        'sans': ['Inter', 'system-ui', '-apple-system', 'sans-serif']
                    }
                }
            }
        }
    </script>
    
    <style>
        * { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
        .sidebar-transition { transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1); }
        .alert-enter { animation: alertIn 0.3s ease; }
        @keyframes alertIn { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="bg-zinc-50 antialiased text-zinc-900">
    <!-- Mobile overlay -->
    <div id="mobile-overlay" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-40 lg:hidden hidden transition-opacity"></div>
    
    <!-- Sidebar -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-[260px] bg-sidebar text-white transform -translate-x-full lg:translate-x-0 sidebar-transition flex flex-col">
        <!-- Logo -->
        <div class="flex items-center justify-between h-16 px-5 border-b border-zinc-800">
            <a href="dashboard.php" class="flex items-center space-x-3">
                <div class="w-7 h-7 bg-brand rounded-md flex items-center justify-center">
                    <span class="text-white text-xs font-bold">I</span>
                </div>
                <span class="text-[14px] font-semibold tracking-tight">INCS Admin</span>
            </a>
            <button id="close-sidebar" class="lg:hidden p-1.5 rounded-md hover:bg-sidebar-hover transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        
        <!-- Nav -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'bg-sidebar-active text-white' : 'text-zinc-400 hover:text-white hover:bg-sidebar-hover'; ?> flex items-center px-3 py-2 text-[13px] font-medium rounded-lg transition-all">
                <svg class="w-4 h-4 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>
            
            <div class="pt-4 pb-1">
                <p class="px-3 text-[11px] font-semibold uppercase tracking-widest text-zinc-600">Content</p>
            </div>
            
            <a href="content.php?type=news" class="<?php echo ($_GET['type'] ?? '') === 'news' ? 'bg-sidebar-active text-white' : 'text-zinc-400 hover:text-white hover:bg-sidebar-hover'; ?> flex items-center px-3 py-2 text-[13px] font-medium rounded-lg transition-all">
                <svg class="w-4 h-4 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                News Articles
            </a>
            
            <a href="content.php?type=announcement" class="<?php echo ($_GET['type'] ?? '') === 'announcement' ? 'bg-sidebar-active text-white' : 'text-zinc-400 hover:text-white hover:bg-sidebar-hover'; ?> flex items-center px-3 py-2 text-[13px] font-medium rounded-lg transition-all">
                <svg class="w-4 h-4 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                Announcements
            </a>
            
            <a href="events.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'events.php' ? 'bg-sidebar-active text-white' : 'text-zinc-400 hover:text-white hover:bg-sidebar-hover'; ?> flex items-center px-3 py-2 text-[13px] font-medium rounded-lg transition-all">
                <svg class="w-4 h-4 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Events
            </a>
            
            <a href="links.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'links.php' ? 'bg-sidebar-active text-white' : 'text-zinc-400 hover:text-white hover:bg-sidebar-hover'; ?> flex items-center px-3 py-2 text-[13px] font-medium rounded-lg transition-all">
                <svg class="w-4 h-4 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                Resource Links
            </a>
            
            <a href="telegram.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'telegram.php' ? 'bg-sidebar-active text-white' : 'text-zinc-400 hover:text-white hover:bg-sidebar-hover'; ?> flex items-center px-3 py-2 text-[13px] font-medium rounded-lg transition-all">
                <svg class="w-4 h-4 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                Telegram Officers
            </a>
            
            <a href="featured-images.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'featured-images.php' ? 'bg-sidebar-active text-white' : 'text-zinc-400 hover:text-white hover:bg-sidebar-hover'; ?> flex items-center px-3 py-2 text-[13px] font-medium rounded-lg transition-all">
                <svg class="w-4 h-4 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Featured Images
            </a>
            
            <div class="pt-4 pb-1">
                <p class="px-3 text-[11px] font-semibold uppercase tracking-widest text-zinc-600">Settings</p>
            </div>
            
            <a href="categories.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'categories.php' ? 'bg-sidebar-active text-white' : 'text-zinc-400 hover:text-white hover:bg-sidebar-hover'; ?> flex items-center px-3 py-2 text-[13px] font-medium rounded-lg transition-all">
                <svg class="w-4 h-4 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                Categories
            </a>
            
            <a href="settings.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'bg-sidebar-active text-white' : 'text-zinc-400 hover:text-white hover:bg-sidebar-hover'; ?> flex items-center px-3 py-2 text-[13px] font-medium rounded-lg transition-all">
                <svg class="w-4 h-4 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Site Settings
            </a>
        </nav>
        
        <!-- Sidebar footer -->
        <div class="px-3 py-4 border-t border-zinc-800">
            <a href="../public/" target="_blank" class="flex items-center px-3 py-2 text-[13px] font-medium text-zinc-400 hover:text-white hover:bg-sidebar-hover rounded-lg transition-all">
                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                View Site
            </a>
        </div>
    </aside>
    
    <!-- Main Content -->
    <div class="lg:ml-[260px] flex flex-col min-h-screen">
        <!-- Top Bar -->
        <header class="bg-white border-b border-zinc-200 sticky top-0 z-30">
            <div class="px-6 lg:px-8">
                <div class="flex justify-between h-14">
                    <div class="flex items-center space-x-4">
                        <button id="mobile-menu-button" class="lg:hidden p-2 -ml-2 rounded-lg text-zinc-400 hover:text-zinc-900 hover:bg-zinc-100 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>
                        
                        <div>
                            <h1 class="text-[15px] font-semibold text-zinc-900">
                                <?php echo isset($pageTitle) ? escape($pageTitle) : 'Admin Panel'; ?>
                            </h1>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <div class="flex items-center space-x-3">
                            <div class="w-7 h-7 bg-zinc-100 rounded-full flex items-center justify-center">
                                <svg class="w-3.5 h-3.5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                            <span class="text-[13px] font-medium text-zinc-700 hidden sm:block"><?php echo escape($_SESSION['admin_name'] ?? 'Admin'); ?></span>
                        </div>
                        <div class="w-px h-5 bg-zinc-200"></div>
                        <a href="logout.php" class="text-[13px] font-medium text-red-500 hover:text-red-700 transition-colors">Logout</a>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Page Content -->
        <main class="flex-1 p-6 lg:p-8">
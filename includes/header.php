<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? escape($pageTitle) . ' - ' : ''; ?>INCS Hub</title>
    <meta name="description" content="Stay updated with the latest news, announcements, and events from our community.">
    
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
                        'primary': '#0a0a0a',
                        'secondary': '#71717a',
                        'accent': '#18181b',
                        'surface': '#fafafa',
                        'border-c': '#e4e4e7',
                        'muted': '#f4f4f5'
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
        html { scroll-behavior: smooth; }
        .fade-up { opacity: 0; transform: translateY(20px); animation: fadeUp 0.6s ease forwards; }
        .fade-up-delay { animation-delay: 0.15s; }
        .fade-up-delay-2 { animation-delay: 0.3s; }
        @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }
        .nav-blur { backdrop-filter: saturate(180%) blur(20px); -webkit-backdrop-filter: saturate(180%) blur(20px); background: rgba(255,255,255,0.85); }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .hover-lift { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .hover-lift:hover { transform: translateY(-2px); box-shadow: 0 8px 25px -5px rgba(0,0,0,0.08); }
    </style>
    
    <!-- Facebook SDK -->
    <div id="fb-root"></div>
    <script>
        window.fbAsyncInit = function() { if (typeof FB !== 'undefined') FB.init({ xfbml: true, version: 'v18.0' }); };
        (function(d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) return;
            js = d.createElement(s); js.id = id; js.async = true; js.defer = true; js.crossOrigin = 'anonymous';
            js.src = 'https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v18.0';
            js.onerror = function() { document.querySelectorAll('.fb-post').forEach(function(p) { var c = p.closest('.facebook-post-container'); if (c) c.innerHTML = '<div class="text-center py-8 text-secondary text-sm">Facebook post unavailable</div>'; }); };
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
        if (typeof console !== 'undefined') { var ow = console.warn; console.warn = function(...a) { var m = a.join(' '); if (m.includes('Permissions-Policy') || m.includes('ErrorUtils') || m.includes('attribution-reporting') || m.includes('interest-cohort')) return; ow.apply(console, a); }; }
    </script>
</head>
<body class="bg-white min-h-screen antialiased text-primary">
    <!-- Navigation -->
    <nav class="nav-blur fixed top-0 left-0 right-0 z-50 border-b border-zinc-200/60">
        <div class="max-w-6xl mx-auto px-6">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/public/index.php" class="flex items-center space-x-3 group">
                        <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center group-hover:bg-zinc-700 transition-colors">
                            <span class="text-white text-sm font-bold">I</span>
                        </div>
                        <span class="text-[15px] font-semibold tracking-tight text-primary">INCS Hub</span>
                    </a>
                </div>
                
                <div class="hidden md:flex items-center space-x-1">
                    <a href="/public/index.php" class="px-3.5 py-2 text-[13px] font-medium text-secondary hover:text-primary hover:bg-zinc-100 rounded-lg transition-all">Home</a>
                    <a href="#news" class="px-3.5 py-2 text-[13px] font-medium text-secondary hover:text-primary hover:bg-zinc-100 rounded-lg transition-all">News</a>
                    <a href="#events" class="px-3.5 py-2 text-[13px] font-medium text-secondary hover:text-primary hover:bg-zinc-100 rounded-lg transition-all">Events</a>
                    <a href="#links" class="px-3.5 py-2 text-[13px] font-medium text-secondary hover:text-primary hover:bg-zinc-100 rounded-lg transition-all">Resources</a>
                    <a href="/public/telegram.php" class="ml-3 px-4 py-2 text-[13px] font-medium text-white bg-primary hover:bg-zinc-700 rounded-lg transition-all">Contact</a>
                </div>
                
                <div class="md:hidden flex items-center">
                    <button class="p-2 text-secondary hover:text-primary rounded-lg hover:bg-zinc-100 transition-all" onclick="toggleMobileMenu()">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        
        <div id="mobile-menu" class="md:hidden hidden border-t border-zinc-200/60 bg-white/95">
            <div class="px-4 py-3 space-y-1">
                <a href="/public/index.php" class="block px-4 py-2.5 text-sm text-secondary hover:text-primary hover:bg-zinc-100 rounded-lg transition-all">Home</a>
                <a href="#news" class="block px-4 py-2.5 text-sm text-secondary hover:text-primary hover:bg-zinc-100 rounded-lg transition-all">News</a>
                <a href="#events" class="block px-4 py-2.5 text-sm text-secondary hover:text-primary hover:bg-zinc-100 rounded-lg transition-all">Events</a>
                <a href="#links" class="block px-4 py-2.5 text-sm text-secondary hover:text-primary hover:bg-zinc-100 rounded-lg transition-all">Resources</a>
                <a href="/public/telegram.php" class="block px-4 py-2.5 text-sm font-medium text-white bg-primary rounded-lg text-center mt-2">Contact Officers</a>
            </div>
        </div>
    </nav>

    <div class="h-16"></div>

    <script>
        function toggleMobileMenu() { document.getElementById('mobile-menu').classList.toggle('hidden'); }
        document.querySelectorAll('#mobile-menu a').forEach(l => l.addEventListener('click', () => document.getElementById('mobile-menu').classList.add('hidden')));
        document.querySelectorAll('a[href^="#"]').forEach(a => a.addEventListener('click', function(e) { e.preventDefault(); const t = document.querySelector(this.getAttribute('href')); if (t) t.scrollIntoView({ behavior: 'smooth', block: 'start' }); }));
    </script>
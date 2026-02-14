    <!-- Footer -->
    <footer class="bg-zinc-950 text-white">
        <div class="max-w-6xl mx-auto py-16 px-6">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-12">
                <!-- Brand -->
                <div class="md:col-span-5">
                    <div class="flex items-center space-x-3 mb-5">
                        <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center">
                            <span class="text-zinc-950 text-sm font-bold">I</span>
                        </div>
                        <span class="text-base font-semibold tracking-tight">INCS Hub</span>
                    </div>
                    <p class="text-zinc-400 text-sm leading-relaxed max-w-sm">
                        Your central hub for news, events, and resources. Stay connected with everything happening in our community.
                    </p>
                </div>
                
                <!-- Quick Links -->
                <div class="md:col-span-3">
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-zinc-500 mb-4">Navigation</h4>
                    <ul class="space-y-2.5">
                        <li><a href="/public/index.php" class="text-zinc-400 hover:text-white transition-colors text-sm">Home</a></li>
                        <li><a href="#news" class="text-zinc-400 hover:text-white transition-colors text-sm">News</a></li>
                        <li><a href="#events" class="text-zinc-400 hover:text-white transition-colors text-sm">Events</a></li>
                        <li><a href="#links" class="text-zinc-400 hover:text-white transition-colors text-sm">Resources</a></li>
                    </ul>
                </div>
                
                <!-- Connect -->
                <div class="md:col-span-4">
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-zinc-500 mb-4">Connect</h4>
                    <ul class="space-y-2.5">
                        <li><a href="/public/telegram.php" class="text-zinc-400 hover:text-white transition-colors text-sm">Contact Officers</a></li>
                        <li><a href="https://wifiportal.onrender.com" target="_blank" class="text-zinc-400 hover:text-white transition-colors text-sm">WiFi Portal</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-zinc-800 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center text-zinc-500 text-xs">
                <p>&copy; <?php echo date('Y'); ?> NeoEra Tech. All rights reserved.</p>
                <p class="mt-2 md:mt-0">Built with care for the community.</p>
            </div>
        </div>
    </footer>
    
    <!-- Back to top -->
    <button id="backToTop" class="fixed bottom-6 right-6 w-10 h-10 bg-primary text-white rounded-full shadow-lg hover:bg-zinc-700 transition-all opacity-0 invisible flex items-center justify-center" onclick="scrollToTop()">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
        </svg>
    </button>

    <script>
        window.addEventListener('scroll', function() {
            const b = document.getElementById('backToTop');
            if (window.pageYOffset > 300) { b.classList.remove('opacity-0', 'invisible'); } 
            else { b.classList.add('opacity-0', 'invisible'); }
        });
        function scrollToTop() { window.scrollTo({ top: 0, behavior: 'smooth' }); }
    </script>
</body>
</html>
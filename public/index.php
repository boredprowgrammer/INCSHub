<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Welcome';

// Get featured content
try {
    $featuredNews = $db->query("
        SELECT c.*, cat.name as category_name, cat.color as category_color, a.full_name as author_name,
               CASE WHEN c.featured_image_blob IS NOT NULL THEN 1 ELSE 0 END as has_blob
        FROM content c 
        LEFT JOIN categories cat ON c.category_id = cat.id 
        LEFT JOIN admins a ON c.author_id = a.id
        WHERE c.is_published = 1 AND c.is_featured = 1 AND c.type = 'news' 
        ORDER BY c.created_at DESC 
        LIMIT 3
    ")->fetchAll();
    
    $recentAnnouncements = $db->query("
        SELECT c.*, cat.name as category_name, cat.color as category_color 
        FROM content c 
        LEFT JOIN categories cat ON c.category_id = cat.id 
        WHERE c.is_published = 1 AND c.type = 'announcement' 
        ORDER BY c.created_at DESC 
        LIMIT 5
    ")->fetchAll();
    
    $featuredLinks = $db->query("
        SELECT l.*, cat.name as category_name 
        FROM links l 
        LEFT JOIN categories cat ON l.category_id = cat.id 
        WHERE l.is_active = 1 AND l.is_featured = 1 
        ORDER BY l.created_at DESC 
        LIMIT 6
    ")->fetchAll();
    
    $featuredImages = $db->query("
        SELECT *, 
               CASE WHEN image_blob IS NOT NULL THEN 1 ELSE 0 END as has_blob
        FROM featured_images 
        WHERE is_active = 1 
        ORDER BY display_order ASC, created_at DESC 
        LIMIT 8
    ")->fetchAll();
    
    $upcomingEvents = $db->query("
        SELECT *,
               CASE WHEN featured_image_blob IS NOT NULL THEN 1 ELSE 0 END as has_blob
        FROM events 
        WHERE is_published = 1 AND event_date >= CURDATE() 
        ORDER BY event_date ASC, event_time ASC 
        LIMIT 4
    ")->fetchAll();
    
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    $featuredNews = [];
    $recentAnnouncements = [];
    $featuredLinks = [];
    $featuredImages = [];
    $upcomingEvents = [];
}

include __DIR__ . '/../includes/header.php';
?>

<!-- itshover-style CSS Animations -->
<style>
/* News / File Description Icon - pathLength draw effect */
.itshover-news .file-fold,
.itshover-news .file-lines {
    stroke-dasharray: 100;
    stroke-dashoffset: 0;
    transition: stroke-dashoffset 0.4s ease;
}
.group:hover .itshover-news .file-fold {
    stroke-dashoffset: 100;
    animation: itshover-draw 0.5s ease forwards;
}
.group:hover .itshover-news .file-lines {
    stroke-dashoffset: 100;
    animation: itshover-draw 0.5s ease 0.15s forwards;
}

/* Events / Alarm Clock Icon - bell shake + plus scale */
.itshover-events .bells {
    transition: transform 0.3s ease;
    transform-origin: center;
}
.itshover-events .plus {
    transition: transform 0.3s ease;
    transform-origin: 12px 13px;
}
.group:hover .itshover-events .bells {
    animation: itshover-bell-shake 0.5s ease;
}
.group:hover .itshover-events .plus {
    animation: itshover-plus-pop 0.4s ease;
}

/* Telegram Icon - fly/send effect */
.itshover-telegram .telegram-plane {
    transition: transform 0.3s ease;
}
.group:hover .itshover-telegram .telegram-plane {
    animation: itshover-fly 0.5s ease;
}

/* WiFi Icon - wave reveal cascade */
.itshover-wifi .wifi-dot,
.itshover-wifi .wifi-wave-1,
.itshover-wifi .wifi-wave-2,
.itshover-wifi .wifi-wave-3 {
    transition: opacity 0.3s ease, transform 0.3s ease;
}
.group:hover .itshover-wifi .wifi-dot {
    animation: itshover-wifi-pulse 0.6s ease;
}
.group:hover .itshover-wifi .wifi-wave-1 {
    animation: itshover-wave-in 0.3s ease 0.1s both;
}
.group:hover .itshover-wifi .wifi-wave-2 {
    animation: itshover-wave-in 0.3s ease 0.2s both;
}
.group:hover .itshover-wifi .wifi-wave-3 {
    animation: itshover-wave-in 0.3s ease 0.3s both;
}

/* Link Icon - chain rotate */
.itshover-link .link-top,
.itshover-link .link-bottom {
    transition: transform 0.3s ease;
    transform-origin: 50% 50%;
}
.itshover-link .link-middle {
    transition: transform 0.3s ease;
    transform-origin: 50% 50%;
}
.group:hover .itshover-link .link-top {
    animation: itshover-link-rotate 0.4s ease;
}
.group:hover .itshover-link .link-bottom {
    animation: itshover-link-rotate-reverse 0.4s ease;
}
.group:hover .itshover-link .link-middle {
    animation: itshover-link-pulse 0.3s ease;
}

/* Clock Icon - hands rotate */
.itshover-clock .clock-hands {
    transition: transform 0.5s ease;
    transform-origin: 12px 12px;
}
.group:hover .itshover-clock .clock-hands {
    animation: itshover-clock-spin 0.6s ease;
}

/* Sparkles / Star Icon - scale pop */
.itshover-sparkle {
    transition: transform 0.3s ease;
    transform-origin: center;
}
.group:hover .itshover-sparkle {
    animation: itshover-sparkle-pop 0.5s ease;
}

/* Generic path draw */
@keyframes itshover-draw {
    0% { stroke-dashoffset: 100; }
    100% { stroke-dashoffset: 0; }
}

/* Bell shake for alarm clock */
@keyframes itshover-bell-shake {
    0%, 100% { transform: rotate(0deg); }
    20% { transform: rotate(8deg); }
    40% { transform: rotate(-8deg); }
    60% { transform: rotate(5deg); }
    80% { transform: rotate(-3deg); }
}

/* Plus pop for alarm clock */
@keyframes itshover-plus-pop {
    0% { transform: scale(1); }
    50% { transform: scale(1.3); }
    100% { transform: scale(1); }
}

/* Telegram fly effect */
@keyframes itshover-fly {
    0% { transform: translate(0, 0); }
    25% { transform: translate(3px, -3px); }
    50% { transform: translate(-2px, 2px); }
    75% { transform: translate(1px, -1px); }
    100% { transform: translate(0, 0); }
}

/* WiFi pulse dot */
@keyframes itshover-wifi-pulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.4); opacity: 0.7; }
}

/* WiFi wave cascade */
@keyframes itshover-wave-in {
    0% { opacity: 0.2; transform: scale(0.9); }
    50% { opacity: 1; transform: scale(1.05); }
    100% { opacity: 1; transform: scale(1); }
}

/* Link chain rotate */
@keyframes itshover-link-rotate {
    0%, 100% { transform: rotate(0deg); }
    50% { transform: rotate(8deg); }
}
@keyframes itshover-link-rotate-reverse {
    0%, 100% { transform: rotate(0deg); }
    50% { transform: rotate(-8deg); }
}
@keyframes itshover-link-pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.15); }
}

/* Clock spin */
@keyframes itshover-clock-spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Sparkle pop */
@keyframes itshover-sparkle-pop {
    0%, 100% { transform: scale(1) rotate(0deg); }
    50% { transform: scale(1.2) rotate(15deg); }
}

/* Home icon parts */
.itshover-home .roof {
    transition: transform 0.4s ease, opacity 0.4s ease;
}
.itshover-home .house {
    transition: transform 0.3s ease;
    transform-origin: center;
}
.itshover-home .door {
    transition: transform 0.3s ease;
    transform-origin: center bottom;
}
.group:hover .itshover-home .roof {
    animation: itshover-roof-drop 0.4s ease;
}
.group:hover .itshover-home .house {
    animation: itshover-house-scale 0.3s ease;
}
.group:hover .itshover-home .door {
    animation: itshover-door-grow 0.3s ease 0.15s both;
}
@keyframes itshover-roof-drop {
    0% { transform: translateY(-3px); opacity: 0.6; }
    100% { transform: translateY(0); opacity: 1; }
}
@keyframes itshover-house-scale {
    0% { transform: scale(0.95); }
    100% { transform: scale(1); }
}
@keyframes itshover-door-grow {
    0% { transform: scaleY(0); }
    100% { transform: scaleY(1); }
}
</style>

<!-- Hero Section -->
<section id="home" class="relative py-24 md:py-32 bg-white">
    <div class="max-w-5xl mx-auto px-6 text-center">
        <div class="fade-up">
            <div class="inline-flex items-center px-3 py-1 bg-zinc-100 text-zinc-600 rounded-full text-xs font-medium mb-8 tracking-wide">
                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full mr-2 animate-pulse"></span>
                Community Hub
            </div>
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-semibold text-zinc-900 mb-6 leading-[1.1] tracking-tight">
                INCS Hub
            </h1>
            <p class="text-lg text-zinc-500 mb-12 max-w-xl mx-auto leading-relaxed font-light">
                Your central hub for news, events, and resources. Stay connected with everything happening.
            </p>
        </div>
        
        <div class="fade-up fade-up-delay grid grid-cols-2 md:grid-cols-4 gap-3 max-w-2xl mx-auto">
            <a href="#news" class="group flex flex-col items-center p-5 bg-zinc-50 hover:bg-zinc-100 rounded-xl transition-all">
                <div class="w-10 h-10 bg-white text-zinc-700 rounded-lg flex items-center justify-center mb-3 shadow-sm group-hover:shadow transition-shadow">
                    <svg class="w-5 h-5 itshover-news" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path class="file-fold" d="M14 3v4a1 1 0 0 0 1 1h4"/>
                        <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"/>
                        <path class="file-lines" d="M9 17h6"/>
                        <path class="file-lines" d="M9 13h6"/>
                    </svg>
                </div>
                <span class="text-[13px] font-medium text-zinc-700">News</span>
            </a>
            
            <a href="#events" class="group flex flex-col items-center p-5 bg-zinc-50 hover:bg-zinc-100 rounded-xl transition-all">
                <div class="w-10 h-10 bg-white text-zinc-700 rounded-lg flex items-center justify-center mb-3 shadow-sm group-hover:shadow transition-shadow">
                    <svg class="w-5 h-5 itshover-events" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle class="clock" cx="12" cy="13" r="8"/>
                        <path class="bells" d="M5 3 2 6" style="transform-origin: 3.5px 4.5px"/>
                        <path class="bells" d="m22 6-3-3" style="transform-origin: 20.5px 4.5px"/>
                        <path class="clock" d="M6.38 18.7 4 21"/>
                        <path class="clock" d="M17.64 18.67 20 21"/>
                        <g class="plus" style="transform-origin: 12px 13px">
                            <path d="M12 10v6"/>
                            <path d="M9 13h6"/>
                        </g>
                    </svg>
                </div>
                <span class="text-[13px] font-medium text-zinc-700">Events</span>
            </a>
            
            <a href="/public/telegram.php" class="group flex flex-col items-center p-5 bg-zinc-50 hover:bg-zinc-100 rounded-xl transition-all">
                <div class="w-10 h-10 bg-white text-zinc-700 rounded-lg flex items-center justify-center mb-3 shadow-sm group-hover:shadow transition-shadow">
                    <svg class="w-5 h-5 itshover-telegram" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path class="telegram-plane" d="M15 10l-4 4l6 6l4 -16l-18 7l4 2l2 6l3 -4"/>
                    </svg>
                </div>
                <span class="text-[13px] font-medium text-zinc-700">Officers</span>
            </a>
            
            <a href="https://wifiportal.onrender.com" target="_blank" rel="noopener noreferrer" class="group flex flex-col items-center p-5 bg-zinc-50 hover:bg-zinc-100 rounded-xl transition-all">
                <div class="w-10 h-10 bg-white text-zinc-700 rounded-lg flex items-center justify-center mb-3 shadow-sm group-hover:shadow transition-shadow">
                    <svg class="w-5 h-5 itshover-wifi" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path class="wifi-dot" d="M12 18l.01 0"/>
                        <path class="wifi-wave-1" d="M9.172 15.172a4 4 0 0 1 5.656 0"/>
                        <path class="wifi-wave-2" d="M6.343 12.343a8 8 0 0 1 11.314 0"/>
                        <path class="wifi-wave-3" d="M3.515 9.515c4.686 -4.687 12.284 -4.687 17 0"/>
                    </svg>
                </div>
                <span class="text-[13px] font-medium text-zinc-700">WiFi</span>
            </a>
        </div>
    </div>
</section>

<!-- Featured News -->
<section id="news" class="py-20 bg-zinc-50/50">
    <div class="max-w-6xl mx-auto px-6">
        <div class="flex items-center justify-between mb-10">
            <div>
                <h2 class="text-2xl font-semibold text-zinc-900 tracking-tight">Latest News</h2>
                <p class="text-sm text-zinc-500 mt-1">Stay informed with recent updates</p>
            </div>
            <div class="hidden md:block w-16 h-px bg-zinc-200"></div>
        </div>
        
        <?php if (!empty($featuredNews)): ?>
        <!-- Mobile: Horizontal scroll -->
        <div class="md:hidden flex overflow-x-auto gap-4 pb-4 -mx-6 px-6 no-scrollbar">
            <?php foreach ($featuredNews as $article): ?>
            <article class="bg-white border border-zinc-200 rounded-xl overflow-hidden min-w-[300px] flex-shrink-0 hover-lift">
                <?php if ($article['facebook_post_url']): ?>
                <div class="p-5 facebook-post-container">
                    <div class="h-80 overflow-y-auto border border-zinc-100 rounded-lg">
                        <div class="fb-post" data-href="<?php echo escape($article['facebook_post_url']); ?>" data-width="auto" data-show-text="true" data-lazy="true"></div>
                    </div>
                </div>
                <?php else: ?>
                <?php if ($article['featured_image']): ?>
                <div class="h-44 overflow-hidden">
                    <img src="/uploads/images/<?php echo escape($article['featured_image']); ?>" alt="<?php echo escape($article['title']); ?>" class="w-full h-full object-cover">
                </div>
                <?php else: ?>
                <div class="h-44 bg-zinc-100 flex items-center justify-center">
                    <svg class="w-8 h-8 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 3v4a1 1 0 0 0 1 1h4"/>
                        <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"/>
                        <path d="M9 17h6"/><path d="M9 13h6"/>
                    </svg>
                </div>
                <?php endif; ?>
                <div class="p-5">
                    <?php if ($article['category_name']): ?>
                    <span class="inline-block px-2 py-0.5 text-[11px] font-medium rounded-md mb-3" style="color: <?php echo escape($article['category_color']); ?>; background-color: <?php echo escape($article['category_color']); ?>10;"><?php echo escape($article['category_name']); ?></span>
                    <?php endif; ?>
                    <h3 class="text-[15px] font-semibold text-zinc-900 mb-2 leading-snug"><?php echo escape($article['title']); ?></h3>
                    <?php if ($article['excerpt']): ?><p class="text-zinc-500 text-[13px] leading-relaxed mb-3 line-clamp-2"><?php echo escape($article['excerpt']); ?></p><?php endif; ?>
                    <div class="flex items-center justify-between text-[11px] text-zinc-400">
                        <span><?php echo date('M j, Y', strtotime($article['created_at'])); ?></span>
                        <?php if ($article['author_name']): ?><span><?php echo escape($article['author_name']); ?></span><?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </article>
            <?php endforeach; ?>
        </div>
        
        <!-- Desktop: Grid -->
        <div class="hidden md:grid md:grid-cols-2 lg:grid-cols-3 gap-5">
            <?php foreach ($featuredNews as $article): ?>
            <article class="bg-white border border-zinc-200 rounded-xl overflow-hidden hover-lift">
                <?php if ($article['facebook_post_url']): ?>
                <div class="p-5 facebook-post-container">
                    <div class="h-80 overflow-y-auto border border-zinc-100 rounded-lg">
                        <div class="fb-post" data-href="<?php echo escape($article['facebook_post_url']); ?>" data-width="auto" data-show-text="true" data-lazy="true"></div>
                    </div>
                </div>
                <?php else: ?>
                <?php if ($article['featured_image']): ?>
                <div class="h-48 overflow-hidden">
                    <img src="/uploads/images/<?php echo escape($article['featured_image']); ?>" alt="<?php echo escape($article['title']); ?>" class="w-full h-full object-cover hover:scale-105 transition-transform duration-500">
                </div>
                <?php else: ?>
                <div class="h-48 bg-zinc-100 flex items-center justify-center">
                    <svg class="w-8 h-8 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 3v4a1 1 0 0 0 1 1h4"/>
                        <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"/>
                        <path d="M9 17h6"/><path d="M9 13h6"/>
                    </svg>
                </div>
                <?php endif; ?>
                <div class="p-5">
                    <?php if ($article['category_name']): ?>
                    <span class="inline-block px-2 py-0.5 text-[11px] font-medium rounded-md mb-3" style="color: <?php echo escape($article['category_color']); ?>; background-color: <?php echo escape($article['category_color']); ?>10;"><?php echo escape($article['category_name']); ?></span>
                    <?php endif; ?>
                    <h3 class="text-[15px] font-semibold text-zinc-900 mb-2 leading-snug"><?php echo escape($article['title']); ?></h3>
                    <?php if ($article['excerpt']): ?><p class="text-zinc-500 text-[13px] leading-relaxed mb-3 line-clamp-2"><?php echo escape($article['excerpt']); ?></p><?php endif; ?>
                    <div class="flex items-center justify-between text-[11px] text-zinc-400">
                        <span><?php echo date('M j, Y', strtotime($article['created_at'])); ?></span>
                        <?php if ($article['author_name']): ?><span><?php echo escape($article['author_name']); ?></span><?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </article>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-16">
            <div class="w-12 h-12 bg-zinc-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 3v4a1 1 0 0 0 1 1h4"/>
                    <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"/>
                    <path d="M9 17h6"/><path d="M9 13h6"/>
                </svg>
            </div>
            <p class="text-zinc-500 text-sm">No news available</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Featured Images -->
<section id="featured-images" class="py-20 bg-white">
    <div class="max-w-6xl mx-auto px-6">
        <div class="flex items-center justify-between mb-10">
            <div>
                <h2 class="text-2xl font-semibold text-zinc-900 tracking-tight">Featured Images</h2>
                <p class="text-sm text-zinc-500 mt-1">Highlights from our community</p>
            </div>
            <div class="hidden md:block w-16 h-px bg-zinc-200"></div>
        </div>
        
        <?php if (!empty($featuredImages)): ?>
        <!-- Mobile -->
        <div class="block md:hidden">
            <div class="flex overflow-x-auto gap-3 pb-3 -mx-6 px-6 no-scrollbar">
                <?php foreach ($featuredImages as $image): ?>
                <div class="cursor-pointer flex-shrink-0 w-28" <?php if ($image['link_url']): ?>onclick="window.open('<?php echo escape($image['link_url']); ?>', '_blank')"<?php endif; ?>>
                    <div class="relative overflow-hidden bg-zinc-100 rounded-lg w-28 h-28">
                        <img src="<?php echo $image['image_blob'] ? '/image.php?type=featured&id=' . $image['id'] : escape($image['image_path']); ?>" alt="<?php echo escape($image['title']); ?>" class="w-full h-full object-cover hover:scale-110 transition-transform duration-300">
                    </div>
                    <p class="text-[11px] text-zinc-600 mt-1.5 text-center line-clamp-2 leading-tight"><?php echo escape($image['title']); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Desktop -->
        <div class="hidden md:grid md:grid-cols-2 lg:grid-cols-4 gap-4">
            <?php foreach ($featuredImages as $image): ?>
            <div class="group cursor-pointer hover-lift" <?php if ($image['link_url']): ?>onclick="window.open('<?php echo escape($image['link_url']); ?>', '_blank')"<?php endif; ?>>
                <div class="relative overflow-hidden bg-zinc-100 rounded-xl aspect-[4/3]">
                    <img src="<?php echo $image['image_blob'] ? '/image.php?type=featured&id=' . $image['id'] : escape($image['image_path']); ?>" alt="<?php echo escape($image['title']); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-all duration-300"></div>
                    <div class="absolute bottom-0 left-0 right-0 p-4 text-white transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300">
                        <h3 class="text-sm font-semibold"><?php echo escape($image['title']); ?></h3>
                        <?php if ($image['description']): ?><p class="text-white/80 text-xs mt-1 opacity-0 group-hover:opacity-100 transition-opacity duration-300"><?php echo escape($image['description']); ?></p><?php endif; ?>
                    </div>
                    <?php if ($image['link_url']): ?>
                    <div class="absolute top-3 right-3 bg-white/20 backdrop-blur-sm rounded-full p-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-16">
            <p class="text-zinc-500 text-sm">No featured images available</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Upcoming Events -->
<section id="events" class="py-20 bg-zinc-50/50">
    <div class="max-w-6xl mx-auto px-6">
        <div class="flex items-center justify-between mb-10">
            <div>
                <h2 class="text-2xl font-semibold text-zinc-900 tracking-tight">Upcoming Events</h2>
                <p class="text-sm text-zinc-500 mt-1">Join us for upcoming activities</p>
            </div>
            <div class="hidden md:block w-16 h-px bg-zinc-200"></div>
        </div>
        
        <?php if (!empty($upcomingEvents)): ?>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <?php foreach ($upcomingEvents as $event): ?>
            <div class="bg-white border border-zinc-200 rounded-xl overflow-hidden hover-lift">
                <?php if ($event['featured_image']): ?>
                <div class="h-48 overflow-hidden">
                    <img src="<?php echo $event['featured_image_blob'] ? '/image.php?type=events&id=' . $event['id'] : '/uploads/images/' . escape($event['featured_image']); ?>" alt="<?php echo escape($event['title']); ?>" class="w-full h-full object-cover">
                </div>
                <?php endif; ?>
                
                <div class="p-6">
                    <div class="flex items-start justify-between mb-3">
                        <h3 class="text-[15px] font-semibold text-zinc-900 leading-snug flex-1"><?php echo escape($event['title']); ?></h3>
                        <div class="ml-4 flex-shrink-0 bg-zinc-100 rounded-lg px-3 py-2 text-center min-w-[52px]">
                            <div class="text-xs font-bold text-zinc-900"><?php echo date('M', strtotime($event['event_date'])); ?></div>
                            <div class="text-lg font-bold text-zinc-900 leading-tight"><?php echo date('j', strtotime($event['event_date'])); ?></div>
                        </div>
                    </div>
                    
                    <p class="text-zinc-500 text-[13px] leading-relaxed mb-4 line-clamp-2"><?php echo escape($event['description']); ?></p>
                    
                    <div class="flex flex-wrap gap-3 text-[12px] text-zinc-500">
                        <?php if ($event['event_time']): ?>
                        <span class="flex items-center group"><svg class="w-3.5 h-3.5 mr-1.5 text-zinc-400 itshover-clock" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"/><path class="clock-hands" d="M12 7v5l3 3"/></svg><?php echo date('g:i A', strtotime($event['event_time'])); ?></span>
                        <?php endif; ?>
                        <?php if ($event['location']): ?>
                        <span class="flex items-center"><svg class="w-3.5 h-3.5 mr-1.5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg><?php echo escape($event['location']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-16">
            <div class="w-12 h-12 bg-zinc-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="13" r="8"/>
                    <path d="M5 3 2 6"/><path d="m22 6-3-3"/>
                    <path d="M6.38 18.7 4 21"/><path d="M17.64 18.67 20 21"/>
                    <path d="M12 10v6"/><path d="M9 13h6"/>
                </svg>
            </div>
            <p class="text-zinc-500 text-sm">No events scheduled</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Featured Links -->
<section id="links" class="py-20 bg-white">
    <div class="max-w-6xl mx-auto px-6">
        <div class="flex items-center justify-between mb-10">
            <div>
                <h2 class="text-2xl font-semibold text-zinc-900 tracking-tight">Resources</h2>
                <p class="text-sm text-zinc-500 mt-1">Quick access to important links</p>
            </div>
            <div class="hidden md:block w-16 h-px bg-zinc-200"></div>
        </div>
        
        <?php if (!empty($featuredLinks)): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php foreach ($featuredLinks as $link): ?>
            <a href="<?php echo escape($link['url']); ?>" target="_blank" rel="noopener noreferrer" class="group bg-zinc-50 hover:bg-zinc-100 border border-zinc-200 rounded-xl p-5 flex items-start space-x-4 transition-all hover-lift">
                <div class="w-10 h-10 bg-white text-zinc-600 rounded-lg flex items-center justify-center flex-shrink-0 shadow-sm">
                    <?php if ($link['icon']): ?>
                    <i class="<?php echo escape($link['icon']); ?> text-sm"></i>
                    <?php else: ?>
                    <svg class="w-4 h-4 itshover-link" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path class="link-middle" d="M9 15l6 -6"/>
                        <path class="link-top" d="M11 6l.463 -.536a5 5 0 0 1 7.071 7.072l-.534 .464"/>
                        <path class="link-bottom" d="M13 18l-.397 .534a5.068 5.068 0 0 1 -7.127 0a4.972 4.972 0 0 1 0 -7.071l.524 -.463"/>
                    </svg>
                    <?php endif; ?>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-[14px] font-semibold text-zinc-900 mb-1"><?php echo escape($link['title']); ?></h3>
                    <?php if ($link['description']): ?><p class="text-zinc-500 text-[13px] leading-relaxed line-clamp-2"><?php echo escape($link['description']); ?></p><?php endif; ?>
                    <span class="inline-flex items-center text-zinc-400 group-hover:text-zinc-600 text-[12px] mt-2 transition-colors">
                        Visit
                        <svg class="w-3 h-3 ml-1 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-16">
            <div class="w-12 h-12 bg-zinc-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 15l6 -6"/>
                    <path d="M11 6l.463 -.536a5 5 0 0 1 7.071 7.072l-.534 .464"/>
                    <path d="M13 18l-.397 .534a5.068 5.068 0 0 1 -7.127 0a4.972 4.972 0 0 1 0 -7.071l.524 -.463"/>
                </svg>
            </div>
            <p class="text-zinc-500 text-sm">No resources available</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
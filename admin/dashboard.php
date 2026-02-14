<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check authentication
requireAdmin();

$pageTitle = 'Dashboard';

// Get statistics
try {
    $stats = [
        'total_content' => $db->query("SELECT COUNT(*) as count FROM content WHERE is_published = 1")->fetch()['count'],
        'total_news' => $db->query("SELECT COUNT(*) as count FROM content WHERE type = 'news' AND is_published = 1")->fetch()['count'],
        'total_announcements' => $db->query("SELECT COUNT(*) as count FROM content WHERE type = 'announcement' AND is_published = 1")->fetch()['count'],
        'total_events' => $db->query("SELECT COUNT(*) as count FROM events WHERE is_published = 1")->fetch()['count'],
        'total_links' => $db->query("SELECT COUNT(*) as count FROM links WHERE is_active = 1")->fetch()['count'],
        'recent_content' => $db->query("
            SELECT c.*, cat.name as category_name 
            FROM content c 
            LEFT JOIN categories cat ON c.category_id = cat.id 
            ORDER BY c.created_at DESC 
            LIMIT 5
        ")->fetchAll(),
        'upcoming_events' => $db->query("
            SELECT * FROM events 
            WHERE is_published = 1 AND event_date >= CURDATE() 
            ORDER BY event_date ASC 
            LIMIT 5
        ")->fetchAll()
    ];
} catch (Exception $e) {
    error_log("Dashboard stats error: " . $e->getMessage());
    $stats = array_fill_keys(['total_content', 'total_news', 'total_announcements', 'total_events', 'total_links'], 0);
    $stats['recent_content'] = [];
    $stats['upcoming_events'] = [];
}

include 'includes/header.php';
?>

<div class="space-y-6">
    <!-- Welcome -->
    <div class="bg-zinc-900 rounded-xl p-6 text-white">
        <h2 class="text-lg font-semibold mb-1">Welcome back, <?php echo escape($_SESSION['admin_name']); ?></h2>
        <p class="text-zinc-400 text-sm">Here's what's happening with your hub today.</p>
    </div>
    
    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
        <?php 
        $statItems = [
            ['label' => 'Published', 'value' => $stats['total_content'], 'color' => 'blue'],
            ['label' => 'News', 'value' => $stats['total_news'], 'color' => 'emerald'],
            ['label' => 'Announcements', 'value' => $stats['total_announcements'], 'color' => 'amber'],
            ['label' => 'Events', 'value' => $stats['total_events'], 'color' => 'violet'],
            ['label' => 'Links', 'value' => $stats['total_links'], 'color' => 'rose'],
        ];
        foreach ($statItems as $stat): ?>
        <div class="bg-white border border-zinc-200 rounded-xl p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-8 h-8 bg-<?php echo $stat['color']; ?>-50 rounded-lg flex items-center justify-center">
                    <div class="w-2 h-2 bg-<?php echo $stat['color']; ?>-500 rounded-full"></div>
                </div>
            </div>
            <p class="text-2xl font-semibold text-zinc-900"><?php echo $stat['value']; ?></p>
            <p class="text-[12px] text-zinc-500 mt-0.5"><?php echo $stat['label']; ?></p>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Quick Actions -->
    <div class="bg-white border border-zinc-200 rounded-xl p-5">
        <h3 class="text-[13px] font-semibold text-zinc-900 mb-4">Quick Actions</h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <a href="content.php?action=add&type=news" class="flex items-center p-3 bg-zinc-50 hover:bg-zinc-100 rounded-lg transition-colors group">
                <div class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/></svg>
                </div>
                <span class="text-[12px] font-medium text-zinc-700">New Article</span>
            </a>
            <a href="content.php?action=add&type=announcement" class="flex items-center p-3 bg-zinc-50 hover:bg-zinc-100 rounded-lg transition-colors group">
                <div class="w-8 h-8 bg-amber-50 text-amber-600 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/></svg>
                </div>
                <span class="text-[12px] font-medium text-zinc-700">Announcement</span>
            </a>
            <a href="events.php?action=add" class="flex items-center p-3 bg-zinc-50 hover:bg-zinc-100 rounded-lg transition-colors group">
                <div class="w-8 h-8 bg-emerald-50 text-emerald-600 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/></svg>
                </div>
                <span class="text-[12px] font-medium text-zinc-700">New Event</span>
            </a>
            <a href="links.php?action=add" class="flex items-center p-3 bg-zinc-50 hover:bg-zinc-100 rounded-lg transition-colors group">
                <div class="w-8 h-8 bg-violet-50 text-violet-600 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/></svg>
                </div>
                <span class="text-[12px] font-medium text-zinc-700">Add Link</span>
            </a>
        </div>
    </div>
    
    <!-- Two Column -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Content -->
        <div class="bg-white border border-zinc-200 rounded-xl">
            <div class="px-5 py-4 border-b border-zinc-100 flex items-center justify-between">
                <h3 class="text-[13px] font-semibold text-zinc-900">Recent Content</h3>
                <a href="content.php" class="text-[12px] text-blue-600 hover:text-blue-700 font-medium">View all</a>
            </div>
            <div class="divide-y divide-zinc-100">
                <?php if (!empty($stats['recent_content'])): ?>
                <?php foreach ($stats['recent_content'] as $content): ?>
                <div class="px-5 py-3.5 flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 <?php echo $content['type'] === 'news' ? 'bg-blue-50 text-blue-500' : 'bg-amber-50 text-amber-500'; ?>">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[13px] font-medium text-zinc-900 truncate"><?php echo escape($content['title']); ?></p>
                        <p class="text-[11px] text-zinc-400"><?php echo ucfirst($content['type']); ?> · <?php echo date('M j', strtotime($content['created_at'])); ?></p>
                    </div>
                    <span class="flex-shrink-0 inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-medium <?php echo $content['is_published'] ? 'bg-emerald-50 text-emerald-700' : 'bg-zinc-100 text-zinc-500'; ?>">
                        <?php echo $content['is_published'] ? 'Live' : 'Draft'; ?>
                    </span>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="px-5 py-8 text-center text-zinc-400 text-sm">No content yet</div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Upcoming Events -->
        <div class="bg-white border border-zinc-200 rounded-xl">
            <div class="px-5 py-4 border-b border-zinc-100 flex items-center justify-between">
                <h3 class="text-[13px] font-semibold text-zinc-900">Upcoming Events</h3>
                <a href="events.php" class="text-[12px] text-blue-600 hover:text-blue-700 font-medium">View all</a>
            </div>
            <div class="divide-y divide-zinc-100">
                <?php if (!empty($stats['upcoming_events'])): ?>
                <?php foreach ($stats['upcoming_events'] as $event): ?>
                <div class="px-5 py-3.5 flex items-center space-x-3">
                    <div class="w-10 h-10 bg-violet-50 rounded-lg flex flex-col items-center justify-center flex-shrink-0">
                        <span class="text-[10px] font-bold text-violet-600 leading-none"><?php echo date('M', strtotime($event['event_date'])); ?></span>
                        <span class="text-sm font-bold text-violet-700 leading-tight"><?php echo date('j', strtotime($event['event_date'])); ?></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[13px] font-medium text-zinc-900 truncate"><?php echo escape($event['title']); ?></p>
                        <p class="text-[11px] text-zinc-400">
                            <?php if ($event['event_time']): ?><?php echo date('g:i A', strtotime($event['event_time'])); ?><?php endif; ?>
                            <?php if ($event['location']): ?> · <?php echo escape($event['location']); ?><?php endif; ?>
                        </p>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="px-5 py-8 text-center text-zinc-400 text-sm">No upcoming events</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<?php
require_once __DIR__ . '/../includes/config.php';

// Get active telegram officers ordered by display_order
try {
    $stmt = $db->query("SELECT * FROM telegram_officers WHERE is_active = 1 ORDER BY display_order ASC, name ASC");
    $officers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $officers = [];
}

include __DIR__ . '/../includes/header.php';
?>

<!-- Hero Section -->
<section class="pt-32 pb-16 bg-zinc-50">
    <div class="max-w-4xl mx-auto px-6 text-center">
        <div class="inline-flex items-center justify-center w-14 h-14 bg-zinc-900 text-white rounded-2xl mb-6">
            <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24">
                <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
            </svg>
        </div>
        <h1 class="text-3xl md:text-4xl font-semibold text-zinc-900 mb-4">Contact INCS Officers</h1>
        <p class="text-zinc-500 text-lg max-w-xl mx-auto">
            Reach out to our officers directly via Telegram for assistance.
        </p>
    </div>
</section>

<!-- Officers Grid -->
<section class="py-20 bg-white">
    <div class="max-w-6xl mx-auto px-6">
        <?php if (empty($officers)): ?>
        <div class="text-center py-16">
            <div class="w-14 h-14 bg-zinc-100 text-zinc-400 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-zinc-900 mb-1">No Officers Available</h3>
            <p class="text-zinc-400 text-sm">Officer contact information will be displayed here once configured.</p>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($officers as $officer): ?>
            <a href="https://t.me/<?php echo htmlspecialchars($officer['telegram_username']); ?>" 
               target="_blank" rel="noopener noreferrer" 
               class="group bg-white border border-zinc-200 rounded-xl p-6 text-center hover:border-zinc-300 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                <div class="w-12 h-12 bg-<?php echo htmlspecialchars($officer['icon_color']); ?>-500 text-white rounded-xl flex items-center justify-center mx-auto mb-4 group-hover:scale-105 transition-transform">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                    </svg>
                </div>
                <h3 class="text-[15px] font-semibold text-zinc-900 mb-1"><?php echo htmlspecialchars($officer['name']); ?></h3>
                <p class="text-[13px] font-medium text-<?php echo htmlspecialchars($officer['icon_color']); ?>-600 mb-3"><?php echo htmlspecialchars($officer['position']); ?></p>
                <p class="text-zinc-500 text-[13px] leading-relaxed"><?php echo htmlspecialchars($officer['description']); ?></p>
                <div class="mt-4 pt-4 border-t border-zinc-100">
                    <span class="text-[12px] text-zinc-400 font-medium group-hover:text-<?php echo htmlspecialchars($officer['icon_color']); ?>-600 transition-colors">
                        @<?php echo htmlspecialchars($officer['telegram_username']); ?>
                    </span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Instructions Section -->
<section class="py-16 bg-zinc-50">
    <div class="max-w-3xl mx-auto px-6 text-center">
        <div class="bg-white border border-zinc-200 rounded-xl p-8">
            <h3 class="text-[15px] font-semibold text-zinc-900 mb-5">How to Connect</h3>
            <div class="space-y-3 text-zinc-600">
                <p class="flex items-center justify-center text-[13px]">
                    <svg class="w-4 h-4 mr-2 flex-shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    Click on any officer's card to open Telegram
                </p>
                <p class="flex items-center justify-center text-[13px]">
                    <svg class="w-4 h-4 mr-2 flex-shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    Our officers are here to help with any questions or concerns
                </p>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
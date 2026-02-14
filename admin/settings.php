<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check authentication
requireAdmin();

$pageTitle = 'Site Settings';
$success = '';
$error = '';

// Handle form submission
if ($_POST) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        try {
            $settings = $_POST['settings'] ?? [];
            
            foreach ($settings as $key => $value) {
                $value = sanitizeInput($value);
                $stmt = $db->query(
                    "UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?",
                    [$value, $key]
                );
            }
            
            $success = 'Settings updated successfully!';
        } catch (Exception $e) {
            error_log("Settings update error: " . $e->getMessage());
            $error = 'An error occurred while updating settings.';
        }
    }
}

// Get settings
try {
    $settings = $db->query("SELECT * FROM settings ORDER BY setting_key ASC")->fetchAll();
    $settingsArray = [];
    foreach ($settings as $setting) {
        $settingsArray[$setting['setting_key']] = $setting;
    }
} catch (Exception $e) {
    error_log("Settings fetch error: " . $e->getMessage());
    $settingsArray = [];
}

include 'includes/header.php';
?>

<?php if ($success): ?>
<div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg mb-6 text-[13px] alert-auto-hide">
    <?php echo escape($success); ?>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 text-[13px]">
    <?php echo escape($error); ?>
</div>
<?php endif; ?>

<div class="bg-white border border-zinc-200 rounded-xl">
    <div class="px-6 py-4 border-b border-zinc-100">
        <h3 class="text-[15px] font-semibold text-zinc-900">Site Settings</h3>
        <p class="text-[12px] text-zinc-400 mt-0.5">Configure your hub settings</p>
    </div>
    
    <form method="POST" class="p-6">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        
        <div class="space-y-8">
            <!-- General Settings -->
            <div>
                <h4 class="text-[13px] font-semibold text-zinc-900 mb-4">General Information</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <?php if (isset($settingsArray['site_title'])): ?>
                    <div>
                        <label for="site_title" class="block text-[13px] font-medium text-zinc-700 mb-1.5">Site Title</label>
                        <input type="text" id="site_title" name="settings[site_title]" 
                               value="<?php echo escape($settingsArray['site_title']['setting_value']); ?>"
                               class="w-full px-3 py-2 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-[11px] text-zinc-400 mt-1"><?php echo escape($settingsArray['site_title']['description']); ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($settingsArray['church_name'])): ?>
                    <div>
                        <label for="church_name" class="block text-[13px] font-medium text-zinc-700 mb-1.5">Church Name</label>
                        <input type="text" id="church_name" name="settings[church_name]" 
                               value="<?php echo escape($settingsArray['church_name']['setting_value']); ?>"
                               class="w-full px-3 py-2 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-[11px] text-zinc-400 mt-1"><?php echo escape($settingsArray['church_name']['description']); ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($settingsArray['contact_email'])): ?>
                    <div>
                        <label for="contact_email" class="block text-[13px] font-medium text-zinc-700 mb-1.5">Contact Email</label>
                        <input type="email" id="contact_email" name="settings[contact_email]" 
                               value="<?php echo escape($settingsArray['contact_email']['setting_value']); ?>"
                               class="w-full px-3 py-2 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-[11px] text-zinc-400 mt-1"><?php echo escape($settingsArray['contact_email']['description']); ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($settingsArray['church_phone'])): ?>
                    <div>
                        <label for="church_phone" class="block text-[13px] font-medium text-zinc-700 mb-1.5">Church Phone</label>
                        <input type="text" id="church_phone" name="settings[church_phone]" 
                               value="<?php echo escape($settingsArray['church_phone']['setting_value']); ?>"
                               class="w-full px-3 py-2 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-[11px] text-zinc-400 mt-1"><?php echo escape($settingsArray['church_phone']['description']); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if (isset($settingsArray['site_description'])): ?>
                <div class="mt-5">
                    <label for="site_description" class="block text-[13px] font-medium text-zinc-700 mb-1.5">Site Description</label>
                    <textarea id="site_description" name="settings[site_description]" rows="3"
                              class="w-full px-3 py-2 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"><?php echo escape($settingsArray['site_description']['setting_value']); ?></textarea>
                    <p class="text-[11px] text-zinc-400 mt-1"><?php echo escape($settingsArray['site_description']['description']); ?></p>
                </div>
                <?php endif; ?>
                
                <?php if (isset($settingsArray['church_address'])): ?>
                <div class="mt-5">
                    <label for="church_address" class="block text-[13px] font-medium text-zinc-700 mb-1.5">Church Address</label>
                    <textarea id="church_address" name="settings[church_address]" rows="2"
                              class="w-full px-3 py-2 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"><?php echo escape($settingsArray['church_address']['setting_value']); ?></textarea>
                    <p class="text-[11px] text-zinc-400 mt-1"><?php echo escape($settingsArray['church_address']['description']); ?></p>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Display Settings -->
            <div class="border-t border-zinc-100 pt-8">
                <h4 class="text-[13px] font-semibold text-zinc-900 mb-4">Display Settings</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <?php if (isset($settingsArray['posts_per_page'])): ?>
                    <div>
                        <label for="posts_per_page" class="block text-[13px] font-medium text-zinc-700 mb-1.5">Posts Per Page</label>
                        <input type="number" id="posts_per_page" name="settings[posts_per_page]" 
                               value="<?php echo escape($settingsArray['posts_per_page']['setting_value']); ?>"
                               min="1" max="50"
                               class="w-full px-3 py-2 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-[11px] text-zinc-400 mt-1"><?php echo escape($settingsArray['posts_per_page']['description']); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Feature Settings -->
            <div class="border-t border-zinc-100 pt-8">
                <h4 class="text-[13px] font-semibold text-zinc-900 mb-4">Feature Settings</h4>
                <div class="space-y-4">
                    <?php if (isset($settingsArray['enable_comments'])): ?>
                    <label class="flex items-start cursor-pointer group">
                        <input type="hidden" name="settings[enable_comments]" value="0">
                        <input type="checkbox" name="settings[enable_comments]" value="1"
                               <?php echo $settingsArray['enable_comments']['setting_value'] ? 'checked' : ''; ?>
                               class="w-4 h-4 mt-0.5 rounded border-zinc-300 text-blue-600 focus:ring-blue-500">
                        <div class="ml-3">
                            <span class="text-[13px] font-medium text-zinc-700 group-hover:text-zinc-900">Enable Comments</span>
                            <p class="text-[11px] text-zinc-400"><?php echo escape($settingsArray['enable_comments']['description']); ?></p>
                        </div>
                    </label>
                    <?php endif; ?>
                    
                    <?php if (isset($settingsArray['maintenance_mode'])): ?>
                    <label class="flex items-start cursor-pointer group">
                        <input type="hidden" name="settings[maintenance_mode]" value="0">
                        <input type="checkbox" name="settings[maintenance_mode]" value="1"
                               <?php echo $settingsArray['maintenance_mode']['setting_value'] ? 'checked' : ''; ?>
                               class="w-4 h-4 mt-0.5 rounded border-zinc-300 text-blue-600 focus:ring-blue-500">
                        <div class="ml-3">
                            <span class="text-[13px] font-medium text-zinc-700 group-hover:text-zinc-900">Maintenance Mode</span>
                            <p class="text-[11px] text-zinc-400"><?php echo escape($settingsArray['maintenance_mode']['description']); ?></p>
                        </div>
                    </label>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="border-t border-zinc-100 pt-5 mt-8">
            <button type="submit" 
                    class="bg-zinc-900 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-zinc-700 transition-colors">
                Save Settings
            </button>
        </div>
    </form>
</div>

<!-- System Information -->
<div class="bg-white border border-zinc-200 rounded-xl mt-6">
    <div class="px-6 py-4 border-b border-zinc-100">
        <h3 class="text-[13px] font-semibold text-zinc-900">System Information</h3>
    </div>
    
    <div class="p-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div>
                <div class="text-[11px] text-zinc-400 uppercase tracking-wider mb-1">PHP Version</div>
                <div class="text-[13px] font-medium text-zinc-900"><?php echo PHP_VERSION; ?></div>
            </div>
            <div>
                <div class="text-[11px] text-zinc-400 uppercase tracking-wider mb-1">Upload Max</div>
                <div class="text-[13px] font-medium text-zinc-900"><?php echo ini_get('upload_max_filesize'); ?></div>
            </div>
            <div>
                <div class="text-[11px] text-zinc-400 uppercase tracking-wider mb-1">Post Max</div>
                <div class="text-[13px] font-medium text-zinc-900"><?php echo ini_get('post_max_size'); ?></div>
            </div>
            <div>
                <div class="text-[11px] text-zinc-400 uppercase tracking-wider mb-1">Memory Limit</div>
                <div class="text-[13px] font-medium text-zinc-900"><?php echo ini_get('memory_limit'); ?></div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
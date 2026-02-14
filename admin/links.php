<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check authentication
requireAdmin();

$pageTitle = 'Manage Links';
$success = '';
$error = '';

// Handle form submissions
if ($_POST) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'add' || $action === 'edit') {
            $title = sanitizeInput($_POST['title'] ?? '');
            $url = validateURL($_POST['url'] ?? '');
            $description = sanitizeInput($_POST['description'] ?? '');
            $category_id = (int)($_POST['category_id'] ?? 0);
            $icon = sanitizeInput($_POST['icon'] ?? '');
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            if (empty($title) || empty($url)) {
                $error = 'Title and URL are required.';
            } else {
                try {
                    if ($action === 'add') {
                        $stmt = $db->query(
                            "INSERT INTO links (title, url, description, category_id, icon, is_featured, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)",
                            [$title, $url, $description, $category_id ?: null, $icon, $is_featured, $is_active]
                        );
                        $success = 'Link added successfully!';
                    } else {
                        $id = (int)($_POST['id'] ?? 0);
                        $stmt = $db->query(
                            "UPDATE links SET title = ?, url = ?, description = ?, category_id = ?, icon = ?, is_featured = ?, is_active = ? WHERE id = ?",
                            [$title, $url, $description, $category_id ?: null, $icon, $is_featured, $is_active, $id]
                        );
                        $success = 'Link updated successfully!';
                    }
                } catch (Exception $e) {
                    error_log("Links management error: " . $e->getMessage());
                    $error = 'An error occurred while saving the link.';
                }
            }
        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            try {
                $stmt = $db->query("DELETE FROM links WHERE id = ?", [$id]);
                $success = 'Link deleted successfully!';
            } catch (Exception $e) {
                error_log("Link deletion error: " . $e->getMessage());
                $error = 'An error occurred while deleting the link.';
            }
        }
    }
}

// Get links
try {
    $links = $db->query("
        SELECT l.*, cat.name as category_name 
        FROM links l 
        LEFT JOIN categories cat ON l.category_id = cat.id 
        ORDER BY l.created_at DESC
    ")->fetchAll();
    
    $categories = $db->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name")->fetchAll();
} catch (Exception $e) {
    error_log("Links fetch error: " . $e->getMessage());
    $links = [];
    $categories = [];
}

// Handle edit mode
$editLink = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $editLink = $db->query("SELECT * FROM links WHERE id = ?", [$id])->fetch();
    } catch (Exception $e) {
        error_log("Link fetch error: " . $e->getMessage());
    }
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

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Add/Edit Form -->
    <div class="lg:col-span-1">
        <div class="bg-white border border-zinc-200 rounded-xl p-5">
            <h3 class="text-[15px] font-semibold text-zinc-900 mb-4">
                <?php echo $editLink ? 'Edit Link' : 'Add Link'; ?>
            </h3>
            
            <form method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="action" value="<?php echo $editLink ? 'edit' : 'add'; ?>">
                <?php if ($editLink): ?>
                <input type="hidden" name="id" value="<?php echo $editLink['id']; ?>">
                <?php endif; ?>
                
                <div>
                    <label for="title" class="block text-[13px] font-medium text-zinc-700 mb-1.5">Title *</label>
                    <input type="text" id="title" name="title" 
                           value="<?php echo escape($editLink['title'] ?? ''); ?>"
                           class="w-full px-3 py-2 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           required>
                </div>
                
                <div>
                    <label for="url" class="block text-[13px] font-medium text-zinc-700 mb-1.5">URL *</label>
                    <input type="url" id="url" name="url" 
                           value="<?php echo escape($editLink['url'] ?? ''); ?>"
                           class="w-full px-3 py-2 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           required>
                </div>
                
                <div>
                    <label for="description" class="block text-[13px] font-medium text-zinc-700 mb-1.5">Description</label>
                    <textarea id="description" name="description" rows="3"
                              class="w-full px-3 py-2 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"><?php echo escape($editLink['description'] ?? ''); ?></textarea>
                </div>
                
                <div>
                    <label for="category_id" class="block text-[13px] font-medium text-zinc-700 mb-1.5">Category</label>
                    <select id="category_id" name="category_id"
                            class="w-full px-3 py-2 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">No Category</option>
                        <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>"
                                <?php echo ($editLink['category_id'] ?? '') == $category['id'] ? 'selected' : ''; ?>>
                            <?php echo escape($category['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label for="icon" class="block text-[13px] font-medium text-zinc-700 mb-1.5">Icon</label>
                    <div class="relative">
                        <input type="text" id="icon" name="icon" 
                               value="<?php echo escape($editLink['icon'] ?? ''); ?>"
                               placeholder="Search for an icon..."
                               class="w-full px-3 py-2 pr-10 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               autocomplete="off"
                               onkeyup="searchIcons()"
                               onfocus="showIconDropdown()">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <i id="selectedIcon" class="<?php echo escape($editLink['icon'] ?? 'fas fa-search'); ?> text-zinc-400"></i>
                        </div>
                        
                        <!-- Icon Dropdown -->
                        <div id="iconDropdown" class="absolute z-50 w-full mt-1 bg-white border border-zinc-200 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                            <div class="p-2 border-b border-zinc-100">
                                <input type="text" id="iconSearch" placeholder="Search icons..." 
                                       class="w-full px-2 py-1.5 text-sm border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       onkeyup="filterIcons()">
                            </div>
                            <div id="iconList" class="p-2 grid grid-cols-4 gap-1.5">
                            </div>
                        </div>
                    </div>
                    <p class="text-[11px] text-zinc-400 mt-1">Select or type a Font Awesome class</p>
                </div>
                
                <div class="space-y-2">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="is_featured" value="1"
                               <?php echo ($editLink['is_featured'] ?? 0) ? 'checked' : ''; ?>
                               class="w-4 h-4 rounded border-zinc-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-[13px] text-zinc-700 ml-2">Featured</span>
                    </label>
                    
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" value="1"
                               <?php echo ($editLink['is_active'] ?? 1) ? 'checked' : ''; ?>
                               class="w-4 h-4 rounded border-zinc-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-[13px] text-zinc-700 ml-2">Active</span>
                    </label>
                </div>
                
                <div class="flex gap-3 pt-1">
                    <button type="submit" 
                            class="bg-zinc-900 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-zinc-700 transition-colors">
                        <?php echo $editLink ? 'Update' : 'Add'; ?> Link
                    </button>
                    
                    <?php if ($editLink): ?>
                    <a href="links.php" 
                       class="bg-zinc-100 text-zinc-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-zinc-200 transition-colors">
                        Cancel
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Links List -->
    <div class="lg:col-span-2">
        <div class="bg-white border border-zinc-200 rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-zinc-100 flex items-center justify-between">
                <h3 class="text-[13px] font-semibold text-zinc-900">All Links</h3>
                <div class="relative">
                    <svg class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" id="searchLinks" placeholder="Search..." 
                           class="pl-9 pr-4 py-1.5 border border-zinc-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-44"
                           onkeyup="filterLinks()">
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-zinc-100">
                            <th class="px-5 py-3 text-left text-[11px] font-medium uppercase tracking-wider text-zinc-500">Title</th>
                            <th class="px-5 py-3 text-left text-[11px] font-medium uppercase tracking-wider text-zinc-500">Category</th>
                            <th class="px-5 py-3 text-left text-[11px] font-medium uppercase tracking-wider text-zinc-500">Status</th>
                            <th class="px-5 py-3 text-left text-[11px] font-medium uppercase tracking-wider text-zinc-500">Clicks</th>
                            <th class="px-5 py-3 text-left text-[11px] font-medium uppercase tracking-wider text-zinc-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        <?php if (!empty($links)): ?>
                        <?php foreach ($links as $link): ?>
                        <tr class="hover:bg-zinc-50 transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center">
                                    <?php if ($link['icon']): ?>
                                    <i class="<?php echo escape($link['icon']); ?> text-blue-500 mr-2.5 text-sm"></i>
                                    <?php endif; ?>
                                    <div class="min-w-0">
                                        <div class="text-[13px] font-medium text-zinc-900 truncate">
                                            <?php echo escape($link['title']); ?>
                                            <?php if ($link['is_featured']): ?>
                                            <span class="ml-1.5 inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-medium bg-amber-50 text-amber-700">Featured</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-[12px] text-zinc-400 truncate max-w-[250px]"><?php echo escape($link['url']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <?php if ($link['category_name']): ?>
                                <span class="text-[13px] text-zinc-600"><?php echo escape($link['category_name']); ?></span>
                                <?php else: ?>
                                <span class="text-[13px] text-zinc-300">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium <?php echo $link['is_active'] ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600'; ?>">
                                    <?php echo $link['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap text-[13px] text-zinc-500">
                                <?php echo number_format($link['click_count']); ?>
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap text-[13px] font-medium space-x-3">
                                <a href="links.php?action=edit&id=<?php echo $link['id']; ?>" 
                                   class="text-blue-600 hover:text-blue-700">Edit</a>
                                
                                <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this link?')">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $link['id']; ?>">
                                    <button type="submit" class="text-red-500 hover:text-red-700">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <tr id="noLinksRow">
                            <td colspan="5" class="px-5 py-10 text-center text-zinc-400 text-sm">
                                No links yet. <a href="?action=add" class="text-blue-600 hover:text-blue-700">Add one</a>
                            </td>
                        </tr>
                        <tr id="noSearchResults" style="display: none;">
                            <td colspan="5" class="px-5 py-10 text-center text-zinc-400 text-sm">
                                No results found.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function filterLinks() {
    const searchInput = document.getElementById('searchLinks');
    const filter = searchInput.value.toLowerCase();
    const table = document.querySelector('tbody');
    const rows = table.querySelectorAll('tr');
    let visibleRows = 0;
    
    rows.forEach(row => {
        if (row.id === 'noLinksRow' || row.id === 'noSearchResults') return;
        
        const title = row.querySelector('.text-gray-900')?.textContent.toLowerCase() || '';
        const url = row.querySelector('.text-gray-500')?.textContent.toLowerCase() || '';
        const category = row.querySelectorAll('td')[1]?.textContent.toLowerCase() || '';
        
        const isVisible = title.includes(filter) || url.includes(filter) || category.includes(filter);
        
        row.style.display = isVisible ? '' : 'none';
        if (isVisible) visibleRows++;
    });
    
    // Show/hide "no results" message
    const noResults = document.getElementById('noSearchResults');
    const noLinks = document.getElementById('noLinksRow');
    
    if (visibleRows === 0 && filter !== '') {
        noResults.style.display = '';
        if (noLinks) noLinks.style.display = 'none';
    } else {
        noResults.style.display = 'none';
        if (noLinks && <?php echo empty($links) ? 'true' : 'false'; ?> && filter === '') {
            noLinks.style.display = '';
        }
    }
}
</script>

<script>
// Common Font Awesome icons for church/organization use
const commonIcons = [
    { class: 'fas fa-home', name: 'Home' },
    { class: 'fas fa-church', name: 'Church' },
    { class: 'fas fa-cross', name: 'Cross' },
    { class: 'fas fa-pray', name: 'Pray' },
    { class: 'fas fa-bible', name: 'Bible' },
    { class: 'fas fa-heart', name: 'Heart' },
    { class: 'fas fa-users', name: 'Users' },
    { class: 'fas fa-user', name: 'User' },
    { class: 'fas fa-phone', name: 'Phone' },
    { class: 'fas fa-envelope', name: 'Email' },
    { class: 'fas fa-map-marker-alt', name: 'Location' },
    { class: 'fas fa-calendar', name: 'Calendar' },
    { class: 'fas fa-clock', name: 'Clock' },
    { class: 'fas fa-external-link-alt', name: 'External Link' },
    { class: 'fas fa-link', name: 'Link' },
    { class: 'fas fa-info-circle', name: 'Info' },
    { class: 'fas fa-question-circle', name: 'Question' },
    { class: 'fas fa-exclamation-circle', name: 'Alert' },
    { class: 'fas fa-download', name: 'Download' },
    { class: 'fas fa-upload', name: 'Upload' },
    { class: 'fas fa-file', name: 'File' },
    { class: 'fas fa-file-pdf', name: 'PDF' },
    { class: 'fas fa-image', name: 'Image' },
    { class: 'fas fa-video', name: 'Video' },
    { class: 'fas fa-music', name: 'Music' },
    { class: 'fas fa-microphone', name: 'Microphone' },
    { class: 'fab fa-facebook', name: 'Facebook' },
    { class: 'fab fa-twitter', name: 'Twitter' },
    { class: 'fab fa-instagram', name: 'Instagram' },
    { class: 'fab fa-youtube', name: 'YouTube' },
    { class: 'fab fa-telegram', name: 'Telegram' },
    { class: 'fas fa-wifi', name: 'WiFi' },
    { class: 'fas fa-globe', name: 'Globe' },
    { class: 'fas fa-star', name: 'Star' },
    { class: 'fas fa-thumbs-up', name: 'Like' },
    { class: 'fas fa-share', name: 'Share' },
    { class: 'fas fa-bullhorn', name: 'Announcement' },
    { class: 'fas fa-newspaper', name: 'News' },
    { class: 'fas fa-book', name: 'Book' },
    { class: 'fas fa-graduation-cap', name: 'Education' },
    { class: 'fas fa-hands-helping', name: 'Help' },
    { class: 'fas fa-handshake', name: 'Partnership' },
    { class: 'fas fa-donate', name: 'Donate' },
    { class: 'fas fa-gift', name: 'Gift' },
    { class: 'fas fa-child', name: 'Child' },
    { class: 'fas fa-baby', name: 'Baby' },
    { class: 'fas fa-user-friends', name: 'Friends' }
];

let filteredIcons = [...commonIcons];

function showIconDropdown() {
    document.getElementById('iconDropdown').classList.remove('hidden');
    populateIcons();
}

function hideIconDropdown() {
    setTimeout(() => {
        document.getElementById('iconDropdown').classList.add('hidden');
    }, 200);
}

function populateIcons() {
    const iconList = document.getElementById('iconList');
    iconList.innerHTML = '';
    
    filteredIcons.forEach(icon => {
        const iconDiv = document.createElement('div');
        iconDiv.className = 'flex flex-col items-center p-2 hover:bg-zinc-100 cursor-pointer rounded-lg text-center';
        iconDiv.onclick = () => selectIcon(icon.class, icon.name);
        
        iconDiv.innerHTML = `
            <i class="${icon.class} text-lg mb-1 text-zinc-500"></i>
            <span class="text-[10px] text-zinc-400 truncate w-full">${icon.name}</span>
        `;
        
        iconList.appendChild(iconDiv);
    });
}

function filterIcons() {
    const search = document.getElementById('iconSearch').value.toLowerCase();
    filteredIcons = commonIcons.filter(icon => 
        icon.name.toLowerCase().includes(search) || 
        icon.class.toLowerCase().includes(search)
    );
    populateIcons();
}

function selectIcon(iconClass, iconName) {
    document.getElementById('icon').value = iconClass;
    document.getElementById('selectedIcon').className = iconClass + ' text-gray-600';
    document.getElementById('iconDropdown').classList.add('hidden');
}

function searchIcons() {
    const input = document.getElementById('icon');
    const value = input.value;
    const selectedIcon = document.getElementById('selectedIcon');
    
    if (value.includes('fa-')) {
        selectedIcon.className = value + ' text-gray-600';
    } else {
        selectedIcon.className = 'fas fa-search text-gray-400';
    }
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('iconDropdown');
    const input = document.getElementById('icon');
    
    if (!dropdown.contains(e.target) && e.target !== input) {
        hideIconDropdown();
    }
});

// Initialize icon preview on page load
document.addEventListener('DOMContentLoaded', function() {
    searchIcons();
});
</script>

<?php include 'includes/footer.php'; ?>
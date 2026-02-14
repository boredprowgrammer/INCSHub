<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check authentication
requireAdmin();

$type = sanitizeInput($_GET['type'] ?? 'news');
if (!in_array($type, ['news', 'announcement', 'article'])) {
    $type = 'news';
}

$pageTitle = 'Manage ' . ucfirst($type);
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
            $content = sanitizeInput($_POST['content'] ?? '');
            $excerpt = sanitizeInput($_POST['excerpt'] ?? '');
            $facebook_post_url = sanitizeInput($_POST['facebook_post_url'] ?? '');
            $category_id = (int)($_POST['category_id'] ?? 0);
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            $is_published = isset($_POST['is_published']) ? 1 : 0;
            $slug = sanitizeInput(strtolower(str_replace([' ', '_'], '-', preg_replace('/[^a-zA-Z0-9 _-]/', '', $title))));
            
            // Handle image upload - Store as blob
            $upload_result = null;
            if (isset($_FILES['featured_image'])) {
                $upload_result = handleImageUpload($_FILES['featured_image'], UPLOAD_DIR_CONTENT);
            }
            
            if (empty($title) || empty($content)) {
                $error = 'Title and content are required.';
            } elseif (empty($error)) {
                try {
                    if ($action === 'add') {
                        $featured_image = $upload_result ? $upload_result['image_path'] : '';
                        $stmt = $db->query(
                            "INSERT INTO content (title, slug, content, excerpt, type, category_id, featured_image, featured_image_blob, featured_image_mime_type, featured_image_size, featured_image_filename, facebook_post_url, is_featured, is_published, author_id, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                            [
                                $title, $slug, $content, $excerpt, $type, $category_id ?: null, 
                                $upload_result['image_path'] ?? '', $upload_result['image_data'] ?? null, 
                                $upload_result['mime_type'] ?? null, $upload_result['size'] ?? null, 
                                $upload_result['filename'] ?? null, $facebook_post_url, $is_featured, 
                                $is_published, $_SESSION['admin_id'], $is_published ? date('Y-m-d H:i:s') : null
                            ]
                        );
                        $success = ucfirst($type) . ' added successfully!';
                    } else {
                        $id = (int)($_POST['id'] ?? 0);
                        $updateFields = [
                            $title, $slug, $content, $excerpt, $facebook_post_url, $category_id ?: null, $is_featured, $is_published, $id
                        ];
                        $sql = "UPDATE content SET title = ?, slug = ?, content = ?, excerpt = ?, facebook_post_url = ?, category_id = ?, is_featured = ?, is_published = ?, updated_at = NOW()";
                        
                        if ($upload_result) {
                            $sql .= ", featured_image = ?, featured_image_blob = ?, featured_image_mime_type = ?, featured_image_size = ?, featured_image_filename = ?";
                            array_splice($updateFields, -1, 0, [
                                $upload_result['image_path'], $upload_result['image_data'], 
                                $upload_result['mime_type'], $upload_result['size'], $upload_result['filename']
                            ]);
                        }
                        
                        if ($is_published) {
                            $sql .= ", published_at = COALESCE(published_at, NOW())";
                        }
                        
                        $sql .= " WHERE id = ?";
                        
                        $stmt = $db->query($sql, $updateFields);
                        $success = ucfirst($type) . ' updated successfully!';
                    }
                } catch (Exception $e) {
                    error_log("Content management error: " . $e->getMessage());
                    $error = 'An error occurred while saving the ' . $type . '.';
                }
            }
        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            try {
                // Get the image filename before deletion
                $content = $db->query("SELECT featured_image FROM content WHERE id = ?", [$id])->fetch();
                
                $stmt = $db->query("DELETE FROM content WHERE id = ?", [$id]);
                
                // Delete associated image file
                if ($content && $content['featured_image']) {
                    $imagePath = '../uploads/images/' . $content['featured_image'];
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }
                
                $success = ucfirst($type) . ' deleted successfully!';
            } catch (Exception $e) {
                error_log("Content deletion error: " . $e->getMessage());
                $error = 'An error occurred while deleting the ' . $type . '.';
            }
        }
    }
}

// Get content
try {
    $content = $db->query("
        SELECT c.*, cat.name as category_name, a.full_name as author_name
        FROM content c 
        LEFT JOIN categories cat ON c.category_id = cat.id 
        LEFT JOIN admins a ON c.author_id = a.id
        WHERE c.type = ?
        ORDER BY c.created_at DESC
    ", [$type])->fetchAll();
    
    $categories = $db->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name")->fetchAll();
} catch (Exception $e) {
    error_log("Content fetch error: " . $e->getMessage());
    $content = [];
    $categories = [];
}

// Handle edit mode
$editContent = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $editContent = $db->query("SELECT * FROM content WHERE id = ? AND type = ?", [$id, $type])->fetch();
    } catch (Exception $e) {
        error_log("Content fetch error: " . $e->getMessage());
    }
}

include 'includes/header.php';
?>

<!-- Type Tabs -->
<div class="mb-6">
    <div class="flex space-x-1 bg-zinc-100 rounded-lg p-1 w-fit">
        <a href="content.php?type=news" 
           class="px-4 py-1.5 text-[13px] font-medium rounded-md transition-colors <?php echo $type === 'news' ? 'bg-white text-zinc-900 shadow-sm' : 'text-zinc-500 hover:text-zinc-700'; ?>">
            News
        </a>
        <a href="content.php?type=announcement" 
           class="px-4 py-1.5 text-[13px] font-medium rounded-md transition-colors <?php echo $type === 'announcement' ? 'bg-white text-zinc-900 shadow-sm' : 'text-zinc-500 hover:text-zinc-700'; ?>">
            Announcements
        </a>
        <a href="content.php?type=article" 
           class="px-4 py-1.5 text-[13px] font-medium rounded-md transition-colors <?php echo $type === 'article' ? 'bg-white text-zinc-900 shadow-sm' : 'text-zinc-500 hover:text-zinc-700'; ?>">
            Articles
        </a>
    </div>
</div>

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

<?php if (isset($_GET['action']) && $_GET['action'] === 'add' || $editContent): ?>
<!-- Add/Edit Form -->
<div class="bg-white border border-zinc-200 rounded-xl p-6 mb-8">
    <h3 class="text-[15px] font-semibold text-zinc-900 mb-5">
        <?php echo $editContent ? 'Edit ' . ucfirst($type) : 'Add New ' . ucfirst($type); ?>
    </h3>
    
    <form method="POST" enctype="multipart/form-data" class="space-y-5">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        <input type="hidden" name="action" value="<?php echo $editContent ? 'edit' : 'add'; ?>">
        <?php if ($editContent): ?>
        <input type="hidden" name="id" value="<?php echo $editContent['id']; ?>">
        <?php endif; ?>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label for="title" class="block text-[13px] font-medium text-zinc-700 mb-1.5">Title *</label>
                <input type="text" id="title" name="title" 
                       value="<?php echo escape($editContent['title'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       required>
            </div>
            
            <div>
                <label for="category_id" class="block text-[13px] font-medium text-zinc-700 mb-1.5">Category</label>
                <select id="category_id" name="category_id"
                        class="w-full px-3 py-2 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">No Category</option>
                    <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>"
                            <?php echo ($editContent['category_id'] ?? '') == $category['id'] ? 'selected' : ''; ?>>
                        <?php echo escape($category['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div>
            <label for="excerpt" class="block text-[13px] font-medium text-zinc-700 mb-1.5">Excerpt</label>
            <textarea id="excerpt" name="excerpt" rows="2"
                      class="w-full px-3 py-2 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      placeholder="Brief summary (optional)"><?php echo escape($editContent['excerpt'] ?? ''); ?></textarea>
        </div>
        
        <div>
            <label for="content" class="block text-[13px] font-medium text-zinc-700 mb-1.5">Content *</label>
            <textarea id="content" name="content" rows="12"
                      class="w-full px-3 py-2 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      required><?php echo escape($editContent['content'] ?? ''); ?></textarea>
        </div>
        
        <div>
            <label for="featured_image" class="block text-[13px] font-medium text-zinc-700 mb-1.5">Featured Image</label>
            <input type="file" id="featured_image" name="featured_image" 
                   accept="image/*"
                   data-preview="image-preview"
                   class="w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200 transition-colors">
            <p class="text-[11px] text-zinc-400 mt-1">JPEG, PNG, GIF, or WebP. Max 5MB.</p>
            
            <?php if ($editContent && $editContent['featured_image']): ?>
            <div class="mt-2">
                <img src="../uploads/images/<?php echo escape($editContent['featured_image']); ?>" 
                     alt="Current featured image" 
                     class="h-24 object-cover rounded-lg border border-zinc-200">
            </div>
            <?php endif; ?>
            
            <img id="image-preview" src="" alt="Preview" class="hidden mt-2 h-24 object-cover rounded-lg border border-zinc-200">
        </div>
        
        <div>
            <label for="facebook_post_url" class="block text-[13px] font-medium text-zinc-700 mb-1.5">Facebook Post URL</label>
            <input type="url" id="facebook_post_url" name="facebook_post_url" 
                   value="<?php echo escape($editContent['facebook_post_url'] ?? ''); ?>"
                   class="w-full px-3 py-2 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                   placeholder="https://www.facebook.com/yourpage/posts/...">
            <p class="text-[11px] text-zinc-400 mt-1">Optional: Embed a Facebook post in this article</p>
        </div>
        
        <div class="flex flex-wrap gap-5">
            <label class="flex items-center cursor-pointer">
                <input type="checkbox" name="is_featured" value="1"
                       <?php echo ($editContent['is_featured'] ?? 0) ? 'checked' : ''; ?>
                       class="w-4 h-4 rounded border-zinc-300 text-blue-600 focus:ring-blue-500">
                <span class="text-[13px] text-zinc-700 ml-2">Featured</span>
            </label>
            
            <label class="flex items-center cursor-pointer">
                <input type="checkbox" name="is_published" value="1"
                       <?php echo ($editContent['is_published'] ?? 1) ? 'checked' : ''; ?>
                       class="w-4 h-4 rounded border-zinc-300 text-blue-600 focus:ring-blue-500">
                <span class="text-[13px] text-zinc-700 ml-2">Published</span>
            </label>
        </div>
        
        <div class="flex gap-3 pt-2">
            <button type="submit" 
                    class="bg-zinc-900 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-zinc-700 transition-colors">
                <?php echo $editContent ? 'Update' : 'Create'; ?> <?php echo ucfirst($type); ?>
            </button>
            
            <a href="content.php?type=<?php echo $type; ?>" 
               class="bg-zinc-100 text-zinc-700 px-5 py-2 rounded-lg text-sm font-medium hover:bg-zinc-200 transition-colors">
                Cancel
            </a>
        </div>
    </form>
</div>
<?php else: ?>
<!-- Add New Button -->
<div class="mb-6">
    <a href="content.php?type=<?php echo $type; ?>&action=add" 
       class="inline-flex items-center bg-zinc-900 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-zinc-700 transition-colors">
        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/></svg>
        Add <?php echo ucfirst($type); ?>
    </a>
</div>
<?php endif; ?>

<!-- Content List -->
<div class="bg-white border border-zinc-200 rounded-xl overflow-hidden">
    <div class="px-5 py-4 border-b border-zinc-100 flex items-center justify-between">
        <h3 class="text-[13px] font-semibold text-zinc-900">All <?php echo ucfirst($type); ?></h3>
        <div class="relative">
            <svg class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" id="searchContent" placeholder="Search..." 
                   class="pl-9 pr-4 py-1.5 border border-zinc-200 rounded-lg text-[13px] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-48"
                   onkeyup="filterContent()">
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-zinc-100">
                    <th class="px-5 py-3 text-left text-[11px] font-medium uppercase tracking-wider text-zinc-500">Title</th>
                    <th class="px-5 py-3 text-left text-[11px] font-medium uppercase tracking-wider text-zinc-500">Category</th>
                    <th class="px-5 py-3 text-left text-[11px] font-medium uppercase tracking-wider text-zinc-500">Status</th>
                    <th class="px-5 py-3 text-left text-[11px] font-medium uppercase tracking-wider text-zinc-500">Date</th>
                    <th class="px-5 py-3 text-left text-[11px] font-medium uppercase tracking-wider text-zinc-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100">
                <?php if (!empty($content)): ?>
                <?php foreach ($content as $item): ?>
                <tr class="hover:bg-zinc-50 transition-colors">
                    <td class="px-5 py-3.5">
                        <div class="flex items-start">
                            <?php if ($item['featured_image']): ?>
                            <img src="../uploads/images/<?php echo escape($item['featured_image']); ?>" 
                                 alt="" class="w-10 h-10 object-cover rounded-lg mr-3 flex-shrink-0">
                            <?php endif; ?>
                            <div class="min-w-0">
                                <div class="text-[13px] font-medium text-zinc-900 truncate">
                                    <?php echo escape($item['title']); ?>
                                    <?php if ($item['is_featured']): ?>
                                    <span class="ml-1.5 inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-medium bg-amber-50 text-amber-700">Featured</span>
                                    <?php endif; ?>
                                </div>
                                <div class="text-[12px] text-zinc-400">
                                    <?php echo escape($item['author_name'] ?? 'Unknown'); ?>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 whitespace-nowrap">
                        <?php if ($item['category_name']): ?>
                        <span class="text-[13px] text-zinc-600"><?php echo escape($item['category_name']); ?></span>
                        <?php else: ?>
                        <span class="text-[13px] text-zinc-300">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-3.5 whitespace-nowrap">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium <?php echo $item['is_published'] ? 'bg-emerald-50 text-emerald-700' : 'bg-zinc-100 text-zinc-500'; ?>">
                            <?php echo $item['is_published'] ? 'Published' : 'Draft'; ?>
                        </span>
                    </td>
                    <td class="px-5 py-3.5 whitespace-nowrap text-[13px] text-zinc-500">
                        <?php echo date('M j, Y', strtotime($item['created_at'])); ?>
                    </td>
                    <td class="px-5 py-3.5 whitespace-nowrap text-[13px] font-medium space-x-3">
                        <a href="content.php?type=<?php echo $type; ?>&action=edit&id=<?php echo $item['id']; ?>" 
                           class="text-blue-600 hover:text-blue-700">Edit</a>
                        
                        <form method="POST" class="inline" 
                              data-confirm="Are you sure you want to delete this <?php echo $type; ?>?">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                            <button type="submit" class="text-red-500 hover:text-red-700">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr id="noContentRow">
                    <td colspan="5" class="px-5 py-10 text-center text-zinc-400 text-sm">
                        No <?php echo $type; ?> yet. <a href="content.php?type=<?php echo $type; ?>&action=add" class="text-blue-600 hover:text-blue-700">Create your first one</a>.
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

<script>
function filterContent() {
    const searchInput = document.getElementById('searchContent');
    const filter = searchInput.value.toLowerCase();
    const table = document.querySelector('tbody');
    const rows = table.querySelectorAll('tr');
    let visibleRows = 0;
    
    rows.forEach(row => {
        if (row.id === 'noContentRow' || row.id === 'noSearchResults') return;
        
        const title = row.querySelector('.font-medium')?.textContent.toLowerCase() || '';
        const category = row.querySelectorAll('td')[1]?.textContent.toLowerCase() || '';
        
        const isVisible = title.includes(filter) || category.includes(filter);
        
        row.style.display = isVisible ? '' : 'none';
        if (isVisible) visibleRows++;
    });
    
    const noResults = document.getElementById('noSearchResults');
    const noContent = document.getElementById('noContentRow');
    
    if (visibleRows === 0 && filter !== '') {
        noResults.style.display = '';
        if (noContent) noContent.style.display = 'none';
    } else {
        noResults.style.display = 'none';
        if (noContent && <?php echo empty($content) ? 'true' : 'false'; ?> && filter === '') {
            noContent.style.display = '';
        }
    }
}
</script>

<?php include 'includes/footer.php'; ?>
<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: /admin/login.php');
    exit;
}

$pageTitle = 'Featured Images Management';
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!verifyCSRFToken($_POST['csrf_token'])) {
            throw new Exception('Invalid security token');
        }

        $action = $_POST['action'];
        
        if ($action === 'add' || $action === 'edit') {
            $title = trim($_POST['title']);
            $description = trim($_POST['description']);
            $link_url = trim($_POST['link_url']);
            $display_order = (int) $_POST['display_order'];
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            // Validate inputs
            if (empty($title)) {
                throw new Exception('Title is required');
            }
            
            // Handle image upload - Store as blob
            $upload_result = null;
            if (isset($_FILES['image'])) {
                $upload_result = handleImageUpload($_FILES['image'], UPLOAD_DIR_FEATURED);
            }
            
            if ($action === 'add') {
                if (empty($upload_result)) {
                    throw new Exception('Image is required');
                }
                
                $db->query("
                    INSERT INTO featured_images (title, description, image_path, image_blob, image_mime_type, image_size, original_filename, link_url, display_order, is_active) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ", [
                    $title, $description, $upload_result['image_path'], $upload_result['image_data'], 
                    $upload_result['mime_type'], $upload_result['size'], $upload_result['filename'], 
                    $link_url, $display_order, $is_active
                ]);
                $message = 'Featured image added successfully!';
                
            } else if ($action === 'edit') {
                $id = (int) $_POST['id'];
                
                if (!empty($upload_result)) {
                    // Delete old image file if it exists
                    $oldImage = $db->query("SELECT image_path FROM featured_images WHERE id = ?", [$id])->fetchColumn();
                    if ($oldImage && file_exists(__DIR__ . '/..' . $oldImage)) {
                        unlink(__DIR__ . '/..' . $oldImage);
                    }
                    
                    $db->query("
                        UPDATE featured_images 
                        SET title = ?, description = ?, image_path = ?, image_blob = ?, image_mime_type = ?, image_size = ?, original_filename = ?, link_url = ?, display_order = ?, is_active = ? 
                        WHERE id = ?
                    ", [
                        $title, $description, $upload_result['image_path'], $upload_result['image_data'], 
                        $upload_result['mime_type'], $upload_result['size'], $upload_result['filename'], 
                        $link_url, $display_order, $is_active, $id
                    ]);
                } else {
                    $db->query("
                        UPDATE featured_images 
                        SET title = ?, description = ?, link_url = ?, display_order = ?, is_active = ? 
                        WHERE id = ?
                    ", [$title, $description, $link_url, $display_order, $is_active, $id]);
                }
                $message = 'Featured image updated successfully!';
            }
        } else if ($action === 'delete') {
            $id = (int) $_POST['id'];
            
            // Delete the image file
            $image = $db->query("SELECT image_path FROM featured_images WHERE id = ?", [$id])->fetchColumn();
            if ($image && file_exists(__DIR__ . '/..' . $image)) {
                unlink(__DIR__ . '/..' . $image);
            }
            
            $db->query("DELETE FROM featured_images WHERE id = ?", [$id]);
            $message = 'Featured image deleted successfully!';
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get featured images with search functionality
$search = $_GET['search'] ?? '';

try {
    if ($search) {
        $searchTerm = "%$search%";
        $featuredImages = $db->query(
            "SELECT * FROM featured_images WHERE title LIKE ? OR description LIKE ? ORDER BY display_order ASC, created_at DESC", 
            [$searchTerm, $searchTerm]
        )->fetchAll();
    } else {
        $featuredImages = $db->query(
            "SELECT * FROM featured_images ORDER BY display_order ASC, created_at DESC"
        )->fetchAll();
    }
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    $featuredImages = [];
}

// Get image being edited
$editImage = null;
if (isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    $editImage = $db->query("SELECT * FROM featured_images WHERE id = ?", [$editId])->fetch();
}

include __DIR__ . '/includes/header.php';
?>

<div class="space-y-6">
    <div>
        <h1 class="text-[15px] font-semibold text-zinc-900">Featured Images</h1>
        <p class="text-[12px] text-zinc-400 mt-0.5">Manage images displayed on the home page</p>
    </div>

    <?php if ($message): ?>
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg text-[13px] alert-auto-hide">
        <?php echo escape($message); ?>
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-[13px]">
        <?php echo escape($error); ?>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Add/Edit Form -->
        <div class="lg:col-span-1">
            <div class="bg-white border border-zinc-200 rounded-xl p-6">
                <h2 class="text-[14px] font-semibold text-zinc-900 mb-5">
                    <?php echo $editImage ? 'Edit Image' : 'Add Image'; ?>
                </h2>
                
                <form method="POST" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="<?php echo $editImage ? 'edit' : 'add'; ?>">
                    <?php if ($editImage): ?>
                    <input type="hidden" name="id" value="<?php echo $editImage['id']; ?>">
                    <?php endif; ?>
                    
                    <div>
                        <label for="title" class="block text-[13px] font-medium text-zinc-700 mb-1.5">Title *</label>
                        <input type="text" id="title" name="title" required 
                               value="<?php echo escape($editImage['title'] ?? ''); ?>"
                               class="w-full border border-zinc-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label for="description" class="block text-[13px] font-medium text-zinc-700 mb-1.5">Description</label>
                        <textarea id="description" name="description" rows="3"
                                  class="w-full border border-zinc-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"><?php echo escape($editImage['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div>
                        <label for="image" class="block text-[13px] font-medium text-zinc-700 mb-1.5">
                            Image <?php echo !$editImage ? '*' : '(leave empty to keep current)'; ?>
                        </label>
                        <input type="file" id="image" name="image" accept="image/*" 
                               <?php echo !$editImage ? 'required' : ''; ?>
                               class="w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-zinc-100 file:text-zinc-700 file:text-sm file:font-medium hover:file:bg-zinc-200 file:cursor-pointer">
                        <?php if ($editImage && $editImage['image_path']): ?>
                        <div class="mt-2">
                            <img src="<?php echo escape($editImage['image_path']); ?>" 
                                 alt="Current image" class="w-20 h-20 object-cover rounded-lg border border-zinc-200">
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <label for="link_url" class="block text-[13px] font-medium text-zinc-700 mb-1.5">Link URL</label>
                        <input type="url" id="link_url" name="link_url" 
                               value="<?php echo escape($editImage['link_url'] ?? ''); ?>"
                               placeholder="https://example.com"
                               class="w-full border border-zinc-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label for="display_order" class="block text-[13px] font-medium text-zinc-700 mb-1.5">Display Order</label>
                        <input type="number" id="display_order" name="display_order" 
                               value="<?php echo escape($editImage['display_order'] ?? 0); ?>" min="0"
                               class="w-full border border-zinc-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" 
                                   <?php echo (!$editImage || $editImage['is_active']) ? 'checked' : ''; ?>
                                   class="w-4 h-4 rounded border-zinc-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-[13px] text-zinc-700 ml-2">Active</span>
                        </label>
                    </div>
                    
                    <div class="flex gap-3 pt-2">
                        <button type="submit" 
                                class="flex-1 bg-zinc-900 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-zinc-700 transition-colors">
                            <?php echo $editImage ? 'Update' : 'Add'; ?> Image
                        </button>
                        
                        <?php if ($editImage): ?>
                        <a href="/admin/featured-images.php" 
                           class="bg-zinc-100 text-zinc-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-zinc-200 transition-colors">
                            Cancel
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Featured Images List -->
        <div class="lg:col-span-2">
            <div class="bg-white border border-zinc-200 rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-zinc-100">
                    <form method="GET">
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input type="text" name="search" value="<?php echo escape($search); ?>" 
                                   placeholder="Search images..." 
                                   class="w-full pl-10 pr-4 py-2 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </form>
                </div>
                
                <div class="overflow-x-auto">
                    <?php if (!empty($featuredImages)): ?>
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-zinc-100">
                                <th class="px-5 py-3 text-left text-[11px] font-medium uppercase tracking-wider text-zinc-500">Image</th>
                                <th class="px-5 py-3 text-left text-[11px] font-medium uppercase tracking-wider text-zinc-500">Title</th>
                                <th class="px-5 py-3 text-left text-[11px] font-medium uppercase tracking-wider text-zinc-500">Order</th>
                                <th class="px-5 py-3 text-left text-[11px] font-medium uppercase tracking-wider text-zinc-500">Status</th>
                                <th class="px-5 py-3 text-left text-[11px] font-medium uppercase tracking-wider text-zinc-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            <?php foreach ($featuredImages as $image): ?>
                            <tr class="hover:bg-zinc-50 transition-colors">
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <img src="<?php echo escape($image['image_path']); ?>" 
                                         alt="<?php echo escape($image['title']); ?>"
                                         class="w-14 h-14 object-cover rounded-lg border border-zinc-200">
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="text-[13px] font-medium text-zinc-900"><?php echo escape($image['title']); ?></div>
                                    <?php if ($image['description']): ?>
                                    <div class="text-[12px] text-zinc-400 mt-0.5 truncate max-w-[250px]"><?php echo escape(substr($image['description'], 0, 100)) . (strlen($image['description']) > 100 ? '...' : ''); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap text-[13px] text-zinc-500">
                                    <?php echo escape($image['display_order']); ?>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium <?php echo $image['is_active'] ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600'; ?>">
                                        <?php echo $image['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap text-[13px] font-medium space-x-3">
                                    <a href="/admin/featured-images.php?edit=<?php echo $image['id']; ?>" 
                                       class="text-blue-600 hover:text-blue-700">Edit</a>
                                    <form method="POST" class="inline" 
                                          onsubmit="return confirm('Are you sure you want to delete this image?')">
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $image['id']; ?>">
                                        <button type="submit" class="text-red-500 hover:text-red-700">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="py-16 text-center">
                        <svg class="w-10 h-10 mx-auto mb-3 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <p class="text-zinc-400 text-sm">No featured images found.</p>
                        <?php if ($search): ?>
                        <a href="/admin/featured-images.php" class="text-blue-600 hover:text-blue-700 text-sm mt-1 inline-block">Clear search</a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
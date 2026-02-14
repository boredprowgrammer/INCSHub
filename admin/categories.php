<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check authentication
requireAdmin();

$pageTitle = 'Manage Categories';
$success = '';
$error = '';

// Handle form submissions
if ($_POST) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'add' || $action === 'edit') {
            $name = sanitizeInput($_POST['name'] ?? '');
            $slug = sanitizeInput(strtolower(str_replace([' ', '_'], '-', preg_replace('/[^a-zA-Z0-9 _-]/', '', $_POST['name'] ?? ''))));
            $description = sanitizeInput($_POST['description'] ?? '');
            $color = sanitizeInput($_POST['color'] ?? '#3B82F6');
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            if (empty($name)) {
                $error = 'Category name is required.';
            } else {
                try {
                    if ($action === 'add') {
                        $stmt = $db->query(
                            "INSERT INTO categories (name, slug, description, color, is_active) VALUES (?, ?, ?, ?, ?)",
                            [$name, $slug, $description, $color, $is_active]
                        );
                        $success = 'Category added successfully!';
                    } else {
                        $id = (int)($_POST['id'] ?? 0);
                        $stmt = $db->query(
                            "UPDATE categories SET name = ?, slug = ?, description = ?, color = ?, is_active = ? WHERE id = ?",
                            [$name, $slug, $description, $color, $is_active, $id]
                        );
                        $success = 'Category updated successfully!';
                    }
                } catch (Exception $e) {
                    error_log("Category management error: " . $e->getMessage());
                    $error = 'An error occurred while saving the category.';
                }
            }
        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            try {
                // Check if category is in use
                $inUse = $db->query("SELECT COUNT(*) as count FROM content WHERE category_id = ?", [$id])->fetch();
                $linksInUse = $db->query("SELECT COUNT(*) as count FROM links WHERE category_id = ?", [$id])->fetch();
                
                if ($inUse['count'] > 0 || $linksInUse['count'] > 0) {
                    $error = 'Cannot delete category as it is being used by content or links.';
                } else {
                    $stmt = $db->query("DELETE FROM categories WHERE id = ?", [$id]);
                    $success = 'Category deleted successfully!';
                }
            } catch (Exception $e) {
                error_log("Category deletion error: " . $e->getMessage());
                $error = 'An error occurred while deleting the category.';
            }
        }
    }
}

// Get categories
try {
    $categories = $db->query("
        SELECT c.*, 
               (SELECT COUNT(*) FROM content WHERE category_id = c.id) as content_count,
               (SELECT COUNT(*) FROM links WHERE category_id = c.id) as links_count
        FROM categories c 
        ORDER BY c.name ASC
    ")->fetchAll();
} catch (Exception $e) {
    error_log("Categories fetch error: " . $e->getMessage());
    $categories = [];
}

// Handle edit mode
$editCategory = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $editCategory = $db->query("SELECT * FROM categories WHERE id = ?", [$id])->fetch();
    } catch (Exception $e) {
        error_log("Category fetch error: " . $e->getMessage());
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
                <?php echo $editCategory ? 'Edit Category' : 'Add Category'; ?>
            </h3>
            
            <form method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="action" value="<?php echo $editCategory ? 'edit' : 'add'; ?>">
                <?php if ($editCategory): ?>
                <input type="hidden" name="id" value="<?php echo $editCategory['id']; ?>">
                <?php endif; ?>
                
                <div>
                    <label for="name" class="block text-[13px] font-medium text-zinc-700 mb-1.5">Name *</label>
                    <input type="text" id="name" name="name" 
                           value="<?php echo escape($editCategory['name'] ?? ''); ?>"
                           class="w-full px-3 py-2 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           required>
                </div>
                
                <div>
                    <label for="description" class="block text-[13px] font-medium text-zinc-700 mb-1.5">Description</label>
                    <textarea id="description" name="description" rows="3"
                              class="w-full px-3 py-2 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"><?php echo escape($editCategory['description'] ?? ''); ?></textarea>
                </div>
                
                <div>
                    <label for="color" class="block text-[13px] font-medium text-zinc-700 mb-1.5">Color</label>
                    <div class="flex items-center space-x-3">
                        <input type="color" id="color" name="color" 
                               value="<?php echo escape($editCategory['color'] ?? '#3B82F6'); ?>"
                               class="h-9 w-14 border border-zinc-200 rounded-lg cursor-pointer">
                        <span class="text-[12px] text-zinc-400">Visual identifier</span>
                    </div>
                </div>
                
                <div>
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" value="1"
                               <?php echo ($editCategory['is_active'] ?? 1) ? 'checked' : ''; ?>
                               class="w-4 h-4 rounded border-zinc-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-[13px] text-zinc-700 ml-2">Active</span>
                    </label>
                </div>
                
                <div class="flex gap-3 pt-1">
                    <button type="submit" 
                            class="bg-zinc-900 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-zinc-700 transition-colors">
                        <?php echo $editCategory ? 'Update' : 'Add'; ?> Category
                    </button>
                    
                    <?php if ($editCategory): ?>
                    <a href="categories.php" 
                       class="bg-zinc-100 text-zinc-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-zinc-200 transition-colors">
                        Cancel
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Categories List -->
    <div class="lg:col-span-2">
        <div class="bg-white border border-zinc-200 rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-zinc-100">
                <h3 class="text-[13px] font-semibold text-zinc-900">All Categories</h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-zinc-100">
                            <th class="px-5 py-3 text-left text-[11px] font-medium uppercase tracking-wider text-zinc-500">Category</th>
                            <th class="px-5 py-3 text-left text-[11px] font-medium uppercase tracking-wider text-zinc-500">Usage</th>
                            <th class="px-5 py-3 text-left text-[11px] font-medium uppercase tracking-wider text-zinc-500">Status</th>
                            <th class="px-5 py-3 text-left text-[11px] font-medium uppercase tracking-wider text-zinc-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                        <tr class="hover:bg-zinc-50 transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 rounded-full mr-3 flex-shrink-0" 
                                         style="background-color: <?php echo escape($category['color']); ?>"></div>
                                    <div>
                                        <div class="text-[13px] font-medium text-zinc-900"><?php echo escape($category['name']); ?></div>
                                        <?php if ($category['description']): ?>
                                        <div class="text-[12px] text-zinc-400"><?php echo escape($category['description']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <div class="text-[13px] text-zinc-600"><?php echo number_format($category['content_count']); ?> content</div>
                                <div class="text-[12px] text-zinc-400"><?php echo number_format($category['links_count']); ?> links</div>
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium <?php echo $category['is_active'] ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600'; ?>">
                                    <?php echo $category['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap text-[13px] font-medium space-x-3">
                                <a href="categories.php?action=edit&id=<?php echo $category['id']; ?>" 
                                   class="text-blue-600 hover:text-blue-700">Edit</a>
                                
                                <?php if ($category['content_count'] == 0 && $category['links_count'] == 0): ?>
                                <form method="POST" class="inline" 
                                      data-confirm="Are you sure you want to delete this category?">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                                    <button type="submit" class="text-red-500 hover:text-red-700">Delete</button>
                                </form>
                                <?php else: ?>
                                <span class="text-zinc-300 cursor-not-allowed" title="In use">Delete</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="4" class="px-5 py-10 text-center text-zinc-400 text-sm">
                                No categories yet. Add one using the form.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
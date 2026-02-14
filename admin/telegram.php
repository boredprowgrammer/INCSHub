<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

try {
    $db = new Database();
} catch (Exception $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $action = $_POST['action'];
        
        switch ($action) {
            case 'add':
                $name = sanitizeInput($_POST['name']);
                $position = sanitizeInput($_POST['position']);
                $telegram_username = sanitizeInput($_POST['telegram_username']);
                $description = sanitizeInput($_POST['description']);
                $icon_color = sanitizeInput($_POST['icon_color']);
                $display_order = (int)$_POST['display_order'];
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                
                if (!empty($name) && !empty($position) && !empty($telegram_username)) {
                    try {
                        $stmt = $db->query("INSERT INTO telegram_officers (name, position, telegram_username, description, icon_color, display_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)", [$name, $position, $telegram_username, $description, $icon_color, $display_order, $is_active]);
                        $success = 'Telegram officer added successfully!';
                    } catch (PDOException $e) {
                        $error = 'Error adding officer: ' . $e->getMessage();
                    }
                } else {
                    $error = 'Please fill in all required fields.';
                }
                break;
                
            case 'edit':
                $id = (int)$_POST['id'];
                $name = sanitizeInput($_POST['name']);
                $position = sanitizeInput($_POST['position']);
                $telegram_username = sanitizeInput($_POST['telegram_username']);
                $description = sanitizeInput($_POST['description']);
                $icon_color = sanitizeInput($_POST['icon_color']);
                $display_order = (int)$_POST['display_order'];
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                
                if (!empty($name) && !empty($position) && !empty($telegram_username)) {
                    try {
                        $stmt = $db->query("UPDATE telegram_officers SET name = ?, position = ?, telegram_username = ?, description = ?, icon_color = ?, display_order = ?, is_active = ? WHERE id = ?", [$name, $position, $telegram_username, $description, $icon_color, $display_order, $is_active, $id]);
                        $success = 'Telegram officer updated successfully!';
                    } catch (PDOException $e) {
                        $error = 'Error updating officer: ' . $e->getMessage();
                    }
                } else {
                    $error = 'Please fill in all required fields.';
                }
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                try {
                    $stmt = $db->query("DELETE FROM telegram_officers WHERE id = ?", [$id]);
                    $success = 'Telegram officer deleted successfully!';
                } catch (PDOException $e) {
                    $error = 'Error deleting officer: ' . $e->getMessage();
                }
                break;
        }
    }
}

// Get all telegram officers
try {
    $stmt = $db->query("SELECT * FROM telegram_officers ORDER BY display_order ASC, name ASC");
    $officers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $officers = [];
    $error = 'Error fetching officers: ' . $e->getMessage();
}

// Get specific officer for editing
$edit_officer = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    try {
        $stmt = $db->query("SELECT * FROM telegram_officers WHERE id = ?", [$edit_id]);
        $edit_officer = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = 'Error fetching officer data: ' . $e->getMessage();
    }
}

include 'includes/header.php';
?>

<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-[15px] font-semibold text-zinc-900">Telegram Officers</h1>
            <p class="text-[12px] text-zinc-400 mt-0.5">Manage officer contact information</p>
        </div>
        <button onclick="toggleAddForm()" class="inline-flex items-center bg-zinc-900 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-zinc-700 transition-colors">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/></svg>
            Add Officer
        </button>
    </div>

    <?php if (isset($error)): ?>
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-[13px]">
        <?php echo escape($error); ?>
    </div>
    <?php endif; ?>

    <?php if (isset($success)): ?>
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg text-[13px] alert-auto-hide">
        <?php echo escape($success); ?>
    </div>
    <?php endif; ?>

    <!-- Add/Edit Form -->
    <div id="addForm" class="<?php echo $edit_officer ? '' : 'hidden'; ?> bg-white border border-zinc-200 rounded-xl p-6">
        <h2 class="text-[15px] font-semibold text-zinc-900 mb-5"><?php echo $edit_officer ? 'Edit Officer' : 'Add New Officer'; ?></h2>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="action" value="<?php echo $edit_officer ? 'edit' : 'add'; ?>">
            <?php if ($edit_officer): ?>
                <input type="hidden" name="id" value="<?php echo $edit_officer['id']; ?>">
            <?php endif; ?>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[13px] font-medium text-zinc-700 mb-1.5">Name *</label>
                    <input type="text" name="name" required class="w-full border border-zinc-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="<?php echo $edit_officer ? escape($edit_officer['name']) : ''; ?>">
                </div>
                <div>
                    <label class="block text-[13px] font-medium text-zinc-700 mb-1.5">Position *</label>
                    <input type="text" name="position" required class="w-full border border-zinc-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="<?php echo $edit_officer ? escape($edit_officer['position']) : ''; ?>">
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[13px] font-medium text-zinc-700 mb-1.5">Telegram Username *</label>
                    <div class="flex">
                        <span class="inline-flex items-center px-3 py-2 rounded-l-lg border border-r-0 border-zinc-200 bg-zinc-50 text-zinc-400 text-sm">@</span>
                        <input type="text" name="telegram_username" required class="flex-1 border border-zinc-200 rounded-r-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="<?php echo $edit_officer ? htmlspecialchars($edit_officer['telegram_username']) : ''; ?>">
                    </div>
                </div>
                <div>
                    <label class="block text-[13px] font-medium text-zinc-700 mb-1.5">Icon Color</label>
                    <select name="icon_color" class="w-full border border-zinc-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="blue" <?php echo ($edit_officer && $edit_officer['icon_color'] === 'blue') ? 'selected' : ''; ?>>Blue</option>
                        <option value="green" <?php echo ($edit_officer && $edit_officer['icon_color'] === 'green') ? 'selected' : ''; ?>>Green</option>
                        <option value="purple" <?php echo ($edit_officer && $edit_officer['icon_color'] === 'purple') ? 'selected' : ''; ?>>Purple</option>
                        <option value="red" <?php echo ($edit_officer && $edit_officer['icon_color'] === 'red') ? 'selected' : ''; ?>>Red</option>
                        <option value="yellow" <?php echo ($edit_officer && $edit_officer['icon_color'] === 'yellow') ? 'selected' : ''; ?>>Yellow</option>
                        <option value="indigo" <?php echo ($edit_officer && $edit_officer['icon_color'] === 'indigo') ? 'selected' : ''; ?>>Indigo</option>
                    </select>
                </div>
            </div>
            
            <div>
                <label class="block text-[13px] font-medium text-zinc-700 mb-1.5">Description</label>
                <textarea name="description" rows="3" class="w-full border border-zinc-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"><?php echo $edit_officer ? htmlspecialchars($edit_officer['description']) : ''; ?></textarea>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[13px] font-medium text-zinc-700 mb-1.5">Display Order</label>
                    <input type="number" name="display_order" min="0" class="w-full border border-zinc-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="<?php echo $edit_officer ? $edit_officer['display_order'] : '0'; ?>">
                </div>
                <div class="flex items-end pb-2">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" id="is_active" class="w-4 h-4 rounded border-zinc-300 text-blue-600 focus:ring-blue-500" <?php echo ($edit_officer && $edit_officer['is_active']) || !$edit_officer ? 'checked' : ''; ?>>
                        <span class="text-[13px] text-zinc-700 ml-2">Active</span>
                    </label>
                </div>
            </div>
            
            <div class="flex gap-3 pt-2">
                <button type="submit" class="bg-zinc-900 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-zinc-700 transition-colors">
                    <?php echo $edit_officer ? 'Update Officer' : 'Add Officer'; ?>
                </button>
                <button type="button" onclick="cancelEdit()" class="bg-zinc-100 text-zinc-700 px-5 py-2 rounded-lg text-sm font-medium hover:bg-zinc-200 transition-colors">
                    Cancel
                </button>
            </div>
        </form>
    </div>

    <!-- Officers List -->
    <div class="bg-white border border-zinc-200 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-zinc-100">
                        <th class="px-5 py-3 text-left text-[11px] font-medium uppercase tracking-wider text-zinc-500">Officer</th>
                        <th class="px-5 py-3 text-left text-[11px] font-medium uppercase tracking-wider text-zinc-500">Position</th>
                        <th class="px-5 py-3 text-left text-[11px] font-medium uppercase tracking-wider text-zinc-500">Telegram</th>
                        <th class="px-5 py-3 text-left text-[11px] font-medium uppercase tracking-wider text-zinc-500">Order</th>
                        <th class="px-5 py-3 text-left text-[11px] font-medium uppercase tracking-wider text-zinc-500">Status</th>
                        <th class="px-5 py-3 text-left text-[11px] font-medium uppercase tracking-wider text-zinc-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    <?php if (empty($officers)): ?>
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-zinc-400 text-sm">
                            No officers yet. <button onclick="toggleAddForm()" class="text-blue-600 hover:text-blue-700">Add one</button>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($officers as $officer): ?>
                    <tr class="hover:bg-zinc-50 transition-colors">
                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-<?php echo htmlspecialchars($officer['icon_color']); ?>-500 text-white rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-[13px] font-medium text-zinc-900"><?php echo htmlspecialchars($officer['name']); ?></div>
                                    <?php if ($officer['description']): ?>
                                    <div class="text-[12px] text-zinc-400 truncate max-w-[200px]"><?php echo htmlspecialchars($officer['description']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 whitespace-nowrap text-[13px] text-zinc-600">
                            <?php echo htmlspecialchars($officer['position']); ?>
                        </td>
                        <td class="px-5 py-3.5 whitespace-nowrap text-[13px]">
                            <a href="https://t.me/<?php echo htmlspecialchars($officer['telegram_username']); ?>" target="_blank" class="text-blue-600 hover:text-blue-700">
                                @<?php echo htmlspecialchars($officer['telegram_username']); ?>
                            </a>
                        </td>
                        <td class="px-5 py-3.5 whitespace-nowrap text-[13px] text-zinc-500">
                            <?php echo $officer['display_order']; ?>
                        </td>
                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium <?php echo $officer['is_active'] ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600'; ?>">
                                <?php echo $officer['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td class="px-5 py-3.5 whitespace-nowrap text-[13px] font-medium space-x-3">
                            <a href="?edit=<?php echo $officer['id']; ?>" class="text-blue-600 hover:text-blue-700">Edit</a>
                            <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this officer?')">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $officer['id']; ?>">
                                <button type="submit" class="text-red-500 hover:text-red-700">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function toggleAddForm() {
    const form = document.getElementById('addForm');
    form.classList.toggle('hidden');
}

function cancelEdit() {
    window.location.href = 'telegram.php';
}
</script>

<?php include 'includes/footer.php'; ?>
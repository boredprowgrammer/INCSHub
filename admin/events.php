<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check authentication
requireAdmin();

$pageTitle = 'Manage Events';
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
            $description = sanitizeInput($_POST['description'] ?? '');
            $event_date = sanitizeInput($_POST['event_date'] ?? '');
            $event_time = sanitizeInput($_POST['event_time'] ?? '');
            $location = sanitizeInput($_POST['location'] ?? '');
            $max_attendees = (int)($_POST['max_attendees'] ?? 0);
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            $is_published = isset($_POST['is_published']) ? 1 : 0;
            
            // Handle image upload - Store as blob
            $upload_result = null;
            if (isset($_FILES['featured_image'])) {
                $upload_result = handleImageUpload($_FILES['featured_image'], UPLOAD_DIR_CONTENT);
            }
            
            if (empty($title) || empty($description) || empty($event_date)) {
                $error = 'Title, description, and event date are required.';
            } elseif (empty($error)) {
                try {
                    if ($action === 'add') {
                        $stmt = $db->query(
                            "INSERT INTO events (title, description, event_date, event_time, location, featured_image, featured_image_blob, featured_image_mime_type, featured_image_size, featured_image_filename, is_featured, is_published, max_attendees) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                            [
                                $title, $description, $event_date, $event_time ?: null, $location, 
                                $upload_result['image_path'] ?? '', $upload_result['image_data'] ?? null, 
                                $upload_result['mime_type'] ?? null, $upload_result['size'] ?? null, 
                                $upload_result['filename'] ?? null, $is_featured, $is_published, $max_attendees ?: null
                            ]
                        );
                        $success = 'Event added successfully!';
                    } else {
                        $id = (int)($_POST['id'] ?? 0);
                        $updateFields = [
                            $title, $description, $event_date, $event_time ?: null, $location, $is_featured, $is_published, $max_attendees ?: null, $id
                        ];
                        $sql = "UPDATE events SET title = ?, description = ?, event_date = ?, event_time = ?, location = ?, is_featured = ?, is_published = ?, max_attendees = ?, updated_at = NOW()";
                        
                        if ($upload_result) {
                            $sql .= ", featured_image = ?, featured_image_blob = ?, featured_image_mime_type = ?, featured_image_size = ?, featured_image_filename = ?";
                            array_splice($updateFields, -1, 0, [
                                $upload_result['image_path'], $upload_result['image_data'], 
                                $upload_result['mime_type'], $upload_result['size'], $upload_result['filename']
                            ]);
                        }
                        
                        $sql .= " WHERE id = ?";
                        
                        $stmt = $db->query($sql, $updateFields);
                        $success = 'Event updated successfully!';
                    }
                } catch (Exception $e) {
                    error_log("Event management error: " . $e->getMessage());
                    $error = 'An error occurred while saving the event.';
                }
            }
        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            try {
                // Get the image filename before deletion
                $event = $db->query("SELECT featured_image FROM events WHERE id = ?", [$id])->fetch();
                
                $stmt = $db->query("DELETE FROM events WHERE id = ?", [$id]);
                
                // Delete associated image file
                if ($event && $event['featured_image']) {
                    $imagePath = '../uploads/images/' . $event['featured_image'];
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }
                
                $success = 'Event deleted successfully!';
            } catch (Exception $e) {
                error_log("Event deletion error: " . $e->getMessage());
                $error = 'An error occurred while deleting the event.';
            }
        }
    }
}

// Get events
try {
    $events = $db->query("
        SELECT * FROM events 
        ORDER BY event_date ASC, event_time ASC
    ")->fetchAll();
} catch (Exception $e) {
    error_log("Events fetch error: " . $e->getMessage());
    $events = [];
}

// Handle edit mode
$editEvent = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $editEvent = $db->query("SELECT * FROM events WHERE id = ?", [$id])->fetch();
    } catch (Exception $e) {
        error_log("Event fetch error: " . $e->getMessage());
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

<?php if (isset($_GET['action']) && $_GET['action'] === 'add' || $editEvent): ?>
<!-- Add/Edit Form -->
<div class="bg-white border border-zinc-200 rounded-xl p-6 mb-8">
    <h3 class="text-[15px] font-semibold text-zinc-900 mb-5">
        <?php echo $editEvent ? 'Edit Event' : 'Add New Event'; ?>
    </h3>
    
    <form method="POST" enctype="multipart/form-data" class="space-y-5">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        <input type="hidden" name="action" value="<?php echo $editEvent ? 'edit' : 'add'; ?>">
        <?php if ($editEvent): ?>
        <input type="hidden" name="id" value="<?php echo $editEvent['id']; ?>">
        <?php endif; ?>
        
        <div>
            <label for="title" class="block text-[13px] font-medium text-zinc-700 mb-1.5">Title *</label>
            <input type="text" id="title" name="title" 
                   value="<?php echo escape($editEvent['title'] ?? ''); ?>"
                   class="w-full px-3 py-2 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                   required>
        </div>
        
        <div>
            <label for="description" class="block text-[13px] font-medium text-zinc-700 mb-1.5">Description *</label>
            <textarea id="description" name="description" rows="4"
                      class="w-full px-3 py-2 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      required><?php echo escape($editEvent['description'] ?? ''); ?></textarea>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div>
                <label for="event_date" class="block text-[13px] font-medium text-zinc-700 mb-1.5">Event Date *</label>
                <input type="date" id="event_date" name="event_date" 
                       value="<?php echo escape($editEvent['event_date'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       required>
            </div>
            
            <div>
                <label for="event_time" class="block text-[13px] font-medium text-zinc-700 mb-1.5">Event Time</label>
                <input type="time" id="event_time" name="event_time" 
                       value="<?php echo escape($editEvent['event_time'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div>
                <label for="max_attendees" class="block text-[13px] font-medium text-zinc-700 mb-1.5">Max Attendees</label>
                <input type="number" id="max_attendees" name="max_attendees" min="0"
                       value="<?php echo escape($editEvent['max_attendees'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="No limit">
            </div>
        </div>
        
        <div>
            <label for="location" class="block text-[13px] font-medium text-zinc-700 mb-1.5">Location</label>
            <input type="text" id="location" name="location" 
                   value="<?php echo escape($editEvent['location'] ?? ''); ?>"
                   class="w-full px-3 py-2 border border-zinc-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                   placeholder="e.g., Main Sanctuary, Fellowship Hall">
        </div>
        
        <div>
            <label for="featured_image" class="block text-[13px] font-medium text-zinc-700 mb-1.5">Featured Image</label>
            <input type="file" id="featured_image" name="featured_image" 
                   accept="image/*"
                   data-preview="image-preview"
                   class="w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200 transition-colors">
            <p class="text-[11px] text-zinc-400 mt-1">JPEG, PNG, GIF, or WebP. Max 5MB.</p>
            
            <?php if ($editEvent && $editEvent['featured_image']): ?>
            <div class="mt-2">
                <img src="../uploads/images/<?php echo escape($editEvent['featured_image']); ?>" 
                     alt="Current featured image" 
                     class="h-24 object-cover rounded-lg border border-zinc-200">
            </div>
            <?php endif; ?>
            
            <img id="image-preview" src="" alt="Preview" class="hidden mt-2 h-24 object-cover rounded-lg border border-zinc-200">
        </div>
        
        <div class="flex flex-wrap gap-5">
            <label class="flex items-center cursor-pointer">
                <input type="checkbox" name="is_featured" value="1"
                       <?php echo ($editEvent['is_featured'] ?? 0) ? 'checked' : ''; ?>
                       class="w-4 h-4 rounded border-zinc-300 text-blue-600 focus:ring-blue-500">
                <span class="text-[13px] text-zinc-700 ml-2">Featured Event</span>
            </label>
            
            <label class="flex items-center cursor-pointer">
                <input type="checkbox" name="is_published" value="1"
                       <?php echo ($editEvent['is_published'] ?? 1) ? 'checked' : ''; ?>
                       class="w-4 h-4 rounded border-zinc-300 text-blue-600 focus:ring-blue-500">
                <span class="text-[13px] text-zinc-700 ml-2">Published</span>
            </label>
        </div>
        
        <div class="flex gap-3 pt-2">
            <button type="submit" 
                    class="bg-zinc-900 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-zinc-700 transition-colors">
                <?php echo $editEvent ? 'Update Event' : 'Create Event'; ?>
            </button>
            
            <a href="events.php" 
               class="bg-zinc-100 text-zinc-700 px-5 py-2 rounded-lg text-sm font-medium hover:bg-zinc-200 transition-colors">
                Cancel
            </a>
        </div>
    </form>
</div>
<?php else: ?>
<!-- Add New Button -->
<div class="mb-6">
    <a href="events.php?action=add" 
       class="inline-flex items-center bg-zinc-900 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-zinc-700 transition-colors">
        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/></svg>
        Add Event
    </a>
</div>
<?php endif; ?>

<!-- Events List -->
<div class="bg-white border border-zinc-200 rounded-xl overflow-hidden">
    <div class="px-5 py-4 border-b border-zinc-100">
        <h3 class="text-[13px] font-semibold text-zinc-900">All Events</h3>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-zinc-100">
                    <th class="px-5 py-3 text-left text-[11px] font-medium uppercase tracking-wider text-zinc-500">Event</th>
                    <th class="px-5 py-3 text-left text-[11px] font-medium uppercase tracking-wider text-zinc-500">Date & Time</th>
                    <th class="px-5 py-3 text-left text-[11px] font-medium uppercase tracking-wider text-zinc-500">Location</th>
                    <th class="px-5 py-3 text-left text-[11px] font-medium uppercase tracking-wider text-zinc-500">Status</th>
                    <th class="px-5 py-3 text-left text-[11px] font-medium uppercase tracking-wider text-zinc-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100">
                <?php if (!empty($events)): ?>
                <?php foreach ($events as $event): ?>
                <tr class="hover:bg-zinc-50 transition-colors <?php echo strtotime($event['event_date']) < time() ? 'opacity-60' : ''; ?>">
                    <td class="px-5 py-3.5">
                        <div class="flex items-start">
                            <?php if ($event['featured_image']): ?>
                            <img src="../uploads/images/<?php echo escape($event['featured_image']); ?>" 
                                 alt="" class="w-10 h-10 object-cover rounded-lg mr-3 flex-shrink-0">
                            <?php endif; ?>
                            <div class="min-w-0">
                                <div class="text-[13px] font-medium text-zinc-900">
                                    <?php echo escape($event['title']); ?>
                                    <?php if ($event['is_featured']): ?>
                                    <span class="ml-1.5 inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-medium bg-amber-50 text-amber-700">Featured</span>
                                    <?php endif; ?>
                                </div>
                                <div class="text-[12px] text-zinc-400 truncate max-w-[250px]">
                                    <?php echo escape(substr($event['description'], 0, 80)); ?>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 whitespace-nowrap">
                        <div class="text-[13px] text-zinc-900"><?php echo date('M j, Y', strtotime($event['event_date'])); ?></div>
                        <?php if ($event['event_time']): ?>
                        <div class="text-[12px] text-zinc-400"><?php echo date('g:i A', strtotime($event['event_time'])); ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="text-[13px] text-zinc-600"><?php echo escape($event['location'] ?: '—'); ?></div>
                        <?php if ($event['max_attendees']): ?>
                        <div class="text-[12px] text-zinc-400">Max <?php echo number_format($event['max_attendees']); ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-3.5 whitespace-nowrap">
                        <div class="space-y-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium <?php echo $event['is_published'] ? 'bg-emerald-50 text-emerald-700' : 'bg-zinc-100 text-zinc-500'; ?>">
                                <?php echo $event['is_published'] ? 'Published' : 'Draft'; ?>
                            </span>
                            <?php if (strtotime($event['event_date']) < time()): ?>
                            <div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-zinc-100 text-zinc-500">Past</span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 whitespace-nowrap text-[13px] font-medium space-x-3">
                        <a href="events.php?action=edit&id=<?php echo $event['id']; ?>" 
                           class="text-blue-600 hover:text-blue-700">Edit</a>
                        
                        <form method="POST" class="inline" 
                              data-confirm="Are you sure you want to delete this event?">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $event['id']; ?>">
                            <button type="submit" class="text-red-500 hover:text-red-700">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="5" class="px-5 py-10 text-center text-zinc-400 text-sm">
                        No events yet. <a href="events.php?action=add" class="text-blue-600 hover:text-blue-700">Create your first event</a>.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
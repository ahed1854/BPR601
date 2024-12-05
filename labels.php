<?php
require_once 'config/database.php';
require_once 'includes/header.php';

$user_id = $_SESSION['user_id'];

// Fetch all labels for the user
$stmt = $pdo->prepare("SELECT * FROM labels WHERE user_id = ? ORDER BY name");
$stmt->execute([$user_id]);
$labels = $stmt->fetchAll();

// Get label usage counts
$stmt = $pdo->prepare("
    SELECT l.id, COUNT(il.id) as usage_count 
    FROM labels l 
    LEFT JOIN item_labels il ON l.id = il.label_id 
    WHERE l.user_id = ? 
    GROUP BY l.id
");
$stmt->execute([$user_id]);
$labelUsage = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'usage_count', 'id');
?>

<div class="container max-w-4xl mx-auto mt-8">
    <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-lg p-6">
        <h2 class="text-2xl font-bold mb-6 dark:text-white">Manage Labels</h2>
        
        <form action="actions/create_label.php" method="POST" class="mb-8">
            <div class="flex gap-4 items-end">
                <div class="flex-1">
                    <label class="block text-sm font-medium mb-1 dark:text-gray-300">Label Name</label>
                    <input type="text" 
                           name="name" 
                           class="w-full px-4 py-2 rounded-lg border dark:border-gray-600 dark:bg-gray-700 dark:text-white" 
                           placeholder="Enter label name" 
                           required>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 dark:text-gray-300">Color</label>
                    <input type="color" 
                           name="color" 
                           class="h-[42px] w-[100px] rounded-lg cursor-pointer bg-transparent p-1 dark:bg-gray-700 border border-gray-300 dark:border-gray-600" 
                           style="-webkit-appearance: none; padding: 0;"
                           value="#808080">
                </div>
                <button type="submit" 
                        class="px-6 py-2 rounded-lg bg-gradient-to-r from-blue-500 to-purple-600 text-white font-semibold hover:opacity-90">
                    Add Label
                </button>
            </div>
        </form>

        <div class="space-y-4">
            <?php if (empty($labels)): ?>
                <p class="text-gray-500 dark:text-gray-400 text-center py-4">No labels created yet</p>
            <?php else: ?>
                <?php foreach ($labels as $label): ?>
                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded" 
                                  style="background-color: <?= htmlspecialchars($label['color']) ?>">
                            </span>
                            <span class="font-medium dark:text-white">
                                <?= htmlspecialchars($label['name']) ?>
                            </span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                (<?= isset($labelUsage[$label['id']]) ? $labelUsage[$label['id']] : 0 ?> items)
                            </span>
                        </div>
                        <div class="flex items-center gap-3">
                            <button type="button" 
                                    onclick="openEditModal(<?= htmlspecialchars(json_encode($label)) ?>)"
                                    class="text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button"
                                    onclick="showConfirmModal(
                                        'Delete Label',
                                        'Are you sure you want to delete this label? This will remove it from all items.',
                                        () => deleteLabel(<?= $label['id'] ?>),
                                        'Delete'
                                    )"
                                    class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="editLabelModal" class="modal items-center justify-center p-4">
    <div class="modal-content bg-white dark:bg-dark-secondary w-full max-w-md rounded-xl shadow-2xl p-6">
        <div class="flex justify-between items-start mb-4">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white">Edit Label</h2>
            <button onclick="closeEditModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form id="editLabelForm" action="actions/update_label.php" method="POST">
            <input type="hidden" name="label_id" id="editLabelId">
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1 dark:text-gray-300">Label Name</label>
                <input type="text" 
                       name="name" 
                       id="editLabelName"
                       class="w-full px-4 py-2 rounded-lg border dark:border-gray-600 dark:bg-gray-700 dark:text-white" 
                       required>
            </div>
            <div class="mb-6">
                <label class="block text-sm font-medium mb-1 dark:text-gray-300">Color</label>
                <input type="color" 
                       name="color" 
                       id="editLabelColor"
                       class="h-[42px] w-full rounded-lg cursor-pointer bg-transparent p-1 dark:bg-gray-700 border border-gray-300 dark:border-gray-600" 
                       style="-webkit-appearance: none; padding: 0;">
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" 
                        onclick="closeEditModal()"
                        class="px-4 py-2 rounded-lg bg-gray-500 text-white hover:bg-gray-600 transition duration-300">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 rounded-lg bg-gradient-to-r from-blue-500 to-purple-600 text-white font-semibold hover:opacity-90">
                    Update Label
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(label) {
        document.getElementById('editLabelId').value = label.id;
        document.getElementById('editLabelName').value = label.name;
        document.getElementById('editLabelColor').value = label.color;
        document.getElementById('editLabelModal').classList.add('show');
        document.body.classList.add('modal-open');
    }
 
    function closeEditModal() {
        document.getElementById('editLabelModal').classList.remove('show');
        document.body.classList.remove('modal-open');
    }

    function deleteLabel(labelId) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'actions/delete_label.php';
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'label_id';
        input.value = labelId;
        
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
</script>

<?php require_once 'includes/footer.php'; ?> 
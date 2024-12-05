<?php
require_once 'label_functions.php';

function renderLabelSelector($pdo, $user_id, $item_id = null, $item_type = null) {
    $labels = getAllLabels($pdo, $user_id);
    $itemLabels = $item_id ? getItemLabels($pdo, $item_id, $item_type) : [];
    ?>
    <div class="form-group">
        <label>Labels:</label>
        <div class="labels-container">
            <?php foreach ($labels as $label): ?>
                <div class="form-check">
                    <input type="checkbox" 
                           name="label_ids[]" 
                           value="<?= $label['id'] ?>" 
                           class="form-check-input"
                           <?= $item_id && hasLabel($itemLabels, $label['id']) ? 'checked' : '' ?>>
                    <label class="form-check-label">
                        <span class="label-color" 
                              style="background-color: <?= htmlspecialchars($label['color']) ?>; 
                                     width: 12px; height: 12px; 
                                     display: inline-block; 
                                     margin-right: 5px;
                                     border-radius: 2px;"></span>
                        <?= htmlspecialchars($label['name']) ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
} 
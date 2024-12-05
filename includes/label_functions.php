<?php
function getItemLabels($pdo, $item_id, $item_type) {
    $stmt = $pdo->prepare("
        SELECT l.* 
        FROM labels l
        JOIN item_labels il ON l.id = il.label_id
        WHERE il.item_id = ? AND il.item_type = ?
    ");
    $stmt->execute([$item_id, $item_type]);
    return $stmt->fetchAll();
}

function hasLabel($labels, $label_id) {
    foreach ($labels as $label) {
        if ($label['id'] == $label_id) {
            return true;
        }
    }
    return false;
}

function getAllLabels($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM labels WHERE user_id = ? ORDER BY name");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

function displayItemLabels($labels) {
    if (!empty($labels)) {
        echo '<div class="flex flex-wrap gap-2 mt-2">';
        foreach ($labels as $label) {
            echo '<span class="px-2 py-1 rounded-full text-xs text-white" style="background-color: ' 
                . htmlspecialchars($label['color']) . '">'
                . htmlspecialchars($label['name']) 
                . '</span>';
        }
        echo '</div>';
    }
} 
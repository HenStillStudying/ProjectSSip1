<?php
// Load existing notes
$notes_file = 'notes.json';

if (!file_exists($notes_file)) {
    file_put_contents($notes_file, json_encode([]));
}

$notes = json_decode(file_get_contents($notes_file), true);

// Check if the request is valid
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['post_id'])) {
    $post_id = $_POST['post_id'];
    $action = $_POST['action'];
    $updated = false;

    foreach ($notes as &$note) {
        if ($note['id'] == $post_id) { // Use == instead of === to match string/integer
            if ($action === 'like') {
                if (!isset($note['likes'])) $note['likes'] = 0; // Ensure the key exists
                $note['likes'] += 1;
            } elseif ($action === 'dislike') {
                if (!isset($note['dislikes'])) $note['dislikes'] = 0;
                $note['dislikes'] += 1;
            }
            $updated = true;
            break;
        }
    }
    unset($note);

    // Save the updated notes
    if ($updated) {
        if (file_put_contents($notes_file, json_encode($notes, JSON_PRETTY_PRINT))) {
            echo json_encode([
                "success" => true,
                "likes" => $notes[array_search($post_id, array_column($notes, 'id'))]['likes'],
                "dislikes" => $notes[array_search($post_id, array_column($notes, 'id'))]['dislikes']
            ]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to save data"]);
        }
        exit;
    }
}

// Error response
echo json_encode(["success" => false, "message" => "Invalid request"]);
?>

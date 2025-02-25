<?php
// File to store notes metadata
$notes_file = 'notes.json';

// Load existing notes
$notes = [];
if (file_exists($notes_file)) {
    $json_data = file_get_contents($notes_file);
    $notes = json_decode($json_data, true);
}

// Ensure all notes have an ID, visibility status, like/dislike counts, replies, and ratings
foreach ($notes as &$note) {
    if (!isset($note['id'])) {
        $note['id'] = uniqid();
    }
    if (!isset($note['hidden'])) {
        $note['hidden'] = false;
    }
    if (!isset($note['likes'])) {
        $note['likes'] = 0;
    }
    if (!isset($note['dislikes'])) {
        $note['dislikes'] = 0;
    }
    if (!isset($note['replies'])) {
        $note['replies'] = [];
    }
    if (!isset($note['rating'])) {
        $note['rating'] = [];
    }
}
unset($note);
file_put_contents($notes_file, json_encode($notes, JSON_PRETTY_PRINT));

// Handle new post submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $user_id = $_POST['user_id'] ?? 'Anonymous';
    $content_type = $_POST['content_type'];
    $content_text = $_POST['content_text'] ?? ''; // Simpan teks deskripsi
    $uploaded_file = null;

    // Handle file uploads
    if (in_array($content_type, ['image', 'audio', 'video']) && isset($_FILES['file'])) {
        $upload_dir = 'uploads/';
        
        // Ensure the upload directory exists
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

    
        $file_ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        $unique_file_name = uniqid('file_', true) . '.' . $file_ext;
        $file_path = $upload_dir . $unique_file_name;
    
        // Check for successful upload
        if (move_uploaded_file($_FILES['file']['tmp_name'], $file_path)) {
            $uploaded_file = $file_path;
        } else {
            echo "Failed to upload the file.";
            exit;
        }
    }
    
    

    // Add the new post
    $new_note = [
        'id' => uniqid(),
        'user_id' => $user_id,
        'content_type' => $content_type,
        'content_text' => $content_text, // Simpan teks deskripsi
        'content' => $uploaded_file,
        'timestamp' => date('Y-m-d H:i:s'),
        'hidden' => false,
        'likes' => 0,
        'dislikes' => 0,
        'replies' => [],
        'rating' => []
    ];

    $notes[] = $new_note;
    file_put_contents($notes_file, json_encode($notes, JSON_PRETTY_PRINT));
    echo "Post added successfully!";
}

// Handle post actions (delete, hide, like, dislike, reply, rate)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $post_id = $_POST['post_id'] ?? '';

    foreach ($notes as &$note) {
        if ($note['id'] === $post_id) {
            if ($action === 'delete') {
                $notes = array_filter($notes, fn($note) => $note['id'] !== $post_id);
                echo "Post deleted successfully!";
            } elseif ($action === 'toggle_hide') {
                $note['hidden'] = !$note['hidden'];
                echo "Post visibility toggled!";
            } elseif ($action === 'like') {
                $note['likes']++;
                echo "Post liked!";
            } elseif ($action === 'dislike') {
                $note['dislikes']++;
                echo "Post disliked!";
            } elseif ($action === 'reply') {
                $reply_content = $_POST['reply_content'] ?? '';
                $reply = [
                    'id' => uniqid(),
                    'user_id' => 'Anonymous',
                    'content' => $reply_content,
                    'timestamp' => date('Y-m-d H:i:s')
                ];
                $note['replies'][] = $reply;
                echo "Reply added successfully!";
            } elseif ($action === 'rate') {
                $rating = (int)$_POST['rating'] ?? 0;
                if ($rating >= 1 && $rating <= 5) {
                    $note['rating'][] = $rating;
                    echo "Rating added successfully!";
                } else {
                    echo "Invalid rating value!";
                }
            }
            break;
        }
    }
    unset($note);
    file_put_contents($notes_file, json_encode(array_values($notes), JSON_PRETTY_PRINT));
}

// Filtering logic
$filter_type = $_GET['filter'] ?? '';
$filtered_notes = array_filter($notes, function ($note) use ($filter_type) {
    return !$note['hidden'] && (empty($filter_type) || $note['content_type'] === $filter_type);
});
?>

<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community Notes</title>
    <link rel="stylesheet" href="styles.css"> <!-- Add this line -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
</head>


<body>
<h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
    <a href="logout.php">Logout</a>
<div class="container">
<button id="toggle-dark-mode" class="btn btn-secondary mb-4"><i class="bi bi-moon-fill"></i> Toggle Dark Mode</button>

    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">

            <h1 class="text-center mb-4">Community Notes</h1>

            <!-- Content Filter -->
            <form method="GET" class="mb-4">
                <div class="d-flex justify-content-center">
                    <label for="filter" class="mr-2">Filter by Type:</label>
                    <select name="filter" id="filter" class="form-control w-auto">
                        <option value="">All</option>
                        <option value="text">Text</option>
                        <option value="image">Image</option>
                        <option value="audio">Audio</option>
                        <option value="video">Video</option>
                    </select>
                    <button type="submit" class="btn btn-primary ml-2"><i class="bi bi-funnel"></i> Filter</button>
                </div>
            </form>

            <!-- Form for Adding New Posts -->
            <form method="POST" enctype="multipart/form-data" class="mb-4">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="user_id" value="1">

                <div class="form-group">
                    <label for="content_type">Content Type:</label>
                        <select name="content_type" id="content_type" class="form-control" onchange="toggleFileInput(this.value)">
                            <option value="text">Text</option>
                            <option value="image">Image</option>
                            <option value="audio">Audio</option>
                            <option value="video">Video</option>
                        </select>
                </div>

                <div id="text-input">
                    <textarea name="content_text" rows="3" class="form-control mb-3" placeholder="Write a description..."></textarea>
                </div>

                <div id="file-input" style="display: none;">
                    <input type="file" name="file" accept="image/*,audio/*,video/*" class="form-control-file">
                </div>

                <button type="submit" class="btn btn-success btn-block mt-3">Add Post</button>
            </form>

            <!-- Notes Display -->
            <div class="row">
                <?php foreach (array_reverse($filtered_notes) as $note): ?>
                    <div class="col-12 mb-4">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($note['user_id']) ?> <small class="text-muted">posted at <?= htmlspecialchars($note['timestamp']) ?></small></h5>
                                <hr>

                                <!-- Content Type Handling -->
                                <?php if ($note['content_type'] === 'text'): ?>
                                    <p class="card-text"><?= nl2br(htmlspecialchars($note['content_text'])) ?></p>
                                <?php else: ?>
                                <!-- Display uploaded file -->
                                <?php if ($note['content_type'] === 'image'): ?>
                                    <img src="<?= htmlspecialchars($note['content']) ?>" alt="Image" class="img-fluid rounded mb-2" style="max-height: 300px; object-fit: cover;">
                                <?php elseif ($note['content_type'] === 'audio'): ?>
                                    <audio controls class="w-100 mb-2"><source src="<?= htmlspecialchars($note['content']) ?>" type="audio/mpeg"></audio>
                                <?php elseif ($note['content_type'] === 'video'): ?>
                                    <video controls class="w-100 mb-2" style="max-height: 300px; object-fit: cover;"><source src="<?= htmlspecialchars($note['content']) ?>" type="video/mp4"></video>
                                <?php endif; ?>

                                <!-- Display text description if available -->
                                <?php if (!empty($note['content_text'])): ?>
                                    <p class="card-text"><strong>Description:</strong> <?= nl2br(htmlspecialchars($note['content_text'])) ?></p>
                                <?php endif; ?>
                            <?php endif; ?>

                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p><strong>Likes: <?= $note['likes'] ?> | Dislikes: <?= $note['dislikes'] ?></strong></p>
                                        <?php
                                            // Calculate the average rating for the post
                                            $average_rating = count($note['rating']) ? array_sum($note['rating']) / count($note['rating']) : 'No ratings yet';
                                        ?>
                                        <p>Average Rating: <?= is_numeric($average_rating) ? number_format($average_rating, 1) : $average_rating ?></p>
                                    </div>

                                    <div>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="like">
                                            <input type="hidden" name="post_id" value="<?= $note['id'] ?>">
                                            <button class="btn btn-outline-primary btn-sm like-btn" data-id="<?= $note['id'] ?>">
                                                <i class="bi bi-hand-thumbs-up"></i> Like (<span id="like-count-<?= $note['id'] ?>"><?= $note['likes'] ?></span>)
                                            </button>
                                        </form>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="dislike">
                                            <input type="hidden" name="post_id" value="<?= $note['id'] ?>">
                                            <button class="btn btn-outline-danger btn-sm dislike-btn" data-id="<?= $note['id'] ?>">
                                                <i class="bi bi-hand-thumbs-down"></i> Dislike (<span id="dislike-count-<?= $note['id'] ?>"><?= $note['dislikes'] ?></span>)
                                            </button>
                                        </form>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="toggle_hide">
                                            <input type="hidden" name="post_id" value="<?= $note['id'] ?>">
                                            <button type="submit" class="btn btn-outline-warning btn-sm"><i class="bi-eye-slash"></i> Hide</button>
                                        </form>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="post_id" value="<?= $note['id'] ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i> Delete</button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Replies Section -->
                            <div class="card-footer">
                                <h6><i class="bi bi-chat-left-text"></i> Replies:</h6>
                                <ul class="list-unstyled">
                                    <?php foreach ($note['replies'] as $reply): ?>
                                        <li class="border-bottom mb-2 pb-2">
                                            <strong><?= htmlspecialchars($reply['user_id']) ?>:</strong>
                                            <p><?= nl2br(htmlspecialchars($reply['content'])) ?></p>
                                            <small class="text-muted">Replied at: <?= htmlspecialchars($reply['timestamp']) ?></small>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>

                                <form method="POST" class="mb-3">
                                    <input type="hidden" name="action" value="reply">
                                    <input type="hidden" name="post_id" value="<?= $note['id'] ?>">
                                    <textarea name="reply_content" class="form-control mb-2" rows="2" placeholder="Write your reply..."></textarea><br>
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-reply"></i> Reply</button>
                                </form>

                                <form method="POST" class="mb-3">
                                    <input type="hidden" name="action" value="rate">
                                    <input type="hidden" name="post_id" value="<?= $note['id'] ?>">
                                    <label for="rating" class="mr-2"><i class="bi bi-star"></i> Rate:</label>
                                    <select name="rating" id="rating" class="form-control d-inline-block w-auto">
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                    </select>
                                    <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-send"></i> Submit Rating</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>
    </div>
</div>


    <!-- Bootstrap JS & Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

    <script>
       function toggleFileInput(value) {
    document.getElementById('file-input').style.display = value === 'text' ? 'none' : 'block';
}
    </script>
w
<script>
    document.getElementById('toggle-dark-mode').addEventListener('click', function () {
    document.body.classList.toggle('dark-mode');
    localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
});

if (localStorage.getItem('darkMode') === 'true') {
    document.body.classList.add('dark-mode');
}
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        $(".like-btn, .dislike-btn").off("click").on("click", function (e) {
            e.preventDefault(); // Prevent double click issue
            let button = $(this);
            let post_id = button.data("id");
            let action = button.hasClass("like-btn") ? "like" : "dislike";
            let likeCount = $("#like-count-" + post_id);
            let dislikeCount = $("#dislike-count-" + post_id);

            // Disable button temporarily to prevent multiple clicks
            button.prop("disabled", true);

            $.ajax({
                url: "update_note.php",
                type: "POST",
                data: { post_id: post_id, action: action },
                dataType: "json",
                success: function (response) {
                    if (response.success) {
                        likeCount.text(response.likes);
                        dislikeCount.text(response.dislikes);
                    } else {
                        alert("Error: " + response.message);
                    }
                },
                error: function () {
                    alert("Failed to update. Please try again.");
                },
                complete: function () {
                    button.prop("disabled", false); // Re-enable after AJAX completes
                }
            });
        });
    });
</script>


</body>
</html>

<?php
// auth.php - Handles registration and login logic

$users_file = 'users.json';

// Load existing users
$users = [];
if (file_exists($users_file)) {
    $json_data = file_get_contents($users_file);
    $users = json_decode($json_data, true);
}

// Handle Registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Check if username exists
    if (isset($users[$username])) {
        echo "Username already exists!";
    } elseif (!empty($username) && !empty($password)) {
        $users[$username] = password_hash($password, PASSWORD_DEFAULT);
        file_put_contents($users_file, json_encode($users, JSON_PRETTY_PRINT));
        echo "Registration successful! You can now <a href='login.php'>login</a>.";
    } else {
        echo "Please fill in all fields.";
    }
}

// Handle Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (isset($users[$username]) && password_verify($password, $users[$username])) {
        session_start();
        $_SESSION['username'] = $username;
        header('Location: index.php'); // Redirect to community notes page
        exit;
    } else {
        echo "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container" style="max-width: 400px; margin: auto; padding: 20px;">
        <div class="card">
            <div class="card-body">
                <h1 class="card-title text-center">Login</h1>
                <form method="POST" action="auth.php">
                    <input type="hidden" name="action" value="login">
                    
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-outline-primary btn-block" style="width: 100%;">Login</button>
                </form>
                <p class="text-center" style="margin-top: 15px;">Don't have an account? <a href="register.php">Register here</a>.</p>
            </div>
        </div>
    </div>
</body>
</html>

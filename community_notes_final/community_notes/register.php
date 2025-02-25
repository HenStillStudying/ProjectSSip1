<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container" style="max-width: 400px; margin: auto; padding: 20px;">
        <div class="card">
            <div class="card-body">
                <h1 class="card-title text-center">Register</h1>
                <form method="POST" action="auth.php">
                    <input type="hidden" name="action" value="register">
                    
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-success btn-block" style="width: 100%;">Register</button>
                </form>
                <p class="text-center" style="margin-top: 15px;">Already have an account? <a href="login.php">Login here</a>.</p>
            </div>
        </div>
    </div>
</body>
</html>

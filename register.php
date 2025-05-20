<?php
session_start();
include 'connection.php';

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $username = $_POST['reg_username'];
    $email = filter_var(trim($_POST['reg_email']), FILTER_SANITIZE_EMAIL);
    $password = password_hash($_POST['reg_password'], PASSWORD_DEFAULT);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error_message = "Username or email already exists. Please choose a different one.";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $password);
            
            if ($stmt->execute()) {
                $success_message = "Registration successful! You can now log in.";
            } else {
                $error_message = "Error: " . $stmt->error;
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <div class="auth-header-container">
            <div class="header-left-title">Your Personalized Task Manager</div>
        </div>
    </header>
    <div class="gap"></div>
    <main>
        <div class="form-container">
            <h1>Register</h1>
            <form method="POST" action="register.php" class="task-form">
                <input type="text" name="reg_username" placeholder="Username" required>
                <input type="email" name="reg_email" placeholder="Email" required>
                <input type="password" name="reg_password" placeholder="Password" required>
                <button type="submit" name="register" class="add-task-btn">Register</button>
            </form>
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
         <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <?php if (!empty($success_message)): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>
    </main>
</body>
</html>

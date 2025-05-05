<?php
session_start();

include 'connection.php';

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $username = $_POST['reg_username'];
    $password = password_hash($_POST['reg_password'], PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $error_message = "Username already exists. Please choose a different username.";
    } else {
        
        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $password);
        
        if ($stmt->execute()) {
            echo "<div class='success-message'>Registration successful! You can now log in.</div>";
        } else {
            $error_message = "Error: " . $stmt->error;
        }
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Register</h1>
    </header>
    <main>
        <form method="POST" action="register.php" class="task-form">
            <input type="text" name="reg_username" placeholder="Username" required>
            <input type="password" name="reg_password" placeholder="Password" required>
            <button type="submit" name="register" class="add-task-btn">Register</button>
        </form>

        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <p>Already have an account? <a href="login.php">Login here</a></p>
    </main>
</body>
</html>
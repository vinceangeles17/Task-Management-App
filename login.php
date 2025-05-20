<?php
session_start();

include 'connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $hashed_password);
        $stmt->fetch();
        
        if (password_verify($password, $hashed_password)) {
            $_SESSION['username'] = $username;
            $_SESSION['user_id'] = $user_id;
            header("Location: index.php");
            exit();
        } else {
            $error_message = "Invalid Password";
        }
    } else {
        $error_message = "No user found";;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css"> 
</head>
<body>
    <header>
        <img src="TASKMANAGERLOGO.png" alt="Logo" class="logo" style="max-width:100%;height:auto;">
        <div class="auth-header-container">
            <div class="header-left-title">Your Personalized Task Manager</div>
        </div>
    </header>
   <div class="gap"></div>
    <main>
        <div class="form-container">
           <h1>Login</h1>
            <form method="POST" action="login.php" class="task-form">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="login" class="add-task-btn">Login</button>
            </form>
            <p>Don't have an account? <a href="register.php">Register here</a></p>
        </div>
        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
    </main>
</body>
</html>

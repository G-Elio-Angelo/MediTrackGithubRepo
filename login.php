<?php
session_start();
require_once "DatabaseFolder/dbconnection.php";
require_once "User.php";

$db = new Database();
$conn = $db->getConnection();
$user = new User($conn);

$message = "";

if (isset($_GET['registered'])) {
    $message = "Registration successful! Please log in.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $result = $user->login($_POST['email'], $_POST['password']);
    if (is_array($result)) {
        $_SESSION['user_id'] = $result['id'];
        $_SESSION['username'] = $result['username'];
        header("Location: dashboard.php");
        exit;
    } else {
        $message = $result;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <form method="POST" action="">
        <h2>Login</h2>

        <p class="message"><?php echo $message; ?></p>

        <input type="email" name="email" placeholder="Enter Email" required>
        <input type="password" name="password" placeholder="Enter Password" required>

        <button type="submit">Login</button>

        <div class="register-link">
            <p>Don't have an account? <a href="registration.php">Register</a></p>
        </div>
    </form>

</body>
</html>

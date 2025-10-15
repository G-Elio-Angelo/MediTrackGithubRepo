<?php
require_once "DatabaseFolder/dbconnection.php";
require_once "User.php";

$db = new Database();
$conn = $db->getConnection();
$user = new User($conn);

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $result = $user->register($_POST['username'], $_POST['email'], $_POST['password'], $_POST['Phone_Number']);
    if ($result === true) {
        header("Location: login.php?registered=true");
        exit;
    } else {
        $message = $result;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register</title> 
    <link rel="stylesheet" href="style.css">  
</head>
<body>

    <form method="POST" action="">
        <h2>Register</h2>

        <p class="message"><?php echo $message; ?></p>

        <input type="text" name="username" placeholder="Enter Username" required>
        <input type="email" name="email" placeholder="Enter Email" required>
        <input type="password" name="password" placeholder="Enter Password" required>
        <input type="tel" name="Phone_Number" placeholder="Enter Phone Number" required>

        <button type="submit">Register</button>

        <div class="login-link">
            <p>Already have an account? <a href="login.php">Login</a></p>
        </div>
    </form>

</body>
</html>
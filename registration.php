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
        header("Location: login.php");
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
</head>
<body>
    <form method="POST" action="">
        <h2>Register</h2>
        <p style="color:red;"><?php echo $message; ?></p>
        <input type="text" name="username" placeholder="Enter Username" required><br>
        <input type="email" name="email" placeholder="Enter Email" required><br>
        <input type="password" name="password" placeholder="Enter Password" required><br>
        <input type="tel" name="Phone_Number" placeholder="Enter Phone Number" required><br>
        <button type="submit">Register</button>
    </form>
</body>
</html>

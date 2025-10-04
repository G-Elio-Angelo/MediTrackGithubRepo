<?php
class User {
    private $conn;
    private $table_name = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Register new user
    public function register($username, $email, $password, $Phone_Number) {
        // check if email already exists
        $query = "SELECT * FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return "Email is already registered!";
        }

        // insert new user
        $query = "INSERT INTO " . $this->table_name . " (username, email, password, Phone_Number)
                  VALUES (:username, :email, :password, :Phone_Number)";
        $stmt = $this->conn->prepare($query);

        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam("Phone_Number", $Phone_Number);
        $stmt->bindParam(":password", $hashed_password);

        if ($stmt->execute()) {
            return true;
        } else {
            return "Registration failed!";
        }
    }

    // Login user
    public function login($email, $password) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $row['password'])) {
                return $row;
            } else {
                return "Invalid password!";
            }
        } else {
            return "No account found with that email!";
        }
    }
}
?>

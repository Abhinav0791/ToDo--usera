<?php
session_start();
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password']; // added closing quote

    $sql = "SELECT id, password FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $hashed_password); // changed variable name to $hashed_password

    if ($stmt->fetch() && $password == $hashed_password) { // removed password_verify
        $_SESSION['user_id'] = $id;
        header("Location: todo.php");
        exit();
    } else {
        echo "Invalid email or password.";
    }

    $stmt->close();
}
?>
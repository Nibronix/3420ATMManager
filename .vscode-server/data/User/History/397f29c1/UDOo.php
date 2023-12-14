<?php
// login.php
session_start();

// Replace these with your actual database credentials
$host = 'localhost';
$dbname = 'your_database';
$dbuser = 'your_username';
$dbpass = 'your_password';

// Create a new PDO instance
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $dbuser, $dbpass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Prepare and execute the SQL statement
        $stmt = $conn->prepare('SELECT * FROM users WHERE username = :username');
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            // Verify the password against the hash stored in the database
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                // Redirect to a new page (e.g., dashboard)
                header('Location: dashboard.php');
                exit;
            } else {
                echo 'Incorrect password.';
            }
        } else {
            echo 'Username does not exist.';
        }
    }
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}
?>
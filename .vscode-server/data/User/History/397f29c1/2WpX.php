
<?php
require_once("config.php");

// Start the session
session_start();

$login_error = ''; // Initialize login error message

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Connect to the database
    $conn = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get user input from the form
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare and execute the query
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    if ($stmt->rowCount() == 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Set session variables and redirect to a new page
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: dashboard.php');
            exit;
        } else {
            $login_error = 'Incorrect password.';
        }
    } else {
        $login_error = 'Username does not exist.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= htmlspecialchars($PROJECT_NAME) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1><?= htmlspecialchars($PROJECT_NAME) ?></h1>
    <?php include("nav.php"); ?>
    
    <!-- Display the login form -->
    <form method="post" action="login.php">
        Username: <input type="text" name="username"><br>
        Password: <input type="password" name="password"><br>
        <input type="submit" value="Login">
    </form>
    
    <!-- Show login error if it exists -->
    <?php if (!empty($login_error)): ?>
        <p><?= htmlspecialchars($login_error) ?></p>
    <?php endif; ?>

    <?php echo "Welcome to our castle!"; ?>
</body>
</html>

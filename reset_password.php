<?php
// 1. Force sessions to only use cookies
ini_set('session.use_only_cookies', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'], 
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);

session_start();

// Include database configuration
require_once('processphp/config.php');

// Ensure the PDO connection variable is available
if (!isset($connection)) {
    header("Location: forgot_password.php?error=" . urlencode("Database connection variable not found."));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $newPassword = $_POST['newPassword'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';

    if (empty($email) || empty($newPassword) || empty($confirmPassword)) {
        header("Location: forgot_password.php?error=" . urlencode("Form data missing."));
        exit();
    }

    if ($newPassword !== $confirmPassword) {
        header("Location: forgot_password.php?error=" . urlencode("Passwords do not match."));
        exit();
    }

    // Hash the password securely
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    if ($hashedPassword === false) {
        header("Location: forgot_password.php?error=" . urlencode("Password hashing failed."));
        exit();
    }

    try {
        // Check if the email exists in the database using PDO
        $checkEmailStmt = $connection->prepare("SELECT email FROM user_client WHERE email = :email");
        $checkEmailStmt->bindParam(":email", $email, PDO::PARAM_STR);
        $checkEmailStmt->execute();

        // Fetch the row instead of using rowCount() to prevent driver inconsistencies
        $user = $checkEmailStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            header("Location: forgot_password.php?error=" . urlencode("Email does not exist."));
            exit();
        }

        // Update the password using PDO
        // Note: I also update password_unhashed because your processphp/forgot_password.php 
        // strictly requires this column to NOT be empty to send the reset code.
        $stmt = $connection->prepare("UPDATE user_client SET password = :password, password_unhashed = :unhashed WHERE email = :email");
        $stmt->bindParam(":password", $hashedPassword, PDO::PARAM_STR);
        $stmt->bindParam(":unhashed", $newPassword, PDO::PARAM_STR); 
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);

        if ($stmt->execute()) {
            // Success: Redirect to index.php (the login page) with a success message
            header("Location: index.php?success=" . urlencode("Password reset successfully. You can now log in."));
            exit();
        } else {
            header("Location: forgot_password.php?error=" . urlencode("Error updating password."));
            exit();
        }
    } catch (PDOException $e) {
        // Catch any PDO exceptions and redirect with error instead of white screen
        error_log("Database Error in reset_password.php: " . $e->getMessage());
        header("Location: forgot_password.php?error=" . urlencode("A database error occurred."));
        exit();
    } catch (Exception $e) {
        // Catch any other generic exceptions
        error_log("Error in reset_password.php: " . $e->getMessage());
        header("Location: forgot_password.php?error=" . urlencode("An unexpected error occurred."));
        exit();
    }
} else {
    // If not a POST request, redirect back
    header("Location: forgot_password.php");
    exit();
}
?>
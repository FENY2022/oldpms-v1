<?php
session_start();

if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: ../client/index.php");
    exit;
}

include('config.php');

define('CLIENT_MAX_LOGIN_ATTEMPTS', 3);
define('CLIENT_LOCKOUT_SECONDS', 180);

function redirectWithClientLoginError($message) {
    header("Location: ../login.php?error=" . urlencode($message));
    exit();
}

function registerFailedClientLogin($message) {
    $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;

    if ($_SESSION['login_attempts'] >= CLIENT_MAX_LOGIN_ATTEMPTS) {
        $_SESSION['lockout_time'] = time() + CLIENT_LOCKOUT_SECONDS;
        redirectWithClientLoginError('Too many failed attempts. Please try again after 3 minutes.');
    }

    $attempts_left = CLIENT_MAX_LOGIN_ATTEMPTS - $_SESSION['login_attempts'];
    redirectWithClientLoginError($message . ' ' . $attempts_left . ' attempts remaining.');
}

// We use $_SERVER['REQUEST_METHOD'] === 'POST' because JS form.submit() doesn't send the button 'name'
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- 1. Account Lockout Check ---
    if (isset($_SESSION['lockout_time']) && time() < $_SESSION['lockout_time']) {
        $wait = $_SESSION['lockout_time'] - time();
        redirectWithClientLoginError("Account locked due to too many failed attempts. Try again in $wait seconds.");
    }

    // --- 2. Verify reCAPTCHA ---
    $recaptcha_secret = '6LeTIY0sAAAAAHPR6a4KnPDoFVaeu0Jb-0UoO37G';
    $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
    $verify_response = @file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($recaptcha_secret) . '&response=' . urlencode($recaptcha_response));
    $response_data = json_decode($verify_response);
    
    if(!$response_data || !$response_data->success) {
        registerFailedClientLogin('reCAPTCHA validation failed. Please check the box.');
    }

    $math_answer = filter_input(INPUT_POST, 'math_answer', FILTER_VALIDATE_INT);
    if (!isset($_SESSION['client_math_answer']) || $math_answer === false || $math_answer !== (int)$_SESSION['client_math_answer']) {
        registerFailedClientLogin('Math answer is incorrect. Please try again.');
    }
    unset($_SESSION['client_math_answer']);

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'];

    $query = $connection->prepare("SELECT * FROM user_client WHERE email = :email LIMIT 1");
    $query->bindValue(':email', $email, PDO::PARAM_STR);
    $query->execute();

    // Check if the query execution was successful
    $result = false;
    if ($query) {
        $result = $query->fetch(PDO::FETCH_ASSOC);

        // Check if a row was fetched
        if ($result !== false) {
            if ($result['Status'] == 0) {
                header("Location: ../login.php?error=Your account is being validated.");
                exit();
            }
        }
    }

    if (!$result) {
        registerFailedClientLogin('Email and Password combination is wrong!');
    } else {
        $id = $result['client_id'];
        $hash = $result['password'];

        if (password_verify($password, $result['password'])) {
            // Success! Clear lockout tracking
            unset($_SESSION['login_attempts']);
            unset($_SESSION['lockout_time']);
            session_regenerate_id(true);

            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }

            // Store data in session variables
            $_SESSION["loggedin"] = true;
            $_SESSION["client_id"] = $id;
            $_SESSION["email"] = $email;

            header("Location: ../client/index.php?success=You have been logged in successfully.");
            exit();
        } else {
            registerFailedClientLogin('Invalid password!');
        }
    }
} else {
    // Redirect back to login if someone accesses this file via GET directly
    header("Location: ../login.php");
    exit();
}
?>

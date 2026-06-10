<?php
// Prevent Clickjacking by setting X-Frame-Options and Content-Security-Policy headers
header('X-Frame-Options: SAMEORIGIN');
header("Content-Security-Policy: frame-ancestors 'self';");

// 1. Force sessions to only use cookies
ini_set('session.use_only_cookies', 1);
// 2. Prevent session fixation attacks
ini_set('session.use_strict_mode', 1);
// 3. Set strict cookie parameters
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');

// 4. Set the cookie parameters array
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

// Include PHPMailer classes directly
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'sendemail/phpmailer/src/Exception.php';
require 'sendemail/phpmailer/src/PHPMailer.php';
require 'sendemail/phpmailer/src/SMTP.php';

$current_page = 'forgot_password.php'; // Hardcoded for reliable redirection

// ==========================================
// 1. SEND VERIFICATION CODE LOGIC
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action_send_code'])) {
    $email = trim($_POST['email']);

    // Rate Limiting (1 request per 60 seconds)
    if (isset($_SESSION['last_reset_time']) && time() - $_SESSION['last_reset_time'] < 60) {
        header("Location: $current_page?error=" . urlencode("Please wait a minute before requesting another code."));
        exit();
    }
    
    // Verify reCAPTCHA
    $recaptcha_secret = '6LeTIY0sAAAAAHPR6a4KnPDoFVaeu0Jb-0UoO37G';
    $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
    
    // Catch if user didn't check the captcha box
    if (empty($recaptcha_response)) {
        header("Location: $current_page?error=" . urlencode("Please check the CAPTCHA box to proceed."));
        exit();
    }

    $verify_response = @file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$recaptcha_secret.'&response='.$recaptcha_response);
    $response_data = json_decode($verify_response);
    
    if(!$response_data || !$response_data->success) {
        header("Location: $current_page?error=" . urlencode("reCAPTCHA validation failed. Please try again."));
        exit();
    }

    $_SESSION['last_reset_time'] = time();

    // Check if the email exists in the database
    $query = $connection->prepare("SELECT email, password_unhashed FROM user_client WHERE email=:email");
    $query->bindParam(":email", $email, PDO::PARAM_STR);
    $query->execute();

    if ($query->rowCount() > 0) {
        $user = $query->fetch(PDO::FETCH_ASSOC);

        // Check if the password_unhashed column has a value
        if (!empty($user['password_unhashed'])) {
            // Generate a verification code
            $verification_code = rand(100000, 999999);

            // Store the verification code in the session
            $_SESSION['verification_code'] = $verification_code;
            $_SESSION['email'] = $email;

            // ==========================================
            // DIRECT PHPMAILER INTEGRATION
            // ==========================================
            $mail = new PHPMailer(true);
            try {
                //Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'otosamsosdenrcaraga@gmail.com'; 
                
                // IMPORTANT: Generate a NEW App Password from Google and paste it here!
                $mail->Password = 'qrkm kzyb qjkk lvjf'; 
                
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                //Recipients
                $mail->setFrom('otosamsosdenrcaraga@gmail.com', 'O-LDPMS PASSWORD'); 
                $mail->addAddress($email); 

                //Content
                $mail->isHTML(true); 
                $mail->Subject = 'Password Reset Verification Code'; 
                $mail->Body    = 'Your verification code is: <b style="font-size:20px; color:#1e3799;">' . $verification_code . '</b>'; 
                $mail->AltBody = 'Your verification code is: ' . $verification_code;

                $mail->send();
            } catch (Exception $e) {
                // If the email fails, we will display the exact error in the Toast notification!
                header("Location: $current_page?error=" . urlencode("Email failed: " . $mail->ErrorInfo));
                exit();
            }
        }
    } 

    // GENERIC RESPONSE: Protects against Email Enumeration
    header("Location: $current_page?code=1&email=" . urlencode($email) . "&success=" . urlencode("If the email exists, a verification code has been sent."));
    exit();
}

// ==========================================
// 2. VERIFY CODE LOGIC
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action_verify_code'])) {
    $entered_code = trim($_POST['code'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (isset($_SESSION['verification_code']) && $_SESSION['verification_code'] == $entered_code) {
        // Redirect to show the password reset modal
        header("Location: $current_page?showModal=true&email=" . urlencode($email));
        exit();
    } else {
        header("Location: $current_page?code=1&email=" . urlencode($email) . "&error=" . urlencode("Invalid Verification Code."));
        exit();
    }
}

// ==========================================
// 3. FINAL PASSWORD RESET LOGIC
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action_reset_password'])) {
    
    if (!isset($connection)) {
        header("Location: $current_page?error=" . urlencode("Database connection variable not found."));
        exit();
    }

    $email = trim($_POST['email'] ?? '');
    $newPassword = $_POST['newPassword'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';

    if (empty($email) || empty($newPassword) || empty($confirmPassword)) {
        header("Location: $current_page?showModal=true&email=" . urlencode($email) . "&error=" . urlencode("Form data missing."));
        exit();
    }

    if ($newPassword !== $confirmPassword) {
        header("Location: $current_page?showModal=true&email=" . urlencode($email) . "&error=" . urlencode("Passwords do not match."));
        exit();
    }

    // Hash the password securely
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    if ($hashedPassword === false) {
        header("Location: $current_page?showModal=true&email=" . urlencode($email) . "&error=" . urlencode("Password hashing failed."));
        exit();
    }

    try {
        // Check if the email exists in the database using PDO
        $checkEmailStmt = $connection->prepare("SELECT email FROM user_client WHERE email = :email");
        $checkEmailStmt->bindParam(":email", $email, PDO::PARAM_STR);
        $checkEmailStmt->execute();

        $user = $checkEmailStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            header("Location: $current_page?error=" . urlencode("Email does not exist."));
            exit();
        }

        // Update the password using PDO
        $stmt = $connection->prepare("UPDATE user_client SET password = :password, password_unhashed = :unhashed WHERE email = :email");
        $stmt->bindParam(":password", $hashedPassword, PDO::PARAM_STR);
        $stmt->bindParam(":unhashed", $newPassword, PDO::PARAM_STR); 
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);

        if ($stmt->execute()) {
            // Clear session verification data
            unset($_SESSION['verification_code']);
            unset($_SESSION['email']);

            // Success: Redirect to index.php (the login page)
            header("Location: index.php?success=" . urlencode("Password reset successfully. You can now log in."));
            exit();
        } else {
            header("Location: $current_page?showModal=true&email=" . urlencode($email) . "&error=" . urlencode("Error updating password."));
            exit();
        }
    } catch (PDOException $e) {
        error_log("Database Error in reset_password: " . $e->getMessage());
        header("Location: $current_page?showModal=true&email=" . urlencode($email) . "&error=" . urlencode("A database error occurred."));
        exit();
    } catch (Exception $e) {
        error_log("Error in reset_password: " . $e->getMessage());
        header("Location: $current_page?showModal=true&email=" . urlencode($email) . "&error=" . urlencode("An unexpected error occurred."));
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>O-LDPMS | Forgot Password</title>
    <meta name="keywords" content="RB-IIMS ENVIRONMENT SYSTEM" />
    <meta name="description" content="DENR RBCO RIVER BASIN PEMSEA">
    <meta name="author" content="JAWA Production">

    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: linear-gradient(135deg, #0c2461 0%, #1e3799 30%, #4a69bd 70%, #6a89cc 100%); min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 20px; position: relative; overflow-x: hidden; }
        .bg-bubbles { position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 1; overflow: hidden; }
        .bg-bubbles li { position: absolute; list-style: none; display: block; width: 40px; height: 40px; background-color: rgba(255, 255, 255, 0.15); bottom: -160px; border-radius: 50%; animation: square 25s infinite; transition-timing-function: linear; }
        .bg-bubbles li:nth-child(1) { left: 10%; animation-delay: 0s; }
        .bg-bubbles li:nth-child(2) { left: 20%; width: 80px; height: 80px; animation-delay: 2s; animation-duration: 17s; }
        .bg-bubbles li:nth-child(3) { left: 25%; animation-delay: 4s; }
        .bg-bubbles li:nth-child(4) { left: 40%; width: 60px; height: 60px; animation-duration: 22s; background-color: rgba(255, 255, 255, 0.25);}
        .bg-bubbles li:nth-child(5) { left: 70%; }
        .bg-bubbles li:nth-child(6) { left: 80%; width: 120px; height: 120px; animation-delay: 3s; background-color: rgba(255, 255, 255, 0.2);}
        .bg-bubbles li:nth-child(7) { left: 32%; width: 160px; height: 160px; animation-delay: 7s;}
        .bg-bubbles li:nth-child(8) { left: 55%; width: 20px; height: 20px; animation-delay: 15s; animation-duration: 40s;}
        @keyframes square { 0% { transform: translateY(0) rotate(0deg); opacity: 1; border-radius: 50%; } 100% { transform: translateY(-1000px) rotate(720deg); opacity: 0; border-radius: 50%; } }
        
        .login-container { position: relative; z-index: 2; background: rgba(255, 255, 255, 0.95); border-radius: 20px; box-shadow: 0 15px 35px rgba(0, 0, 0, 0.25); width: 100%; max-width: 450px; padding: 40px; text-align: center; transform: translateY(0); transition: transform 0.4s, box-shadow 0.4s; overflow: hidden; }
        .login-container:hover { transform: translateY(-10px); box-shadow: 0 20px 45px rgba(0, 0, 0, 0.3); }

        .logo img { width: 300px; height: 300px; object-fit: contain; margin-bottom: -85px; margin-top: -85px; }
        .system-name { font-size: 28px; margin-top: 0px; color: #0c2461; font-weight: 700; }
        .system-desc { color: #4a69bd; font-size: 14px; font-weight: 500; margin-bottom: 20px; }
        .instruction-text { color: #333; font-size: 15px; margin-bottom: 15px; }

        .login-form { margin-top: 15px; }
        .form-group { position: relative; margin-bottom: 18px; }
        .form-group i { position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: #4a69bd; font-size: 18px; }
        .form-control { width: 100%; height: 55px; padding: 0 20px 0 50px; border: none; border-radius: 12px; background: #f0f7ff; font-size: 16px; color: #0c2461; box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.05); transition: all 0.3s; border: 2px solid transparent; }
        .form-control:focus { outline: none; border-color: #4a69bd; background: #fff; box-shadow: 0 5px 15px rgba(74, 105, 189, 0.1); }

        .btn-login { width: 100%; height: 55px; border: none; border-radius: 12px; background: linear-gradient(135deg, #43cea2 0%, #185a9d 100%); color: white; font-size: 18px; font-weight: 600; cursor: pointer; transition: all 0.4s; box-shadow: 0 8px 20px rgba(67, 206, 162, 0.4); position: relative; overflow: hidden; margin-top: 10px; margin-bottom: 15px; }
        .btn-login:hover { transform: translateY(-3px); box-shadow: 0 12px 25px rgba(67, 206, 162, 0.5); }
        .btn-login:active { transform: translateY(0); }
        .btn-login::after { content: ''; position: absolute; top: -50%; left: -60%; width: 20px; height: 200%; background: rgba(255, 255, 255, 0.4); transform: rotate(25deg); transition: all 0.5s; }
        .btn-login:hover::after { left: 120%; }

        .action-buttons { display: flex; gap: 15px; margin-top: 12px; }
        .action-btn { flex: 1; height: 50px; border-radius: 12px; font-size: 16px; font-weight: 500; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none; }
        .btn-guest { background: #0c2461; color: white; border: 2px solid #0c2461; }
        .btn-guest:hover { background: #1e3799; transform: translateY(-3px); color: white; }

        .security-indicator { display: flex; align-items: center; justify-content: center; gap: 10px; margin-top: 15px; padding: 12px; background: #f0f7ff; border-radius: 10px; font-size: 14px; color: #0c2461; }
        .security-indicator i { color: #43cea2; font-size: 18px; }
        .footer { margin-top: 35px; color: #4a69bd; font-size: 14px; line-height: 1.6; }
        .copyright { font-weight: 500; color: #0c2461; }
        .version { background: linear-gradient(to right, #43cea2, #185a9d); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-weight: 600; margin-top: 5px; }

        .toast-container { position: fixed; top: 20px; right: 20px; z-index: 1050; pointer-events: none; }
        .toast { min-width: 280px; border-radius: 12px; box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15); font-family: 'Poppins', sans-serif; opacity: 0; transition: opacity 0.4s ease-in-out, transform 0.4s ease-in-out; transform: translateX(100%); margin-bottom: 15px; pointer-events: auto; border: none; }
        .toast.show { opacity: 1; transform: translateX(0); }
        .toast-header { border-bottom: 1px solid rgba(0, 0, 0, 0.05); padding: 12px 15px; border-radius: 10px 10px 0 0; font-weight: 600; display: flex; align-items: center; gap: 10px; }
        .toast-body { padding: 15px; font-size: 15px; color: #333; }
        .toast.bg-success-custom { background-color: #e6ffed; color: #007a3e; border: 1px solid #99e6b3; }
        .toast.bg-success-custom .toast-header { background-color: #4CAF50; color: white; }
        .toast.bg-danger-custom { background-color: #ffe6e6; color: #cc0000; border: 1px solid #ff9999; }
        .toast.bg-danger-custom .toast-header { background-color: #f44336; color: white; }
        .btn-close-white { filter: brightness(0) invert(1); }

        .modal-header .btn-close { background: transparent url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23000'%3e%3cpath d='M.293.293a1 1 0 011.414 0L8 6.586 14.293.293a1 1 0 111.414 1.414L9.414 8l6.293 6.293a1 1 0 01-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 01-1.414-1.414L6.586 8 .293 1.707a1 1 0 010-1.414z'/%3e%3c/svg%3e") center/1em auto no-repeat; opacity: .5; }
        .modal-header .btn-close:hover { opacity: .75; }

        @media (max-width: 480px) {
            .login-container { padding: 30px 20px; }
            .system-name { font-size: 24px; }
            .action-buttons { flex-direction: column; gap: 12px; }
        }
    </style>
</head>
<body>
    <ul class="bg-bubbles">
        <li></li><li></li><li></li><li></li><li></li><li></li><li></li><li></li>
    </ul>

    <div class="toast-container"></div>

    <div class="login-container">
        <div class="logo">
            <a href="index.php">
                <img src="images/oldpmslogin.png" alt="O-LDPMS Logo">
            </a>
        </div>
        <p class="system-name">Forgot Password</p>
        <p class="system-desc">O-LDPMS | Online Lumber Dealer Permitting and Monitoring System</p>

        <?php if (!isset($_GET['code'])): ?>
            <form action="" id="forgotPasswordForm" class="login-form" method="post" role="form" autocomplete="off">
                <p class="instruction-text">Enter your email address to receive a verification code.</p>
                
                <input type="hidden" name="action_send_code" value="1">
                
                <div class="form-group">
                    <i class="fas fa-envelope"></i>
                    <input class="form-control" id="Email" name="email" type="email" placeholder="Your email address" value="<?php echo htmlspecialchars($_GET['email'] ?? ''); ?>" required autofocus />
                </div>
                
                <div class="g-recaptcha" data-sitekey="6LeTIY0sAAAAAJDzQT7Atu4lR7NsfUH07D8vNPxc" style="display: flex; justify-content: center; margin-bottom: 15px;"></div>
                
                <button type="submit" class="btn-login">
                    <i class="fas fa-paper-plane"></i> SEND CODE
                </button>
                
                <div class="action-buttons">
                    <a href="index.php" class="action-btn btn-guest">
                        <i class="fas fa-arrow-left"></i> Back to Login
                    </a>
                </div>
            </form>

        <?php else: ?>
            <form action="" id="verifyCodeForm" class="login-form" method="post" role="form" autocomplete="off">
                <p class="instruction-text">Enter the 6-digit verification code sent to your email.</p>
                
                <input type="hidden" name="action_verify_code" value="1">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($_GET['email'] ?? ''); ?>">
                
                <div class="form-group">
                    <i class="fas fa-hashtag"></i>
                    <input class="form-control" id="Code" name="code" type="text" placeholder="Verification code" required autofocus />
                </div>
                
                <button type="submit" class="btn-login">
                    <i class="fas fa-check-circle"></i> VERIFY CODE
                </button>
                
                <div class="action-buttons">
                    <a href="index.php" class="action-btn btn-guest">
                        <i class="fas fa-arrow-left"></i> Back to Login
                    </a>
                </div>
            </form>
        <?php endif; ?>

        <div class="security-indicator">
            <i class="fas fa-shield-alt"></i>
            <span>Secure 256-bit SSL Encrypted Connection</span>
        </div>
        <div class="footer">
            <p class="copyright">DENR CARAGA | © Copyright 2024</p>
            <p class="version">O-LDPMS Version B1.01</p>
        </div>
    </div>

    <?php $showModal = isset($_GET['showModal']) && $_GET['showModal'] == 'true'; ?>
    <div class="modal fade" id="passwordResetModal" tabindex="-1" aria-labelledby="passwordResetModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="passwordResetModalLabel">Reset Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="resetPasswordForm" action="" method="POST" onsubmit="return validatePasswordReset()">
                        <input type="hidden" name="action_reset_password" value="1">
                        
                        <div class="form-group mb-3">
                            <label for="newPassword" class="form-label" style="text-align: left; display: block;">New Password</label>
                            <input autocomplete="off" type="password" class="form-control" id="newPassword" name="newPassword" style="padding-left: 20px;" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="confirmPassword" class="form-label" style="text-align: left; display: block;">Confirm Password</label>
                            <input autocomplete="off" type="password" class="form-control" id="confirmPassword" name="confirmPassword" style="padding-left: 20px;" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="email" class="form-label" style="text-align: left; display: block;">Confirm Email</label>
                            <input autocomplete="off" type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['email'] ?? ($_GET['email'] ?? '')); ?>" style="padding-left: 20px;" readonly required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" form="resetPasswordForm" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // UI Interactions and Form Validation
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    if (this.parentNode.classList.contains('form-group')) {
                        this.parentNode.style.transform = 'scale(1.02)';
                    }
                });
                input.addEventListener('blur', function() {
                    if (this.parentNode.classList.contains('form-group')) {
                        this.parentNode.style.transform = 'scale(1)';
                    }
                });
            });

            // Button ripple effect
            const buttons = document.querySelectorAll('.btn-login, .action-btn');
            buttons.forEach(button => {
                button.addEventListener('click', function(e) {
                    const x = e.clientX - e.target.offsetLeft;
                    const y = e.clientY - e.target.offsetTop;
                    const ripple = document.createElement('span');
                    ripple.classList.add('ripple');
                    ripple.style.left = x + 'px';
                    ripple.style.top = y + 'px';
                    this.appendChild(ripple);
                    setTimeout(() => { ripple.remove(); }, 1000);
                });
            });

            // Handle URL parameters for Toasts
            const urlParams = new URLSearchParams(window.location.search);
            let shouldClearParams = false;

            if (urlParams.has('error')) {
                showToast('error', urlParams.get('error'));
                shouldClearParams = true;
            }
            if (urlParams.has('success')) {
                showToast('success', urlParams.get('success'));
                shouldClearParams = true;
            }

            // Clean URL after toasts
            if (shouldClearParams) {
                const newUrl = window.location.protocol + '//' + window.location.host + window.location.pathname + 
                (urlParams.has('code') ? '?code=' + urlParams.get('code') + '&email=' + urlParams.get('email') : '') +
                (urlParams.has('showModal') ? '?showModal=true&email=' + urlParams.get('email') : '');
                window.history.replaceState({}, document.title, newUrl);
            }

            // Show password reset modal automatically if flag is set
            <?php if ($showModal): ?>
                var passwordResetModal = new bootstrap.Modal(document.getElementById('passwordResetModal'));
                passwordResetModal.show();
            <?php endif; ?>
        });

        // Bootstrap 5 Toast function
        function showToast(type, message) {
            const toastContainer = document.querySelector('.toast-container');
            const toastElement = document.createElement('div');
            toastElement.classList.add('toast');
            toastElement.setAttribute('role', 'alert');
            toastElement.setAttribute('aria-live', 'assertive');
            toastElement.setAttribute('aria-atomic', 'true');
            toastElement.setAttribute('data-bs-autohide', 'true');
            toastElement.setAttribute('data-bs-delay', '5000'); 

            const headerBgClass = type === 'success' ? 'bg-success-custom' : 'bg-danger-custom'; 
            const titleText = type === 'success' ? 'Success' : 'Error';
            const iconClass = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'; 

            toastElement.innerHTML = `
                <div class="toast-header ${headerBgClass}">
                    <i class="${iconClass} toast-icon"></i>
                    <strong class="me-auto">${titleText}</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            `;

            toastContainer.appendChild(toastElement);

            const toast = new bootstrap.Toast(toastElement);
            toast.show();

            toastElement.addEventListener('hidden.bs.toast', function () {
                toastElement.remove();
            });
        }

        // Modal password reset validation check
        function validatePasswordReset() {
            var newPassword = document.getElementById('newPassword').value;
            var confirmPassword = document.getElementById('confirmPassword').value;

            if (newPassword !== confirmPassword) {
                showToast('error', 'Passwords do not match.');
                return false;
            }
            return true;
        }
    </script>
</body>
</html>
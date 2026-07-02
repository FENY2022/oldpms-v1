<?php
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
      
    if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
        exit;
    }

    include('../processphp/config.php');

    define('ADMIN_MAX_LOGIN_ATTEMPTS', 3);
    define('ADMIN_LOCKOUT_SECONDS', 180);

    function redirectWithLoginError($message) {
        $error = urlencode($message);
        echo "<script type='text/javascript'>location='login.php?error={$error}';</script>";
        exit();
    }

    function registerFailedLogin($message) {
        $_SESSION['admin_login_attempts'] = ($_SESSION['admin_login_attempts'] ?? 0) + 1;

        if ($_SESSION['admin_login_attempts'] >= ADMIN_MAX_LOGIN_ATTEMPTS) {
            $_SESSION['admin_lockout_time'] = time() + ADMIN_LOCKOUT_SECONDS;
            redirectWithLoginError('Too many failed attempts. Please try again after 3 minutes.');
        }

        $attempts_left = ADMIN_MAX_LOGIN_ATTEMPTS - $_SESSION['admin_login_attempts'];
        redirectWithLoginError($message . ' ' . $attempts_left . ' attempts remaining.');
    }

    function redirectByRole($roleId) {
        if ($roleId == '99') {
            header("location: index.php");
            exit();
        }

        if (in_array((string)$roleId, ['1', '2', '4', '7', '8', '9', '9.1', '10', '11', '12'], true)) {
            header("location: ../main/production/application.php");
            exit();
        }

        if (in_array((string)$roleId, ['12.5', '13', '14', '15', '16'], true)) {
            header("location: ../main/action.php");
            exit();
        }

        if ($roleId == '17') {
            header("location: ../main/records/action.php");
            exit();
        }

        if ($roleId == '19') {
            header("location: ../main/tableic.php");
            exit();
        }

        $_SESSION = array();
        session_destroy();
        echo "<script type='text/javascript'>alert('Unauthorized role. Please contact the administrator.');location='login.php';</script>";
        exit();
    }
    
    if (isset($_POST['btn'])) {

        // --- Account Lockout Check ---
        if (isset($_SESSION['admin_lockout_time']) && time() < $_SESSION['admin_lockout_time']) {
            $wait = $_SESSION['admin_lockout_time'] - time();
            redirectWithLoginError("Account locked due to too many failed attempts. Try again in $wait seconds.");
        }

        // --- Verify reCAPTCHA ---
        $recaptcha_secret = '6LeTIY0sAAAAAHPR6a4KnPDoFVaeu0Jb-0UoO37G';
        $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
        $verify_response = @file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($recaptcha_secret) . '&response=' . urlencode($recaptcha_response));
        $response_data = json_decode($verify_response);
        
        if (!$response_data || !$response_data->success) {
            registerFailedLogin('reCAPTCHA validation failed. Please check the box.');
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'];

        $query = $connection->prepare("SELECT * FROM denr_users WHERE username = :username LIMIT 1");
        $query->bindValue(':username', $username, PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            registerFailedLogin('Username and Password combination is wrong!');
        } else {
            $id = $result['user_id'];
            $hash =  $result['password'];
            $_SESSION["user_role_id"] =  $result['user_role_id'];
            $name =  $result['name'];
  
            if (password_verify($password, $result['password'])) {

                // Clear login attempts tracking
                unset($_SESSION['admin_login_attempts']);
                unset($_SESSION['admin_lockout_time']);
                session_regenerate_id(true);
            
                $_SESSION["loggedin"] = true;
                $_SESSION["user_id"] = $id;
                $_SESSION["username"] = $username;   
                $_SESSION["name"] = $name;
                $_SESSION["user_role_id"] =  $result['user_role_id'];

                redirectByRole($result['user_role_id']);
            } 
            else 
            {
                registerFailedLogin('Invalid Password.');
            }
        }
    }
?>

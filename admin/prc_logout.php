
<?php 
                             
                  $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ((int)($_SERVER['SERVER_PORT'] ?? 80) === 443);
                  ini_set('session.cookie_httponly', 1);
                  ini_set('session.cookie_secure', $is_https ? '1' : '0');
                  ini_set('session.cookie_samesite', 'Lax');

                  session_set_cookie_params([
                    'lifetime' => 0,
                    'path' => '/',
                    'secure' => $is_https,
                    'httponly' => true,
                    'samesite' => 'Lax'
                  ]);

                  if (session_status() == PHP_SESSION_NONE) {
                      session_start();
                      }
          
                    // if (isset($_POST['btn'])) {
                      
                                  
                             
// Initialize the session
if (session_status() == PHP_SESSION_NONE) {
  session_start();
 }
 
// Unset all of the session variables
$_SESSION = array();
 
// Destroy the session.
if (session_status() == PHP_SESSION_NONE) {
  session_start();
 }
 
// Redirect to login page
header("Location: login.php");
exit;
                                  

                                //   }
                  

                              ?>

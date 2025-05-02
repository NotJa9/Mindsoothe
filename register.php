<?php 
session_start();
include 'connect.php';

// For debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) && preg_match('/@usl\.edu\.ph$/', $email);
}

// Initialize session variables to prevent undefined array key notices
if (!isset($_SESSION['firstName'])) $_SESSION['firstName'] = '';
if (!isset($_SESSION['lastName'])) $_SESSION['lastName'] = '';
if (!isset($_SESSION['Department'])) $_SESSION['Department'] = '';
if (!isset($_SESSION['profile_image'])) $_SESSION['profile_image'] = 'images/blueuser.svg';

// Login Logic
if(isset($_POST['signIn'])){
    $email = trim(strtolower($_POST['email']));
    $password = $_POST['password'];
    $hashedPassword = md5($password); // md5 hash for students and MHP
    $loginSuccessful = false;
    
    // Try to log in as a student
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND password = ?");
    $stmt->bind_param("ss", $email, $hashedPassword);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0){
        $user = $result->fetch_assoc();
        
        // Set student session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['firstName'] = $user['firstName'] ?? '';
        $_SESSION['lastName'] = $user['lastName'] ?? '';
        $_SESSION['Department'] = $user['Department'] ?? '';
        $_SESSION['profile_image'] = $user['profile_image'] ?: 'images/blueuser.svg';
        $_SESSION['isLoggedIn'] = true;
        $_SESSION['user_type'] = 'student';

        // Log successful login
        error_log("Successful student login for user: " . $user['email']);
        console_log("Student login successful");
        
        echo "<script type='text/javascript'>
            window.location.href = 'gracefulThread.php';
            </script>";
        exit();
    } else {
        console_log("Student login failed: User not found or incorrect password");
    }
    
    // If not a student, try MHP login with md5 hash
    $stmt = $conn->prepare("SELECT * FROM MHP WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows === 1){
        $doctor = $result->fetch_assoc();
        
        // Compare hashed passwords
        if($hashedPassword === $doctor['password']) {
            // Set all possible session variables to prevent undefined array key notices
            $_SESSION['mhp_id'] = $doctor['id'];
            $_SESSION['user_id'] = $doctor['id']; // Add this as a fallback
            $_SESSION['firstName'] = $doctor['firstName'] ?? '';
            $_SESSION['lastName'] = $doctor['lastName'] ?? '';
            $_SESSION['mhp_name'] = ($doctor['firstName'] ?? '') . ' ' . ($doctor['lastName'] ?? '');

            $_SESSION['email'] = $doctor['email'] ?? ''; // Add email
            $_SESSION['Department'] = $doctor['Department'] ?? ''; // Add Department if it exists
            $_SESSION['counselor-image'] = $doctor['profile_image'] ?? 'images/blueuser.svg';
            $_SESSION['profile_image'] = $doctor['profile_image'] ?? 'images/blueuser.svg'; // For consistency
            $_SESSION['old_profile_image'] = $doctor['profile_image'] ?? 'images/blueuser.svg';
            $_SESSION['is_logged_in'] = true;
            $_SESSION['isLoggedIn'] = true; // Add both versions for consistency
            $_SESSION['user_type'] = 'mhp';
            
            // Log successful login
            error_log("Successful MHP login for user: " . $doctor['email']);
            console_log("MHP login successful");
            
            echo "<script type='text/javascript'>
                window.location.href = 'mhp_dashboard.php';
                </script>";
            exit();
        } else {
            console_log("MHP login failed: Incorrect password");
        }
    } else {
        console_log("MHP login failed: User not found");
    }
    
    // If not a student or MHP, try Admin login with PHP's password_verify
    $username = '';
    
    // Check if this is an admin email or just a username
    if (strpos($email, '@') !== false) {
        $username = explode('@', $email)[0]; // Extract username from email
    } else {
        $username = $email; // Assume it's just a username
    }
    
    $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0){
        $admin = $result->fetch_assoc();
        
        // Use password_verify for admin passwords which use PHP's secure hashing
        if(password_verify($password, $admin['password'])) {
            // Set admin session variables
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['username'] = $admin['username'];
            $_SESSION['firstName'] = $admin['firstName'] ?? '';
            $_SESSION['lastName'] = $admin['lastName'] ?? '';
            $_SESSION['isLoggedIn'] = true;
            $_SESSION['user_type'] = 'admin';
            
            // Log successful login
            error_log("Successful admin login for user: " . $admin['username']);
            console_log("Admin login successful");
            
            echo "<script type='text/javascript'>
                window.location.href = 'admin_dashboard.php';
                </script>";
            exit();
        } else {
            console_log("Admin login failed: Incorrect password");
        }
    } else {
        console_log("Admin login failed: User not found");
    }
    
    // If we reach here, no login was successful
    echo "<script type='text/javascript'>
        console.log('Login failed: No matching account found in any database');
        alert('Incorrect Email or Password');
        window.location.href = 'Login.html';
        </script>";
}

// Helper function for console logging
function console_log($message) {
    echo "<script>console.log('PHP: " . addslashes($message) . "');</script>";
}

$conn->close();
?>
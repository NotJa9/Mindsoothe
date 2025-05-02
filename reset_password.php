<?php
include 'connect.php';
session_start();

if (isset($_POST['reset'])) {
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    // Check if passwords match
    if ($newPassword !== $confirmPassword) {
        echo "<script type='text/javascript'>
                alert('Passwords do not match!');
                window.location.href = 'reset_password.html';
                </script>";
        exit();
    }

    $newPasswordHashed = md5($newPassword); // Encrypt the new password
    $email = $_SESSION['email'];
    $userType = $_SESSION['user_type'] ?? 'student'; // Default to student if not set

    // Update the password in the appropriate table based on user type
    if ($userType == 'mhp') {
        $updatePassword = "UPDATE MHP SET password='$newPasswordHashed' WHERE email='$email'";
    } else {
        $updatePassword = "UPDATE Users SET password='$newPasswordHashed' WHERE email='$email'";
    }

    if ($conn->query($updatePassword) === TRUE) {
        echo "<script type='text/javascript'>
                alert('Password has been reset. Please log in.');
                window.location.href = 'Login.html';
                </script>";
        // Clear the session
        session_unset();
        session_destroy();
        exit();
    } else {
        echo "Error updating password: " . $conn->error;
    }
}
?>
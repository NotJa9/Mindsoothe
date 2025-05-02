<?php
include 'connect.php';
require 'vendor/autoload.php'; // Only needed if using Composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

// Load .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

function sendOTP($email, $otp) {
    $mail = new PHPMailer(true);
    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['SMTP_USERNAME']; // Load the SMTP username from the .env file
        $mail->Password = $_ENV['SMTP_PASSWORD']; // Load the SMTP password from the .env file
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        //Recipients
        $mail->setFrom('pablojaninekarla@gmail.com', 'Mindsoothe');
        $mail->addAddress($email);

        //Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset OTP';
        $mail->Body="<b>Dear User,</b>
            <p>We received a request to reset your password.</p>
            <p>Your OTP code is <b> $otp </b></p>
            <p>Please do not share it with anyone.</p>
            <br><br>
            <p>With regards,</p>
            <b>Mindsoothe</b>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        return false;
    }
}

if (isset($_POST['resetPassword'])) {
    $email = $_POST['email'];

    // Validate email syntax
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format!";
        exit();
    }

    // Check if email exists in Users table (students)
    $checkStudentEmail = "SELECT * FROM Users WHERE email='$email'";
    $studentResult = $conn->query($checkStudentEmail);

    // Check if email exists in MHP table
    $checkMHPEmail = "SELECT * FROM MHP WHERE email='$email'";
    $mhpResult = $conn->query($checkMHPEmail);

    if ($studentResult->num_rows > 0 || $mhpResult->num_rows > 0) {
        // Email exists in either table
        $otp = rand(100000, 999999); // Generate a 6-digit OTP
        
        // Store OTP, email, and user type in the session
        $_SESSION['otp'] = $otp;
        $_SESSION['email'] = $email;
        
        // Store user type for later use in password update
        if ($studentResult->num_rows > 0) {
            $_SESSION['user_type'] = 'student';
        } else {
            $_SESSION['user_type'] = 'mhp';
        }
 
        if (sendOTP($email, $otp)) {
            echo "<script type='text/javascript'>
                    alert('OTP has been sent to your email.');
                    window.location.href = 'enter_otp.html';
                  </script>";
            exit();
        } else {
            echo "<script type='text/javascript'>
                    alert('Failed to send OTP.');
                  </script>";
        }
    } else {
        echo "<script type='text/javascript'>
                alert('Email address not found!');
              </script>";
    }
}
?>
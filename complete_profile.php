<?php
session_start();
include 'connect.php'; // Include database connection

// Check if user is coming from the registration form
if (!isset($_SESSION['temp_email'])) {
    // Redirect to login if accessed directly
    header("Location: Login.html");
    exit();
}

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get session data
    $email = $_SESSION['temp_email'];
    $firstName = $_SESSION['temp_firstName'];
    $lastName = $_SESSION['temp_lastName'];
    $picture = $_SESSION['temp_picture'] ?? 'images/blueuser.svg'; // Default image if not set
    
    // Get form data
    $studentId = $_POST['idNumber']; // This will map to Student_id in the database
    $department = $_POST['department']; // This will map to Department
    $course = $_POST['program']; // This will map to Course
    $year = $_POST['year']; // Year as text input
    
    // Validate password
    if (strlen($_POST['password']) < 8) {
        echo "<script>alert('Password must be at least 8 characters long.'); window.history.back();</script>";
        exit();
    }
    
    if ($_POST['password'] !== $_POST['confirmPassword']) {
        echo "<script>alert('Passwords do not match.'); window.history.back();</script>";
        exit();
    }
    
    // Use MD5 hashing as requested
    $password = md5($_POST['password']); 
    
    // Check if the user already exists (additional safeguard)
    $checkQuery = "SELECT * FROM users WHERE email = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        // User already exists - might happen if they try to register twice in separate tabs
        echo "<script>alert('Account already exists. Please login instead.'); window.location.href='Login.html';</script>";
        exit();
    }
    
    // Generating a 6-digit OTP
    $otp = sprintf("%06d", mt_rand(1, 999999));
    
    // Insert into database with correct column names that match your database schema
    $insertQuery = "INSERT INTO users (Student_id, firstName, lastName, email, password, profile_image, Department, Course, Year, otp) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("isssssssss", $studentId, $firstName, $lastName, $email, $password, $picture, $department, $course, $year, $otp);
    
    if ($stmt->execute()) {
        // Clear temporary session data
        unset($_SESSION['temp_email']);
        unset($_SESSION['temp_firstName']);
        unset($_SESSION['temp_lastName']);
        unset($_SESSION['temp_picture']);
        
        // Set permanent session data
        $_SESSION['email'] = $email;
        $_SESSION['firstName'] = $firstName;
        $_SESSION['lastName'] = $lastName;
        $_SESSION['picture'] = $picture;
        $_SESSION['Student_id'] = $studentId;
        $_SESSION['Department'] = $department;
        $_SESSION['Course'] = $course;
        $_SESSION['Year'] = $year;
        
        // Redirect to main application
        header("Location: gracefulThread.php");
        exit();
    } else {
        // If there was an error with the database insertion
        $errorMsg = "Error: " . $stmt->error;
        
        // Log the error
        error_log("Database insertion error in complete_profile.php: " . $errorMsg);
        
        // Display a user-friendly error message
        echo "<div style='text-align: center; margin-top: 50px;'>";
        echo "<h3>Registration Error</h3>";
        echo "<p>We encountered an error while setting up your account.</p>";
        echo "<p>Technical details: " . htmlspecialchars($errorMsg) . "</p>";
        echo "<p><a href='Login.html' class='btn btn-primary'>Return to Login</a></p>";
        echo "</div>";
    }
    
    $stmt->close();
} else {
    // If accessed without POST data
    header("Location: Login.html");
    exit();
}
?>
<?php
include("auth.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Validate input
    if (!isset($_POST['responses']) || !isset($_POST['userId'])) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
        exit;
    }

    $responses = json_decode($_POST['responses'], true);
    $userId = intval($_POST['userId']); // Use the posted userId

    if (count($responses) !== 10) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid number of responses']);
        exit;
    }

    // Calculate total score
    $totalScore = array_sum($responses);

    // Use prepared statements to prevent SQL injection
    $query = "INSERT INTO phq9_responses 
              (user_id, question_1, question_2, question_3, question_4, question_5, 
               question_6, question_7, question_8, question_9, question_10, total_score) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $conn->error]);
        exit;
    }

    // Bind parameters
    $stmt->bind_param(
        'iiiiiiiiiiii',
        $userId,
        $responses[0],
        $responses[1],
        $responses[2],
        $responses[3],
        $responses[4],
        $responses[5],
        $responses[6],
        $responses[7],
        $responses[8],
        $responses[9],
        $totalScore
    );

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Response saved successfully', 'totalScore' => $totalScore]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save response: ' . $stmt->error]);
    }
    exit;
}

$sql = "SELECT id, fname, lname, department, profile_image 
        FROM MHP";
$result = $conn->query($sql);

// Store MHP data in an array
$mhps = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $mhps[] = $row;
    }
} else {
    echo "No mental health professionals found.";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mental Wellness Companion</title>
</head>
<style>
        * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: Arial, sans-serif;
        background-color: #f5f5f5;
    }

    .container {
        display: flex;
    }

    .sidebar {
        width: 250px;
        background-color: #f4f4f4;
        position: fixed; /* Make the sidebar fixed on the left */
        top: 0; /* Stick to the top */
        left: 0; /* Stick to the left */
        height: 100vh; /* Full height of the viewport */
        display: flex;
        flex-direction: column;
        border-right: 1px solid #ddd;
        overflow-y: auto; /* Add scrolling if the sidebar content overflows */
        z-index: 1000; /* Ensure it's on top of other content */
    }

    .intro {
    background-color: #fefefe;
    display: flex;
    flex-direction: row;
    justify-content: center;
    width: 100%;
    }
    
    .intro .phq-intro {
      background-color: #fefefe;
      width: 2440px;
      height: 1024px;
      position: relative;
    }
    
    .intro .ellipse {
      position: absolute;
      width: 60px;
      height: 55px;
      top: 15px;
      left: 1134px;
      object-fit: cover;
    }
    
    .intro .text-wrapper {
      position: absolute;
      top: 32px;
      left: 1201px;
      font-size: 14px;
      font-family: "Poppins", Helvetica;
      font-weight: 400;
      color: #000000;
      letter-spacing: 0;
      line-height: normal;
    }
    
    .intro .group {
      height: 41px;
    }
    
    .intro .overlap-group-wrapper {
      width: 386px;
      height: 41px;
    }
    
    .intro .div {
      position: absolute;
      top: 8px;
      left: 51px;
      font-family: "Poppins", Helvetica;
      font-weight: 400;
      color: #747474;
      font-size: 16px;
      letter-spacing: 0;
      line-height: normal;
    }
    
    .intro .mental-wellness {
      position: absolute;
      width: 772px;
      top: 125px;
      left: 382px;
      font-family: "Poppins", Helvetica;
      font-weight: 700;
      color: #1CABE3;
      font-size: 40px;
      letter-spacing: 0;
      line-height: 40px;
    }
    
    .intro .span {
      color: #000000;
    }
    
    .intro .text-wrapper-2 {
      color: #1CABE3;
    }
    
    .intro .frame {
      position: absolute;
      width: 29px;
      height: 25px;
      top: 24px;
      left: 25px;
    }
    
    .intro .mini {
      position: absolute;
      width: 76px;
      height: 121px;
      top: 153px;
      left: 1px;
    }
    
    .intro .date-picker {
      display: flex;
      flex-direction: column;
      width: 340px;
      height: 300px;
      align-items: flex-end;
      gap: 20px;
      padding: 20px;
      position: absolute;
      top: 94px;
      left: 1082px;
      background-color: #f9f9f9;
      border-radius: 8px 8px 0px 0px;
    }
    
    .intro .base-calendar {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 12px;
      position: relative;
      align-self: stretch;
      width: 100%;
      flex: 0 0 auto;
      background-color: #f9f9f9;
    }
    
    .intro .date-header {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      position: relative;
      align-self: stretch;
      width: 100%;
      flex: 0 0 auto;
    }
    
    .intro .chevron {
      position: relative;
      width: 20px;
      height: 20px;
    }
    
    .intro .div-2 {
      display: inline-flex;
      align-items: flex-start;
      gap: 4px;
      position: relative;
      flex: 0 0 auto;
    }
    
    .intro .december-wrapper {
      display: inline-flex;
      align-items: flex-start;
      gap: 8px;
      position: relative;
      flex: 0 0 auto;
    }
    
    .intro .december {
      position: relative;
      width: fit-content;
      margin-top: -1px;
      font-family: "Inter", Helvetica;
      font-weight: 500;
      color: #1CABE3;
      font-size: 16px;
      letter-spacing: 0;
      line-height: 20px;
      white-space: nowrap;
    }
    
    .intro .element {
      position: relative;
      width: fit-content;
      margin-top: -1px;
      font-family: "Inter", Helvetica;
      font-weight: 500;
      color: #000000;
      font-size: 16px;
      letter-spacing: 0;
      line-height: 20px;
      white-space: nowrap;
    }
    
    .intro .frame-2 {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 4px;
      position: relative;
      align-self: stretch;
      width: 100%;
      flex: 0 0 auto;
    }
    
    .intro .frame-3 {
      display: flex;
      align-items: flex-start;
      position: relative;
      align-self: stretch;
      width: 100%;
      flex: 0 0 auto;
    }
    
    .intro .work-day {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 8px 2px;
      position: relative;
      flex: 1;
      flex-grow: 1;
    }
    
    .intro .th {
      position: relative;
      width: 28px;
      margin-top: -1px;
      font-family: "Inter", Helvetica;
      font-weight: 400;
      color: #000000;
      font-size: 12px;
      text-align: center;
      letter-spacing: 0;
      line-height: 16px;
    }
    
    .intro .th-2 {
      color: #000000;
      position: relative;
      width: 28px;
      margin-top: -1px;
      font-family: "Inter", Helvetica;
      font-weight: 400;
      font-size: 12px;
      text-align: center;
      letter-spacing: 0;
      line-height: 16px;
    }
    
    .intro .th-3 {
      color: #000000;
      font-family: "Inter", Helvetica;
      font-weight: 400;
      font-size: 12px;
      text-align: center;
      letter-spacing: 0;
      line-height: normal;
      white-space: nowrap;
    }

    .intro .th-4 {
      color: #000000;
      font-family: "Inter", Helvetica;
      font-weight: 400;
      font-size: 12px;
      text-align: center;
      letter-spacing: 0;
      line-height: normal;
      white-space: nowrap;
    }

    .intro .th-5 {
      color: #000000;
      font-family: "Inter", Helvetica;
      font-weight: 400;
      font-size: 12px;
      text-align: center;
      letter-spacing: 0;
      line-height: normal;
      white-space: nowrap;
    }

    .calendar-header th {
      color: #000000;
    }
    
    .intro .calendar-date {
      position: relative;
      flex: 1;
      flex-grow: 1;
      height: 32px;
    }
    
    .intro .calendar-date-2 {
      display: flex;
      height: 32px;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 8px;
      position: relative;
      flex: 1;
      flex-grow: 1;
    }
    
    .intro .text-wrapper-3 {
      font-weight: 400;
      color: #000000;
      position: relative;
      width: fit-content;
      margin-top: -1px;
      font-family: "Inter", Helvetica;
      font-size: 12px;
      text-align: center;
      letter-spacing: 0;
      line-height: normal;
      white-space: nowrap;
    }
    
    .intro .element-2 {
      font-weight: 400;
      color: #000000;
      position: relative;
      width: fit-content;
      margin-top: -1px;
      font-family: "Inter", Helvetica;
      font-size: 12px;
      text-align: center;
      letter-spacing: 0;
      line-height: normal;
      white-space: nowrap;
    }
    
    .intro .ellipse-2 {
      position: absolute;
      width: 4px;
      height: 4px;
      top: 4px;
      left: 35px;
      background-color: #1CABE3;
      border-radius: 2px;
    }
    
    .intro .element-wrapper {
      border-radius: 1000px;
      display: flex;
      height: 32px;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 8px;
      position: relative;
      flex: 1;
      flex-grow: 1;
    }
    
    .intro .element-3 {
      font-weight: 600;
      color: #000000;
      position: relative;
      width: fit-content;
      margin-top: -1px;
      font-family: "Inter", Helvetica;
      font-size: 12px;
      text-align: center;
      letter-spacing: 0;
      line-height: normal;
      white-space: nowrap;
    }
    
    .intro .calendar-date-3 {
      border-radius: 8px;
      display: flex;
      height: 32px;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 8px;
      position: relative;
      flex: 1;
      flex-grow: 1;
    }
    
    .intro .text-wrapper-4 {
      font-weight: 400;
      color: #000000;
      position: relative;
      width: fit-content;
      margin-top: -1px;
      font-family: "Inter", Helvetica;
      font-size: 12px;
      text-align: center;
      letter-spacing: 0;
      line-height: normal;
      white-space: nowrap;
    }
    
    .intro .ellipse-3 {
      width: 6px;
      height: 6px;
      top: 3px;
      left: 34px;
      border-radius: 3px;
      border: 1px solid;
      border-color: #1CABE3;
      position: absolute;
      background-color: #1CABE3;
    }
    
    .intro .calendar-date-4 {
      border-radius: 8px 0px 0px 8px;
      display: flex;
      height: 32px;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 8px;
      position: relative;
      flex: 1;
      flex-grow: 1;
    }
    
    .intro .ellipse-4 {
      width: 4px;
      height: 4px;
      top: 4px;
      left: 35px;
      border-radius: 2px;
      position: absolute;
      background-color: #1CABE3;
    }
    
    .intro .div-wrapper {
      border-radius: 0px 8px 8px 0px;
      display: flex;
      height: 32px;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 8px;
      position: relative;
      flex: 1;
      flex-grow: 1;
    }
    
    .intro .overlap-2 {
      position: absolute;
      width: 872px;
      height: 749px;
      top: 202px;
      left: 133px;
    }
    
    .intro .frame-4 {
      display: inline-flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 10px;
      position: absolute;
      top: 1px;
      left: 180px;
      opacity: 0.75;
    }
    
    .intro .subs-free-card {
      position: relative;
      width: 293px;
      height: 177px;
      margin-top: -3px;
      margin-bottom: -5px;
      margin-left: -4px;
      margin-right: -4px;
    }
    
    .intro .frame-5 {
      display: inline-flex;
      flex-direction: column;
      align-items: center;
      gap: 18px;
      position: absolute;
      top: 19px;
      left: 24px;
    }
    
    .intro .frame-6 {
      display: inline-flex;
      flex-direction: column;
      align-items: flex-start;
      justify-content: center;
      gap: 16px;
      position: relative;
      flex: 0 0 auto;
    }
    
    .intro .frame-7 {
      display: inline-flex;
      align-items: flex-start;
      gap: 16px;
      position: relative;
      flex: 0 0 auto;
    }
    
    .intro .dr-pic {
      position: relative;
      width: 50px;
      height: 50px;
      object-fit: cover;
    }
    
    .intro .frame-8 {
      display: inline-flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 6px;
      position: relative;
      flex: 0 0 auto;
    }
    
    .intro .text-wrapper-5 {
      position: relative;
      width: fit-content;
      margin-top: -1px;
      font-family: "Poppins", Helvetica;
      font-weight: 400;
      color: #000000;
      font-size: 16px;
      letter-spacing: 0;
      line-height: normal;
      white-space: nowrap;
    }
    
    .intro .licensed-mental {
      position: relative;
      width: fit-content;
      font-family: "Poppins", Helvetica;
      font-weight: 300;
      color: #000000;
      font-size: 10px;
      letter-spacing: 0;
      line-height: normal;
      white-space: nowrap;
    }
    
    .intro .text-wrapper-6 {
      position: relative;
      width: fit-content;
      font-family: "Poppins", Helvetica;
      font-weight: 300;
      color: #000000;
      font-size: 12px;
      letter-spacing: 0;
      line-height: normal;
      white-space: nowrap;
    }
    
    .intro .frame-9 {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 5px;
      padding: 5px 10px;
      position: relative;
      flex: 0 0 auto;
      background-color: #1CABE3;
      border-radius: 5px;
      box-shadow: 0px 1px 4px #00000040;
    }
    
    .intro .text-wrapper-7 {
      position: relative;
      width: fit-content;
      margin-top: -1px;
      font-family: "Poppins", Helvetica;
      font-weight: 300;
      color: #000000;
      font-size: 12px;
      letter-spacing: 0;
      line-height: normal;
      white-space: nowrap;
    }
    
    .intro .text-wrapper-8 {
      position: relative;
      width: 67px;
      margin-top: -1px;
      font-family: "Poppins", Helvetica;
      font-weight: 300;
      color: #000000;
      font-size: 12px;
      letter-spacing: 0;
      line-height: normal;
    }
    
    .intro .frame-10 {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      padding: 5px 10px;
      position: relative;
      flex: 0 0 auto;
      background-color: #ffffff;
      border-radius: 5px;
      box-shadow: 0px 1px 4px #00000040;
    }
    
    .intro .text-wrapper-9 {
      position: relative;
      width: fit-content;
      margin-top: -1px;
      font-family: "Poppins", Helvetica;
      font-weight: 300;
      color: #1CABE3;
      font-size: 14px;
      letter-spacing: 0;
      line-height: normal;
      white-space: nowrap;
    }
    
    .intro .frame-11 {
      display: inline-flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 10px;
      position: absolute;
      top: 194px;
      left: 0;
      opacity: 0.75;
    }
    
    .intro .image {
      position: relative;
      width: 58px;
      height: 58px;
      margin-top: -3px;
      margin-bottom: -5px;
      margin-left: -4px;
    }
    
    .intro .frame-12 {
      display: inline-flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 10px;
      position: absolute;
      top: 387px;
      left: 2px;
      opacity: 0.75;
    }
    
    .intro .frame-13 {
      display: inline-flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 10px;
      position: absolute;
      top: 580px;
      left: 4px;
      opacity: 0.75;
    }
    
    .intro .frame-14 {
      display: inline-flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 10px;
      position: absolute;
      top: 0;
      left: 297px;
      opacity: 0.75;
    }
    
    .intro .frame-15 {
      display: inline-flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 10px;
      position: absolute;
      top: 193px;
      left: 293px;
      opacity: 0.75;
    }
    
    .intro .frame-16 {
      display: inline-flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 10px;
      position: absolute;
      top: 386px;
      left: 295px;
      opacity: 0.75;
    }
    
    .intro .frame-17 {
      display: inline-flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 10px;
      position: absolute;
      top: 579px;
      left: 297px;
      opacity: 0.75;
    }
    
    .intro .frame-18 {
      display: inline-flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 10px;
      position: absolute;
      top: 0;
      left: 587px;
      opacity: 0.75;
    }
    
    .intro .frame-19 {
      display: inline-flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 10px;
      position: absolute;
      top: 193px;
      left: 583px;
      opacity: 0.75;
    }
    
    .intro .frame-20 {
      display: inline-flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 10px;
      position: absolute;
      top: 386px;
      left: 585px;
      opacity: 0.75;
    }
    
    .intro .frame-21 {
      display: inline-flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 10px;
      position: absolute;
      top: 579px;
      left: 587px;
      opacity: 0.75;
    }
    
    .intro .overlap-wrapper {
      position: absolute;
      width: 720px;
      height: 600px;
      top: 74px;
      left: 78px;
    }
    
    .intro .overlap-3 {
      position: relative;
      width: 716px;
      height: 600px;
      background-color: #f9f9f9;
      border-radius: 10px;
      box-shadow: 0px 1px 8px #000000bf;
      left: 37%;
      top: 20%;
    }
    
    .intro .instructions-for {
      position: absolute;
      top: 93px;
      left: 80px;
      font-family: "Poppins", Helvetica;
      font-weight: 400;
      color: #000000;
      font-size: 16px;
      letter-spacing: 0;
      line-height: normal;
    }
    
    .intro .text-wrapper-10 {
      font-weight: 500;
    }
    
    .intro .text-wrapper-11 {
      font-weight: 300;
    }
    
    .intro .patient-health {
      position: absolute;
      top: 38px;
      left: 123px;
      font-family: "Poppins", Helvetica;
      font-weight: 600;
      color: #1CABE3;
      font-size: 32px;
      letter-spacing: 0;
      line-height: 32px;
      white-space: nowrap;
    }

    .start-btn {
      color: white !important;
      border: none;
      border-radius: 10px;
      padding: 10px 20px;
      font-size: 16px;
      font-family: Arial, sans-serif;
      cursor: pointer;
      text-align: center;
      display: flex;
      width: 80px;
      height: 34px;
      align-items: center;
      justify-content: center;
      position: absolute;
      top: 515px;
      left: 585px;
      background-color: #1CABE3;
    }
    
    .start-btn:hover {
      background-color: #1697c8;
      color: white !important;
    }
    
    .intro .text-wrapper-12 {
      position: relative;
      width: fit-content;
      margin-top: -6px;
      margin-bottom: -4px;
      font-family: "Poppins", Helvetica;
      font-weight: 600;
      color: #ffffff;
      font-size: 16px;
      letter-spacing: 0;
      line-height: normal;
    }
    
    
    .intro .frame-22 {
      display: inline-flex;
      flex-direction: column;
      height: 32px;
      align-items: center;
      position: absolute;
      top: 14px;
      left: 37px;
    }
    
    .intro .frame-23 {
      display: inline-flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 9px;
      position: relative;
      flex: 0 0 auto;
      margin-bottom: -0.96px;
    }
    
    .intro .text-wrapper-13 {
      position: relative;
      width: 90px;
      margin-top: -1px;
      font-family: "Poppins", Helvetica;
      font-weight: 500;
      color: #1CABE3;
      font-size: 16px;
      letter-spacing: 0;
      line-height: normal;
    }
    
    .intro .line {
      position: relative;
      width: 266px;
      height: 1px;
      object-fit: cover;
    }
    
    .intro .frame-wrapper {
      display: flex;
      flex-direction: column;
      width: 340px;
      align-items: center;
      gap: 1px;
      position: absolute;
      top: 59px;
      left: 0;
    }
    
    .intro .frame-24 {
      display: inline-flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 2px;
      position: relative;
      flex: 0 0 auto;
    }
    
    .intro .frame-25 {
      display: flex;
      width: 338px;
      align-items: center;
      gap: 15px;
      padding: 3px 35px;
      position: relative;
      flex: 0 0 auto;
    }
    
    .intro .img {
      position: relative;
      width: 40px;
      height: 40px;
      object-fit: cover;
    }
    
    .intro .text-wrapper-14 {
      position: relative;
      width: fit-content;
      font-family: "Poppins", Helvetica;
      font-weight: 400;
      color: #000000;
      font-size: 16px;
      letter-spacing: 0;
      line-height: normal;
    }
    
    .intro .eeccf-fbb-b {
      position: relative;
      width: 40px;
      height: 34.78px;
      object-fit: cover;
    }
    
    .intro .rectangle {
      position: absolute;
      width: 384px;
      height: 41px;
      top: -2718px;
      left: 891px;
    }
    
    .modal-title {
        font-size: 1.5rem;
      }
      
      .modal-body h6 {
        font-size: 1.2rem;
        margin-bottom: 1.5rem;
      }

      .menu-content {
          display: flex;
          flex-direction: column;
      }

      .menu-item {
          display: flex;
          align-items: center;
          padding: 15px 20px;
          text-decoration: none;
          color: #333;
          transition: background-color 0.3s, padding-left 0.3s;
          font-size: 16px;
      }

      .menu-item:hover {
          background-color: #e2e2e2;
      }

      .menu-item.active {
          background-color: #d0e4f5; /* Highlight color for active item */
          border-left: 4px solid #007bff; /* A left border to indicate active section */
      }
      .clicked {
          background: rgba(217, 217, 217, 0.45);
          box-shadow: 0px 4px 5px 0px rgba(0, 0, 0, 0.25) inset;
      }

      .menu-icon {
          width: 24px;
          height: 24px;
      }

      .menu-text {
          margin-left: 15px;
          transition: opacity 0.3s, margin-left 0.3s;
      }

      .user-profile {
          display: flex;
          align-items: center;
          padding: 20px;
          background-color: #f4f4f4;
          border-top: 1px solid #ddd;
          cursor: pointer;
          text-decoration: none;
          color: #333;
      }

      .user-avatar {
          border-radius: 50%;
          width: 40px;
          height: 40px;
          margin-right: 15px;
      }

      .username {
          font-size: 16px;
          transition: opacity 0.3s, margin-left 0.3s;
      }

      .Logout {
          display: block;
          padding: 15px 20px;
          color: #333;
          text-decoration: none;
          text-align: center;
          transition: background-color 0.3s;
      }

      .Logout:hover {
          background-color: #e2e2e2;
      }

      .UserAcc {
          margin-top: auto;
          display: flex;
          flex-direction: column;
      }

      .logo {
          padding: 10px;
          display: flex;
          justify-content: space-evenly;
          align-items: center;
      }

      .logo img {
          width: 235px; /* Adjust the size of the logo */
          height: 80px;
          border-bottom: 1px solid #ddd;
      }

      .summary-table {
        width: 100%;
        margin-top: 1rem;
      }
      .summary-table th, .summary-table td {
        padding: 0.5rem;
        border-bottom: 1px solid #dee2e6;
      }
      .summary-table th {
        text-align: left;
      }

      .modal-content {
          border-radius: 15px;
          border: none;
      }
      
      .text-primary {
          color: #1CABE3 !important;
      }
      
      .btn-primary {
          background-color: #1CABE3;
          border-color: #1CABE3;
          color: white;
      }
      
      .btn-primary:hover {
          background-color: #1697c8;
          border-color: #1697c8;
          color: white;
      }
      
      .btn-outline-primary {
          color: #1CABE3;
          border-color: #1CABE3;
      }
      
      .btn-outline-primary:hover {
          background-color: #1CABE3;
          border-color: #1CABE3;
      }
      
      .option {
          
          padding: 8px 16px;
          border-radius: 6px;
          cursor: pointer;
          transition: all 0.3s ease;
          display: flex;
          align-items: center;
          gap: 4px;
      }
    
      .option input[type="radio"] {
          cursor: pointer;
          margin: 0;
          display: none;
      }
      
      .option input[type="radio"]:checked + label {
          background-color: #1CABE3;
          color: white;
      }
      
      .option label {
        background-color: #f8f9fa;
          cursor: pointer;
          margin: 0;
          flex: 1;
      }

      .options-container {
          display: flex;
          flex-direction: column;
          gap: 8px;
      }
      
      .question-number {
          color: #000000;
          font-weight: 500;
          font-size: 1.1rem;
          min-width: 25px;
      }
      
      .question-text {
          font-size: 1.3rem;
          font-weight: 500;
          line-height: 1.5;
      }

      .question-header {
          align-items: flex-start;
      }

      .results-container {
          background-color: #f8f9fa;
          padding: 20px;
          border-radius: 10px;
      }

      .score-section {
          text-align: center;
          padding-bottom: 20px;
          border-bottom: 1px solid #dee2e6;
      }

      .summary-table {
          display: flex;
          flex-direction: column;
          gap: 10px;
      }

      .summary-row {
          display: flex;
          justify-content: space-between;
          padding: 8px;
          background-color: white;
          border-radius: 6px;
      }

      .summary-row span:first-child {
          font-weight: 500;
      }
      
      .score-section h4 .text-primary,
      .score-section h5 .text-primary {
          color: #1CABE3 !important;
          font-weight: 600;
      }
      .hidden {
          display: none;
      }
      .content-area {
      flex: 1;
      padding: 0 20px;
  }

  .profile-card {
      background-color: #ffffff;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
      margin-left: 240px;
      width: 80%;
  }

  .profile-header {
      display: flex;
      align-items: center;
  }

  .profile-header img {
      width: 150px;
      height: 200px;
      border-radius: 10px;
      object-fit: cover;
      margin-right: 20px;
  }

  .profile-details {
      flex: 1;
  }

  .profile-details h2 {
      margin-bottom: 10px;
  }

  .profile-details p {
      margin-bottom: 10px;
      color: #555;
  }

  .profile-details span {
      font-weight: bold;
  }

  .profile-schedule-container {
      width: 70%;
      background-color: #ffffff;
      border-radius: 10px;
      box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
      overflow: hidden;
      padding: 1px;
      margin-left: 240px;
      width: 80%;
  }

  .tabs {
      display: flex;
      justify-content: space-around;
      background-color: #1CABE3;
      border-bottom: 1px solid #ddd;
      
  }

  .tab {
      width: 33.33%;
      text-align: center;
      padding: 15px 0;
      cursor: pointer;
      font-weight: bold;
      color: #333;
  }

  .tab.active {
      background-color: #ddd;
      color: #ffffff;
      border-bottom: 3px solid #ddd;
  }

  .tab-content {
      padding: 20px;
      width: 80%;
  }

  .tab-content h3 {
      font-size: 18px;
      margin-bottom: 10px;
      color: #333;
  }

  .tab-content p {
      font-size: 16px;
      color: #666;
  }

  .tab-content hr {
      border: none;
      border-bottom: 1px solid #ddd;
      margin: 20px 0;
  }

  .content {
      display: none;
  }

  .tab-content .content.active {
      display: block;
  }
  /* Success Message Animation */
  .success-message {
      display: none;
      position: fixed;
      top: 20px;
      right: 20px;
      background-color: #67DCC9;
      color: white;
      padding: 15px 25px;
      border-radius: 5px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.2);
      z-index: 9999;
      font-size: 16px;
      font-weight: 500;
      transform: translateX(100%);
      opacity: 0;
      transition: all 0.3s ease-in-out;
  }

  .success-message.show {
      transform: translateX(0);
      opacity: 1;
  }

  .success-message.hide {
      transform: translateX(100%);
      opacity: 0;
  }

  .write-review-section {
          background-color: #f9f9f9;
          padding: 20px;
          border-radius: 8px;
          margin-bottom: 20px;
          box-shadow: 0px 2px 8px rgba(0, 0, 0, 0.1);
          width: 130%;
    
      }

      .star-rating {
          display: inline-flex;
          flex-direction: row-reverse;
          gap: 4px;
      }

      .star-rating input {
          display: none;
      }

      .star-rating label {
          font-size: 25px;
          color: #ddd;
          cursor: pointer;
      }

      .star-rating label:before {
          content: '★';
      }

      .star-rating input:checked ~ label {
          color: gold;
      }

      .star-rating label:hover,
      .star-rating label:hover ~ label {
          color: gold;
      }

      .review-input {
          width: 100%;
          min-height: 100px;
          margin: 10px 0;
          padding: 10px;
          border: 1px solid #ddd;
          border-radius: 4px;
          resize: vertical;
      }

      .submit-review {
          background-color: #67DCC9;
          color: white;
          border: none;
          padding: 10px 20px;
          border-radius: 4px;
          cursor: pointer;
          font-size: 14px;
      }

      .submit-review:hover {
          background-color: #50b3a3;
      }

          /* Scoped CSS for Calendar in Schedule Tab */
          #schedule-content .calendar-container {
              background-color: #fff;
              border-radius: 8px;
              box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
              padding: 20px;
              margin: 20px;
              width: 120%;
          }

          #schedule-content .calendar-header {
              display: flex;
              justify-content: space-between;
              align-items: center;
              margin-bottom: 15px;
          }

          #schedule-content .calendar-header button {
              background-color: #e0e0e0;
              border: none;
              border-radius: 5px;
              padding: 5px 10px;
              cursor: pointer;
              font-size: 16px;
          }

          #schedule-content .month-year {
              font-weight: bold;
              font-size: 18px;
          }

          #schedule-content .calendar-body {
              display: flex;
              flex-direction: column;
              align-items: center;
          }

          #schedule-content .calendar-weekdays {
              display: flex;
              justify-content: space-between;
              width: 100%;
              margin-bottom: 9px;
              color: #666666;
              font-weight: bold;
          }

          #schedule-content .calendar-weekdays div {
              flex-basis: 13%;
              text-align: center;
          }

          #schedule-content .calendar-dates {
              display: grid;
              grid-template-columns: repeat(7, 1fr);
              gap: 8px;
              width: 100%;
          }

          .date {
              padding: 10px;
              margin-bottom: 8px;
              border-radius: 5px;
              text-align: center;
              cursor: pointer;
          }
          .date:hover {
              background-color: #67DCC9;
              color: white;
          }


          /* Time Selection Modal Styles */
          .modal {
          display: none;
          position: fixed;
          z-index: 1;
          left: 0;
          top: 0;
          width: 90%;
          height: 100%;
          overflow: auto;
          background-color: rgb(0,0,0);
          background-color: rgba(0,0,0,0.4);
          padding-top: 60px;
          }

          .modal-content {
              background-color: #fefefe;
              margin: auto; /* Center modal horizontally */
              padding: 20px;
              border: 1px solid #888;
              width: 80%;
              max-width: 300px;
              border-radius: 10px;
              text-align: center;
              margin-top: 20%; /* Move modal lower */
          }

          .close {
              color: #aaa;
              float: right;
              font-size: 28px;
              font-weight: bold;
          }

          .close:hover,
          .close:focus {
              color: black;
              text-decoration: none;
              cursor: pointer;
          }

          .time-option {
              padding: 8px; /* Reduced padding for smaller size */
              margin: 5px 0;
              border: 1px solid #ddd;
              border-radius: 5px;
              cursor: pointer;
              transition: background-color 0.3s;
          }

          .time-option:hover {
              background-color: #67DCC9;
              color: white;
          }
        
          .time-option.booked {
              background-color: #FFA7AC;
              color: white;
              cursor: not-allowed;
          }

          /* Responsive Adjustments */
          @media (max-width: 768px) {
              #schedule-content .calendar-header button {
                  font-size: 14px;
              }

              #schedule-content .calendar-weekdays div,
              #schedule-content .calendar-dates .date {
                  font-size: 14px;
              }
          }

          @media (max-width: 480px) {
              #schedule-content .calendar-header {
                  flex-direction: column;
                  align-items: center;
              }

              #schedule-content .calendar-header button {
                  margin-bottom: 10px;
              }
          }
  </style>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <body>
      <div class="container">
          <!-- Left Sidebar -->
            <div id="sidebar" class="sidebar fixed top-0 left-0 h-screen bg-white shadow-lg z-10">
          <!-- Logo Section -->
          <div class="flex items-center p-6 border-b">
              <div class="w-15 h-10 rounded-full flex items-center justify-center">
                <a href="#"><img src="images/Mindsoothe(2).svg" alt="Mindsoothe Logo"></a>
            </div>
        </div>

        <!-- Menu Items -->
        <nav class="mt-6">
            <a href="#" class="menu-item flex items-center px-6 py-3" data-section="dashboard" id="gracefulThreadItem">
                <img src="images/gracefulThread.svg" alt="Graceful Thread" class="w-5 h-5">
                <span class="menu-text ml-3">Graceful Thread</span>
            </a>
            <a href="#" class="menu-item active flex items-center px-6 py-3 text-gray-600" data-section="appointments" id="MentalWellness">
                <img src="images/Vector.svg" alt="Mental Wellness Companion" class="w-5 h-5">
                <span class="menu-text ml-3">Mental Wellness Companion</span>
            </a>
        </nav>

        <!-- User Profile and Logout Section -->
        <div class="absolute bottom-0 w-full border-t">
            <!-- User Profile -->
            <a href="#" class="menu-item flex items-center px-6 py-4 text-gray-600">
                <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="Profile Image" class="w-8 h-8 rounded-full">
                <span class="menu-text ml-3"><?php echo htmlspecialchars($fullName); ?></span>
            </a>

            <!-- Logout -->
            <a href="landingpage.html" class="menu-item flex items-center px-6 py-4 text-red-500 hover:text-red-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span class="menu-text ml-3">Logout</span>
            </a>
        </div>
    </div>
    </div>

    
          <div class="content-area">
            <div class="profile-card">
                <div class="profile-header">
                    <img src="images/emily.jpg" alt="Emily Roberts">
                    <div class="profile-details">
                        <h2>Emily Roberts</h2>
                        <p><span>Licensed Mental Health Counselor</span></p>
                        <p>MAEd | MindCare Psychiatry Associates</p>
                        <p>Rating: ⭐⭐⭐⭐⭐ (50 Patient Satisfaction Ratings)</p>
                    </div>
                </div>
            </div>

            <div class="profile-schedule-container">
                <div class="tabs">
                    <div class="tab active" id="profile-tab">Profile</div>
                    <div class="tab" id="schedule-tab">Schedule</div>
                    <div class="tab" id="reviews-tab">Rating and Reviews</div>
                </div>
                <div class="tab-content">
                    <div id="profile-content" class="content active">
                        <h3>Qualifications and experience</h3>
                        <p>Details about qualifications and experience go here...</p>
                        <hr>
                        <h3>Education</h3>
                        <p>Details about education go here...</p>
                    </div>

                    <!-- Schedule Content with Calendar -->
                    <div id="schedule-content" class="content">
                        <div class="availability-bar">
                            <div class="booked-indicator"></div>
                        </div>
                        <div class="calendar-container">
<!-- Calendly inline widget begin -->
<div class="calendly-inline-widget" data-url="https://calendly.com/pablojaninekarla/30min?hide_gdpr_banner=1" style="min-width:320px;height:700px;"></div>
<script type="text/javascript" src="https://assets.calendly.com/assets/external/widget.js" async></script>
<!-- Calendly inline widget end -->
                        </div>
                    </div>

                    <div id="successMessage" class="success-message">
                             Appointment booked successfully!
                    </div>

                    <!-- Reviews Content -->
                    <div id="reviews-content" class="content">
                        <div class="write-review-section">
                            <h3>Write a Review</h3>
                            <form id="reviewForm" onsubmit="submitReview(event)">
                                <div class="star-rating">
                                    <input type="radio" id="star5" name="rating" value="5" />
                                    <label for="star5"></label>
                                    <input type="radio" id="star4" name="rating" value="4" />
                                    <label for="star4"></label>
                                    <input type="radio" id="star3" name="rating" value="3" />
                                    <label for="star3"></label>
                                    <input type="radio" id="star2" name="rating" value="2" />
                                    <label for="star2"></label>
                                    <input type="radio" id="star1" name="rating" value="1" />
                                    <label for="star1"></label>
                                </div>
                                <textarea class="review-input" placeholder="Write your review here..." required></textarea>
                                <button type="submit" class="submit-review">Submit Review</button>
                            </form>
                        </div>
                    
                             

                        <!-- Time Selection Modal -->
                        <div id="timeModal" class="modal">
                        <div class="modal-content">
                            <span id="closeModal" class="close">&times;</span>
                            <h3>Select Time for Appointment</h3>
                            <div id="timeSlotContainer">
                                <!-- Time slots will be added dynamically -->
                            </div>
                        </div>
                    </div>
   <script>
              // Tab Switching Logic
              function switchTab(clickedTab, contentId) {
                  // Remove 'active' class from all tabs
                  const tabs = document.querySelectorAll('.tab');
                  tabs.forEach(tab => tab.classList.remove('active'));

                  // Add 'active' class to the clicked tab
                  clickedTab.classList.add('active');

                  // Hide all content sections
                  const contents = document.querySelectorAll('.content');
                  contents.forEach(content => content.classList.remove('active'));

                  // Show the clicked tab's content
                  const contentToShow = document.getElementById(contentId);
                  if (contentToShow) {
                      contentToShow.classList.add('active');
                  }
              }

              // Event listeners for tab switching
              document.getElementById('profile-tab').addEventListener('click', function() {
                  switchTab(this, 'profile-content');
              });

              document.getElementById('schedule-tab').addEventListener('click', function() {
                  switchTab(this, 'schedule-content');
              });

              document.getElementById('reviews-tab').addEventListener('click', function() {
                  switchTab(this, 'reviews-content');
              });

              // Global variables
              let currentMonth = new Date().getMonth();
              let currentYear = new Date().getFullYear();
              let selectedDay, selectedTime;
              const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
              const daysInMonth = (month, year) => new Date(year, month + 1, 0).getDate();

              // Object to store booked times
              let bookedTimes = {};

              // Function to update the calendar
              function updateCalendar() {
                  const monthYear = document.getElementById("monthYear");
                  const calendarDates = document.getElementById("calendarDates");

                  monthYear.textContent = `${monthNames[currentMonth]} ${currentYear}`;
                  calendarDates.innerHTML = '';

                  const firstDay = new Date(currentYear, currentMonth, 1).getDay();
                  const days = daysInMonth(currentMonth, currentYear);

                  for (let i = 0; i < firstDay; i++) {
                      calendarDates.innerHTML += `<div class="date"></div>`;
                  }

                  for (let day = 1; day <= days; day++) {
                      calendarDates.innerHTML += `<div class="date" data-day="${day}">${day}</div>`;
                  }
              }

              function openTimeModal(day) {
                selectedDay = day;
                const modal = document.getElementById("timeModal");
                const timeOptionsContainer = document.getElementById("timeSlotContainer");
                modal.style.display = "block";

          // Clear existing time slots
          timeOptionsContainer.innerHTML = '';

          const fullDate = `${currentYear}-${(currentMonth + 1).toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;

          // Define available time slots
          const timeSlots = [
              "09:00AM-10:00AM",
              "10:00AM-11:00AM",
              "11:00AM-12:00PM",
              "02:00PM-03:00PM",
              "03:00PM-04:00PM",
              "04:00PM-05:00PM"
          ];

          // Only create and add available time slots
          timeSlots.forEach(time => {
              if (!isTimeBooked(fullDate, time)) {
                  const timeOption = document.createElement('div');
                  timeOption.classList.add('time-option');
                  timeOption.setAttribute('data-time', time);
                  timeOption.textContent = time;
                  timeOption.onclick = () => confirmAppointment(time);
                  timeOptionsContainer.appendChild(timeOption);
              }
          });
      }

      function isTimeBooked(date, time) {
          return bookedTimes[date] && bookedTimes[date].includes(time);
      }

      function confirmAppointment(time) {
          selectedTime = time;
          const fullDate = `${currentYear}-${(currentMonth + 1).toString().padStart(2, '0')}-${selectedDay.toString().padStart(2, '0')}`;
          const confirmation = confirm(`Are you sure you want to book an appointment on ${monthNames[currentMonth]} ${selectedDay}, ${currentYear} at ${time}?`);
          
          if (confirmation) {
              // Save the appointment
              if (!bookedTimes[fullDate]) {
                  bookedTimes[fullDate] = [];
              }
              bookedTimes[fullDate].push(time);

              alert(`Your appointment is booked for ${monthNames[currentMonth]} ${selectedDay}, ${currentYear} at ${time}.`);
              closeTimeModal();
              
              // Save to server
              saveAppointment(fullDate, time);
          }
      }

      function saveAppointment(appointmentDate, appointmentTime) {
          const appointmentData = {
              appointmentDate: appointmentDate,
              appointmentTime: appointmentTime
          };

          fetch('save_appointmentD.php', {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/json',
              },
              body: JSON.stringify(appointmentData)
          })
          .then(response => response.json())
          .then(data => {
              if (data.success) {
                  const successMessage = document.getElementById('successMessage');
                  successMessage.style.display = 'block';
                  // Add small delay to ensure display:block is processed
                  setTimeout(() => {
                      successMessage.classList.add('show');
                  }, 10);
                  
                  setTimeout(() => {
                      successMessage.classList.add('hide');
                      setTimeout(() => {
                          successMessage.style.display = 'none';
                          successMessage.classList.remove('show', 'hide');
                      }, 300);
                  }, 3000);
              } else {
                  alert(data.message || 'Error saving appointment');
              }
          })
          .catch((error) => {
              console.error('Error:', error);
              alert('An error occurred while booking the appointment');
          });
      }
      function closeTimeModal() {
          document.getElementById("timeModal").style.display = "none";
      }
              
              function closeTimeModal() {
                  document.getElementById("timeModal").style.display = "none";
              }

              document.getElementById('closeModal').addEventListener('click', function() {
        document.getElementById('timeModal').style.display = 'none';
      });

              // Function to update time slot display

              function updateTimeSlotDisplay(date, time) {
          const timeOptions = document.querySelectorAll(".time-option");
          timeOptions.forEach(option => {
              if (option.getAttribute('data-time') === time) {
                  option.remove();  // Remove the booked time slot
              }
          });
      }
              // Event listeners
              document.querySelector('.prev-month').addEventListener('click', function() {
                  currentMonth--;
                  if (currentMonth < 0) {
                      currentMonth = 11;
                      currentYear--;
                  }
                  updateCalendar();
              });

              document.querySelector('.next-month').addEventListener('click', function() {
                  currentMonth++;
                  if (currentMonth > 11) {
                      currentMonth = 0;
                      currentYear++;
                  }
                  updateCalendar();
              });

              document.getElementById('calendarDates').addEventListener('click', function(e) {
                  if (e.target.classList.contains('date') && e.target.textContent) {
                      const selectedDay = e.target.getAttribute('data-day');
                      openTimeModal(selectedDay);
                  }
              });

              // Function to load booked appointments from server

              function loadBookedAppointments() {
                  fetch('get_booked_appointments.php')
                  .then(response => response.json())
                  .then(data => {
                      bookedTimes = data;
                      updateCalendar(); // Refresh the calendar to reflect booked times
                  })
                  .catch((error) => {
                      console.error('Error loading booked appointments:', error);
                  });
              }

              // Initial calls
              updateCalendar();
              loadBookedAppointments();
          </script>
          <script>
                document.addEventListener('DOMContentLoaded', function() {
                  const timeOptions = document.querySelectorAll('.time-option');
                  
                  timeOptions.forEach(option => {
                      option.addEventListener('click', function() {
                          if (this.classList.contains('booked')) {
                              alert('This time slot is already booked. Please select another time.');
                          } else {
                              // Handle selection of available time slot
                              console.log('Selected time:', this.dataset.time);
                              // Add your logic here to proceed with the booking
                          }
                      });
                  });
              });
          </script>
          <script>
              function submitReview(event) {
          event.preventDefault();
          
          // Get the selected rating
          const rating = document.querySelector('input[name="rating"]:checked');
          if (!rating) {
              alert('Please select a rating');
              return;
          }

          // Get the review text
          const reviewText = document.querySelector('.review-input').value;
          if (!reviewText.trim()) {
              alert('Please write a review');
              return;
          }

          // Create new review element
          const reviewDiv = document.createElement('div');
          reviewDiv.className = 'review';
          
          // Get the current user's photo and name from the top navbar
          const userPhoto = document.querySelector('.user-avatar').src;
          const userName = document.querySelector('.username').textContent;

          reviewDiv.innerHTML = `
              <img src="${userPhoto}" alt="${userName}" class="reviewer-photo">
              <div class="review-info">
                  <h4>${userName}</h4>
                  <div class="rating">
                      ${Array(Number(rating.value)).fill('★').join('')}
                  </div>
                  <p>${reviewText}</p>
              </div>
          `;

          // Add the new review at the top of the reviews list
          const reviewsSection = document.querySelector('#reviews-content');
          const firstReview = reviewsSection.querySelector('.review');
          reviewsSection.insertBefore(reviewDiv, firstReview);

          // Reset the form
          document.getElementById('reviewForm').reset();

          // Show success message
          alert('Your Review is Submitted');
      }
    </script>
            
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script src="sidebarnav.js"></script>


</body>
</html>
<?php
include('includes/db_connection.php'); // Adjust the path if necessary
include('includes/otp_functions.php'); // Adjust the path if necessary

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate OTP
    if (empty($_POST['otp']) || empty($_POST['email'])) {
        echo "OTP and email are required.";
        exit();
    }

    $otp_code = $_POST['otp'];
    $email = $_POST['email'];

    // Fetch the attorney_id based on email
    $stmt = $conn->prepare("SELECT attorney_id FROM attorney WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $attorney = $result->fetch_assoc();
        $attorney_id = $attorney['attorney_id'];

        // Validate OTP
        if (validateOTP($attorney_id, $otp_code)) {
            session_start();
            $_SESSION['attorney_id'] = $attorney_id;
            header("Location: view_appointments.php");
            exit();
        } else {
            echo '<script>alert("Invalid or expired OTP."); window.location.href = "request_otp.php";</script>';
        }
    } else {
        echo "No attorney found with this email.";
    }

    $stmt->close();
}

$conn->close();
?>

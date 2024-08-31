<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

include('includes/db_connection.php');
include('includes/otp_functions.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = $_POST['email'];

    $stmt = $conn->prepare("SELECT attorney_id FROM attorney WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $attorney = $result->fetch_assoc();
        $attorney_id = $attorney['attorney_id'];

        $otp = createOTP($attorney_id, $email);

        // Calculate expiration time and store in session
        $expires_at = date("Y-m-d H:i:s", strtotime("+3 minutes"));
        $_SESSION['otp_expires_at'] = $expires_at;

        // Set up PHP Mailer
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Gmail SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'cedrickarnigo1723@gmail.com'; // Your Gmail address
            $mail->Password = 'dzkt nwsb rryc hvtk'; // Your password or App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Use TLS
            $mail->Port = 587; // TCP port for TLS

            // Recipients
            $mail->setFrom('cedrickarnigo1723@gmail.com', 'Your Attorney OTP');
            $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Your OTP Code';
            $mail->Body    = 'Your OTP code is: <strong>' . htmlspecialchars($otp) . '</strong>';

            // Send the email
            $mail->send();

            // Set success message and redirect
            $_SESSION['message'] = "Please check your email address for your OTP (one-time-password).";
            header("Location: otp_form.php?email=" . urlencode($email));
            exit();
        } catch (Exception $e) {
            echo '<script>alert("Message could not be sent. Mailer Error: ' . htmlspecialchars($mail->ErrorInfo) . '"); window.location.href = "request_otp.php";</script>';
            exit();
        }

    } else {
        echo '<script>alert("No attorney found with that email address."); window.location.href = "request_otp.php";</script>';
        exit();
    }
}
?>

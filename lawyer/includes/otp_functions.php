<?php
function generateOTP($length = 6) {
    $otp = '';
    for ($i = 0; $i < $length; $i++) {
        $otp .= random_int(0, 9);  // Use random_int() for cryptographic security
    }
    return $otp;
}

function createOTP($attorney_id, $email) {
    global $conn;

    $otp = generateOTP();
    $expires_at = date("Y-m-d H:i:s", strtotime("+3 minutes")); // Set expiration to 3 minutes from now

    $stmt = $conn->prepare("INSERT INTO attorney_otp (attorney_id, otp_code, expires_at) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $attorney_id, $otp, $expires_at);
    $stmt->execute();
    $stmt->close();

    // Store expiration time in session
    $_SESSION['otp_expires_at'] = $expires_at;

    return $otp;
}


function validateOTP($attorney_id, $otp_code) {
    global $conn;

    $current_time = date("Y-m-d H:i:s"); // Get current time

    $stmt = $conn->prepare("SELECT otp_id FROM attorney_otp WHERE attorney_id = ? AND otp_code = ? AND expires_at > ? AND is_used = 0");
    $stmt->bind_param("iss", $attorney_id, $otp_code, $current_time);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $otp = $result->fetch_assoc();
        $stmt = $conn->prepare("UPDATE attorney_otp SET is_used = 1 WHERE otp_id = ?");
        $stmt->bind_param("i", $otp['otp_id']);
        $stmt->execute();

        return true;
    } else {
        return false;
    }
}


function getAppointments($attorney_id) {
    global $conn;

    $stmt = $conn->prepare("SELECT title, appointment_date, description FROM appointment WHERE attorney_id = ? ORDER BY appointment_date DESC");
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }

    $stmt->bind_param("i", $attorney_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $appointments = [];
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }

    $stmt->close();

    return $appointments;
}
?>

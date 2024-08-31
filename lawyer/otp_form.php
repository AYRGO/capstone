<?php
session_start();

// Set OTP expiration duration (in seconds)
$otp_expiration_duration = 3 * 60; // 3 minutes in seconds
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php
    if (isset($_SESSION['message'])) {
        echo '<div class="max-w-md mx-auto bg-green-100 p-4 mt-4 rounded shadow-md text-center">';
        echo '<p>' . $_SESSION['message'] . '</p>';
        echo '<div id="countdown" class="mt-4 text-red-500 text-center"></div>';
        echo '</div>';
        // Clear the message from the session
        unset($_SESSION['message']);
    }
    ?>

    <div class="max-w-md mx-auto bg-white p-6 mt-10 rounded shadow-md">
        <h2 class="text-2xl font-bold text-center mb-4">Verify OTP</h2>
        <form method="post" action="process_otp_verification.php">
            <input required type="hidden" name="email" value="<?php echo htmlspecialchars($_GET['email']); ?>">
            <label for="otp" class="block text-sm font-medium text-gray-700">OTP Code</label>
            <input type="text" id="otp" name="otp" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" required>
            <button type="submit" class="mt-4 bg-blue-500 text-white px-4 py-2 rounded">Verify OTP</button>
        </form>
    </div>

    <script>
    // Get the current time and the expiration time
    var now = new Date().getTime();
    var expirationTime = now + <?php echo $otp_expiration_duration * 1000; ?>; // Set expiration time

    function updateCountdown() {
        var now = new Date().getTime();
        var timeLeft = expirationTime - now;

        if (timeLeft <= 0) {
            document.getElementById('countdown').innerText = "OTP has expired.";
            return;
        }

        var minutes = Math.floor(timeLeft / (1000 * 60));
        var seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);

        // Display the countdown
        document.getElementById('countdown').innerText =
            "Time left: " + minutes + "m " + seconds + "s";

        // Update the countdown every second
        setTimeout(updateCountdown, 1000);
    }

    // Start the countdown
    updateCountdown();
    </script>
</body>
</html>

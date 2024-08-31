<?php
// Start a secure session
session_start([
    'cookie_secure' => true,    // Only send cookie over HTTPS
    'cookie_httponly' => true,  // Prevent JavaScript access to session cookie
    'use_strict_mode' => true   // Enforce strict session ID mode
]);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "legalaid";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Capture the input
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Prepare and execute the query to check credentials using prepared statements
    $stmt = $conn->prepare("SELECT user_id, role_as FROM users WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_id = $row['user_id'];
        $role_as = $row['role_as'];

        // Insert into login history
        $history_stmt = $conn->prepare("INSERT INTO admin_login_history (user_id) VALUES (?)");
        $history_stmt->bind_param("i", $user_id);
        $history_stmt->execute();
        $history_stmt->close();

        // Update the last_login timestamp
        $update_stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
        $update_stmt->bind_param("i", $user_id);
        $update_stmt->execute();
        $update_stmt->close();

        // Regenerate session ID after successful login to prevent session fixation
        session_regenerate_id(true);

        // Set session variables
        $_SESSION['admin_logged_in'] = true;  // Indicate that the admin is logged in
        $_SESSION['username'] = $username;    // Store the username in the session
        $_SESSION['role_as'] = $role_as;      // Store the role in the session
        $_SESSION["show_login_alert"] = true; // Show login alert

        setcookie('last_check', 0, time() + (86400 * 30), "/"); // Set a cookie

        // Redirect based on role
        if ($role_as == 1) {
            header("Location: /web/superadmin/superadmin.php");
        } else {
            header("Location: /web/dashboard/dashboard.php");
        }
    } else {
        // Invalid credentials, show an error and redirect back to login
        $_SESSION["login_error"] = "Invalid credentials";
        echo "<script type='text/javascript'>alert('" . $_SESSION["login_error"] . "');</script>";
        header("Location: login.php");
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio,line-clamp,container-queries"></script>
    <link rel="stylesheet" href="login.css">
    <script>
        tailwind.config = {
          theme: {
            extend: {
              colors: {
                clifford: '#da373d',
              }
            }
          }
        }
    </script>
    <script type="text/javascript">
        window.history.forward();
        function noBack() { window.history.forward(); }
    </script>
</head>
<body onload="noBack();" onpageshow="if (event.persisted) noBack();" onunload="">
    <div class="bg-[#EEEFEE] flex justify-center items-center h-screen">
        <!-- Left: Image -->
        <div class="w-1/2 h-screen hidden lg:block">
            <img src="tingey-injury-law-firm-6sl88x150Xs-unsplash.jpg" alt="Placeholder Image" class="object-cover w-full h-full">
        </div>
        <!-- Right: Login Form -->
        <div class="lg:p-36 md:p-52 sm:20 p-8 w-full lg:w-1/2">
            <h1 class="text-2xl font-semibold mb-4 text-[#572C14]">Login</h1>
            <form action="login.php" method="POST">
                <!-- Username Input -->
                <div class="mb-4">
                    <label for="username" class="block text-gray-600">Username</label>
                    <input type="text" id="username" name="username" class="w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:border-blue-500" autocomplete="off">
                </div>
                <!-- Password Input -->
                <div class="mb-4">
                    <label for="password" class="block text-gray-600">Password</label>
                    <input type="password" id="password" name="password" class="w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:border-blue-500" autocomplete="off">
                </div>
                <!-- Login Button -->
                <button type="submit" class="border-2 border-[#572C14] hover:bg-[#d3d3d3] text-[#572C14] font-semibold rounded-md py-2 px-4 w-full">Login</button>
            </form>
            <!-- Link to OTP Request -->
            <div class="mt-4">
                <a href="../lawyer/request_otp.php" class="text-[#572C14] hover:underline">If you are an Attorney: Request OTP</a>
            </div>
        </div>
    </div>
</body>
</html>

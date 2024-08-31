<?php
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

// Fetch user details
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    $stmt = $conn->prepare("SELECT user_id, username, role_as FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        echo "User not found.";
        exit();
    }

    $stmt->close();
} else {
    echo "Invalid user ID.";
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $role_as = $_POST['role_as'];
    $password = $_POST['password'];

    // Update query based on whether a password is provided
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE users SET username = ?, role_as = ?, password = ? WHERE user_id = ?");
        $stmt->bind_param("sisi", $username, $role_as, $hashed_password, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET username = ?, role_as = ? WHERE user_id = ?");
        $stmt->bind_param("sii", $username, $role_as, $user_id);
    }

    if ($stmt->execute()) {
        echo "User updated successfully.";
        header("Location: superadmin.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

    <header class="text-white py-4 text-center" style="background-color: #5c0017;">
        <h1 class="text-2xl font-bold">Edit User</h1>
    </header>

    <div class="container mx-auto px-4 py-8">
        <div class="bg-white shadow-md rounded-lg p-6">
            <form method="post" class="space-y-4">
                <div>
                    <label for="username" class="block text-gray-700">Username:</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required class="w-full mt-1 px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label for="role_as" class="block text-gray-700">Role:</label>
                    <select id="role_as" name="role_as" required class="w-full mt-1 px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="0" <?php echo $user['role_as'] == 0 ? 'selected' : ''; ?>>Admin</option>
                        <option value="1" <?php echo $user['role_as'] == 1 ? 'selected' : ''; ?>>Superadmin</option>
                    </select>
                </div>

                <div>
                    <label for="password" class="block text-gray-700">New Password (leave blank to keep current password):</label>
                    <input type="password" id="password" name="password" class="w-full mt-1 px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="flex space-x-4">
                    <input type="submit" value="Update User" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 cursor-pointer">
                    <button type="button" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600" onclick="window.location.href='superadmin.php';">Back</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>

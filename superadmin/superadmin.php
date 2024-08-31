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

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // When user logs out
  if (isset($_POST['signout'])) {
    // Update the last_check cookie with the id of the last seen form
    setcookie('last_check', $_SESSION['last_seen_form_id'], time() + (86400 * 30), "/"); // 86400 = 1 day

    // remove all session variables
    session_unset();

    // destroy the session
    session_destroy();

    // delete the cookie
    setcookie("username", "", time() - 3600, "/"); // 3600 seconds = 1 hour

    // redirect to login page
    header("Location: http://localhost/web/login/login.php");
    exit();
  }
}

if (!isset($_SESSION["username"])) {
  // if not logged in, redirect to login page
  header("Location: http://localhost/web/login/login.php");
  exit();
} else {
  // set a cookie that expires in 1 hour
  setcookie("username", $_SESSION["username"], time() + 3600, "/");

  // if the login alert session variable is set, show the alert and then unset the variable
  if (isset($_SESSION["show_login_alert"])) {
    echo '<script type="text/javascript">';
    echo 'alert("Login Successfully");';
    echo '</script>';
    unset($_SESSION["show_login_alert"]);
  }
}
?>




<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio,line-clamp,container-queries"></script>
  <link rel="stylesheet" href="dashboard.css">
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
</head>

<body class="bg-[#EEEFEE]">
  <nav class="bg-[#5c0017]">
    <div class="mx-auto max-w-7xl px-2 sm:px-6 lg:px-8">
      <div class="relative flex h-16 items-center justify-between">
        <div class="absolute inset-y-0 left-0 flex items-center sm:hidden">
          <!-- Mobile menu button-->
          <button type="button" class="relative inline-flex items-center justify-center rounded-md p-2 text-gray-400 hover:bg-gray-700 hover:text-white focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white" aria-controls="mobile-menu" aria-expanded="false">
            <span class="absolute -inset-0.5"></span>
            <span class="sr-only">Open main menu</span>

            <svg class="block h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>

            <svg class="hidden h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div class="flex flex-1 items-center justify-center sm:items-stretch sm:justify-start">
          <div class="flex flex-shrink-0 items-center">
            <img class="h-12 w-auto" src="Logo.png" alt="Your Company" id="logo">
          </div>
          <div class="hidden sm:ml-6 sm:block">
            <div class="flex space-x-4 pt-1 ">

            </div>
          </div>
        </div>
        <div class="absolute inset-y-0 right-0 flex items-center pr-2 sm:static sm:inset-auto sm:ml-6 sm:pr-0">
          <!-- Notification Button -->
          <button id="notificationButton" type="button" class="relative rounded-full bg-gray-800 p-1 text-gray-400 hover:text-white focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800">
            <span class="absolute -inset-1.5"></span>
            <span class="sr-only">View notifications</span>
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
            </svg>
            <!-- Notification Badge -->
            <span id="notificationBadge" class="absolute top-0 right-0 px-2 py-1 text-xs font-bold leading-none text-red-100 transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full hidden">0</span>
          </button>

          <!-- Dropdown Content -->
          <div id="dropdownContent" class="hidden absolute mt-2 right-0 w-72 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 overflow-auto max-h-60 transition duration-300 ease-in-out transform">
            <div class="py-2 px-4" role="menu" aria-orientation="vertical" aria-labelledby="options-menu">
              <!-- Notification Items Here -->
            </div>
          </div>

          <!-- Notification Script -->
          <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
          <script>
            let notificationCount = 0;

            // Check for new notifications every 10 seconds
            setInterval(function() {
              $.get('check_notification.php', function(data) {
                let newNotifications = Number(data);
                if (newNotifications > 0) {
                  notificationCount += newNotifications;
                  $('#notificationBadge').text(notificationCount).removeClass('hidden');
                }
              });
            }, 10000); // 10-second interval

            // Fetch form data and show/hide dropdown when notification button is clicked
            $('#notificationButton').click(function(event) {
              event.stopPropagation(); // Prevent the event from propagating
              $.get('../dashboard/fetch_form_notif.php', function(data) {
                $('#dropdownContent div').html(data);
                $('#dropdownContent').toggleClass('hidden');
              });

              // Reset notification count and update badge text
              notificationCount = 0;
              $('#notificationBadge').text('').addClass('hidden');

              // Send a request to the server to reset the notification count
              $.get('reset_notification.php');
            });

            // Hide the dropdown when clicking anywhere else on the page
            $(document).click(function() {
              $('#dropdownContent').addClass('hidden');
            });

            // When user logs out
            $('#signout').click(function() {
              // Get the id of the latest form seen by the user
              $.get('get_max_form_id.php', function(data) {
                let maxFormId = Number(data);

                // Update the last_check cookie with the id of the last seen form
                document.cookie = 'last_check=' + maxFormId + '; expires=' + new Date(new Date().getTime() + 86400 * 30 * 1000).toUTCString() + '; path=/';
              });
            });
          </script>


          <!--------------------------------->


          <!-- Profile dropdown -->
          <div class="relative ml-3">
            <div>
              <button type="button" class="relative flex rounded-full bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800" id="user-menu-button" aria-expanded="false" aria-haspopup="true">
                <span class="absolute -inset-1.5"></span>
                <span class="sr-only">Open user menu</span>
                <img class="h-8 w-8 rounded-full" src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="">
              </button>
            </div>


            <div class="hidden absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" tabindex="-1">
              <form method="post" action="">
                <a href="#" class="block px-4 py-2 text-sm text-gray-700" role="menuitem" tabindex="-1" id="user-menu-item-0">Your Profile</a>
                <a href="#" class="block px-4 py-2 text-sm text-gray-700" role="menuitem" tabindex="-1" id="user-menu-item-1">Settings</a>
                <button type="submit" name="signout" id="signout" class="block px-4 py-2 text-sm text-gray-700" role="menuitem" tabindex="-1" id="user-menu-item-2">Sign out</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile menu, show/hide based on menu state. -->
    <div class="hidden" id="mobile-menu">
      <div class="space-y-1 px-2 pb-3 pt-2 p-2">

        <a href="#" class="bg-gray-900 text-white block rounded-md px-3 py-2 text-base font-medium" aria-current="page">Dashboard</a>
        <a href="#" class="text-gray-300 hover:bg-gray-700 hover:text-white block rounded-md px-3 py-2 text-base font-medium">Cases</a>
        <a href="#" class="text-gray-300 hover:bg-gray-700 hover:text-white block rounded-md px-3 py-2 text-base font-medium">Files</a>
        <a href="#" class="text-gray-300 hover:bg-gray-700 hover:text-white block rounded-md px-3 py-2 text-base font-medium">Appointment</a>
      </div>
    </div>
  </nav>
  </div>
<!------------------------------------------------nav-code-end-here--------------------------->
<!-------------------------------------------Content-Code-For-Super-Admin-Here----------------->
<div class="container mx-auto p-6 bg-gray-100 mt-4 rounded-lg shadow-md">
    <h1 class="text-4xl font-bold text-gray-900 mb-8">Super Admin Dashboard</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <!-- Create Admin Account Section -->
        <section id="create-admin" class="bg-white shadow-lg rounded-lg p-8 hover:shadow-xl transition-shadow duration-200">
            <h2 class="text-3xl font-semibold text-gray-800 mb-6">Create Admin Account</h2>

            <form method="post" class="space-y-6">
                <div>
                    <label for="username" class="block text-gray-700 font-medium">Username:</label>
                    <input type="text" id="username" name="username" required class="mt-2 px-4 py-2 w-full border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label for="password" class="block text-gray-700 font-medium">Password:</label>
                    <input type="password" id="password" name="password" required class="mt-2 px-4 py-2 w-full border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="flex justify-end">
                    <input type="submit" value="Create Admin" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors duration-150">
                </div>
            </form>
        </section>

        <!-- Manage Users Section -->
        <section id="manage-users" class="bg-white shadow-lg rounded-lg p-8 hover:shadow-xl transition-shadow duration-200">
            <h2 class="text-3xl font-semibold text-gray-800 mb-6">Manage Users</h2>

            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-300">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 border-b text-left text-gray-700 font-medium">User ID</th>
                            <th class="px-6 py-3 border-b text-left text-gray-700 font-medium">Username</th>
                            <th class="px-6 py-3 border-b text-left text-gray-700 font-medium">Role</th>
                            <th class="px-6 py-3 border-b text-left text-gray-700 font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch users from the database
                        $sql = "SELECT user_id, username, role_as FROM users";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td class='px-6 py-4 border-b text-gray-800'>" . htmlspecialchars($row['user_id']) . "</td>";
                                echo "<td class='px-6 py-4 border-b text-gray-800'>" . htmlspecialchars($row['username']) . "</td>";
                                echo "<td class='px-6 py-4 border-b text-gray-800'>" . ($row['role_as'] == 0 ? 'Admin' : 'Superadmin') . "</td>";
                                echo "<td class='px-6 py-4 border-b text-gray-800'>
                                        <form method='post' action='delete_user.php' class='inline'>
                                            <input type='hidden' name='user_id' value='" . htmlspecialchars($row['user_id']) . "'>
                                            <button type='submit' class='bg-red-600 text-white px-4 py-2 mb-1 rounded-lg hover:bg-red-700 transition-colors duration-150'>Delete</button>
                                        </form>
                                        <form method='get' action='edit_user.php' class='inline'>
                                            <input type='hidden' name='user_id' value='" . htmlspecialchars($row['user_id']) . "'>
                                            <button type='submit' class='bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors duration-150'>Edit</button>
                                        </form>
                                      </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' class='px-6 py-4 border-b text-gray-800 text-center'>No users found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>

       <!-- Admin Login History Section -->
       <section id="admin-logins" class="bg-white shadow-lg rounded-lg p-8 hover:shadow-xl transition-shadow duration-200">
            <h2 class="text-3xl font-semibold text-gray-800 mb-6">Admin Login History</h2>

            <div class="overflow-x-auto max-h-96">
                <table class="min-w-full bg-white border border-gray-300">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 border-b text-left text-gray-700 font-medium">Admin Username</th>
                            <th class="px-6 py-3 border-b text-left text-gray-700 font-medium">Login Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch admin login history from the database
                        $sql = "SELECT u.username, l.login_timestamp
                                FROM admin_login_history l
                                JOIN users u ON l.user_id = u.user_id
                                ORDER BY l.login_timestamp DESC";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td class='px-6 py-4 border-b text-gray-800'>" . htmlspecialchars($row['username']) . "</td>";
                                echo "<td class='px-6 py-4 border-b text-gray-800'>" . htmlspecialchars($row['login_timestamp']) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='2' class='px-6 py-4 border-b text-gray-800 text-center'>No login history found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
<!--------------------------------------------Super-Admin-Code-End-Here----------------------------->







</body>
</html>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const userMenuButton = document.getElementById('user-menu-button');
    const userMenu = document.querySelector('.absolute[aria-labelledby="user-menu-button"]');

    userMenuButton.addEventListener('click', function() {
      userMenu.classList.toggle('hidden');
    });
  });
</script>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuButton = document.querySelector('[aria-controls="mobile-menu"]');
    const mobileMenu = document.getElementById('mobile-menu');

    mobileMenuButton.addEventListener('click', function() {
      mobileMenu.classList.toggle('hidden');
    });
  });
</script>

</body>

</html>
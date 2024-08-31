<?php
session_start();
session_unset();
session_destroy();
header("Location: request_otp.php");
exit();
?>

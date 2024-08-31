<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request OTP</title>
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

</head>
<body onload="noBack();" onpageshow="if (event.persisted) noBack();" onunload="">
    <div class="bg-[#EEEFEE] flex justify-center items-center h-screen">
        <!-- Left: Image -->
        <div class="w-1/2 h-screen hidden lg:block">
            <img src="tingey-injury-law-firm-6sl88x150Xs-unsplash.jpg" alt="Placeholder Image" class="object-cover w-full h-full">
        </div>
        <!-- Right: OTP Request Form -->
        <div class="lg:p-36 md:p-52 sm:20 p-8 w-full lg:w-1/2">
            <h1 class="text-2xl font-semibold mb-4 text-[#572C14]">Request OTP</h1>
            <form method="post" action="process_otp_request.php">
                <!-- Email Input -->
                <div class="mb-4">
                    <label for="email" class="block text-gray-600">Email Address</label>
                    <input type="email" id="email" name="email" class="w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:border-blue-500" required>
                </div>
                <!-- Request OTP Button -->
                <button type="submit" class="border-2 border-[#572C14] hover:bg-[#d3d3d3] text-[#572C14] font-semibold rounded-md py-2 px-4 w-full">Request OTP</button>
            </form>
            <!-- Back to Login Link -->
            <div class="mt-4 text-center">
                <a href="../login/login.php" class="text-[#572C14] hover:underline">Back to Login</a>
            </div>
        </div>
    </div>
</body>
</html>

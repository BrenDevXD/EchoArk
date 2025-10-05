<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: profile.html");
    exit;
}

// --- DATABASE CONNECTION ---
$servername = "localhost";
$username = "root";
$password_db = "";
$dbname = "echoark_base"; // FIX: Corrected database name as per your instruction

$conn = new mysqli($servername, $username, $password_db, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$update_message = '';
$update_class = '';
$userId = $_SESSION['id'];

// HANDLE FORM SUBMISSION (UPDATE LOGIC)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // --- HANDLE PROFILE PICTURE UPLOAD ---
    if (isset($_FILES["profile_pic_upload"]) && $_FILES["profile_pic_upload"]["error"] == 0) {
        $target_dir = "uploads/";
        $imageFileType = strtolower(pathinfo($_FILES["profile_pic_upload"]["name"], PATHINFO_EXTENSION));
        $unique_filename = $userId . '_' . uniqid() . '.' . $imageFileType;
        $target_file = $target_dir . $unique_filename;
        $uploadOk = 1;

        // Validation checks...
        $check = getimagesize($_FILES["profile_pic_upload"]["tmp_name"]);
        if ($check === false) {
            $update_message = "File is not an image.";
            $uploadOk = 0;
        }
        if ($_FILES["profile_pic_upload"]["size"] > 5000000) { // 5MB limit
            $update_message = "Sorry, your file is too large.";
            $uploadOk = 0;
        }
        if (!in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) {
            $update_message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }

        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["profile_pic_upload"]["tmp_name"], $target_file)) {
                $stmt = $conn->prepare("UPDATE user SET profile_picture = ? WHERE id = ?");
                $stmt->bind_param("si", $target_file, $userId);
                $stmt->execute();
                $_SESSION['profile_picture'] = $target_file; // Update session immediately
                $stmt->close();
            } else {
                 $update_message = "Sorry, there was an error uploading your file.";
            }
        }
        if (!empty($update_message)) { // Set update class if there was an upload error
            $update_class = "bg-red-100 border-red-500 text-red-700";
        }
    }

    // --- HANDLE TEXT DATA UPDATE ---
    $newName = htmlspecialchars(trim($_POST['name']));
    $newPhone = htmlspecialchars(trim($_POST['phone']));
    $newDob = htmlspecialchars(trim($_POST['dob']));

    if (empty($newName)) {
        $update_message = "Name cannot be empty.";
        $update_class = "bg-red-100 border-red-500 text-red-700";
    } else {
        $stmt = $conn->prepare("UPDATE user SET Name = ?, Phone_Number = ?, Birthdate = ? WHERE id = ?");
        $stmt->bind_param("sssi", $newName, $newPhone, $newDob, $userId);
        if ($stmt->execute()) {
            // Only show success if there wasn't an upload error before
            if (empty($update_message)) {
                $update_message = "Your profile has been updated successfully!";
                $update_class = "bg-green-100 border-green-500 text-green-700";
            }
            $_SESSION['name'] = $newName;
        } else {
            $update_message = "Error updating profile data: " . $conn->error;
            $update_class = "bg-red-100 border-red-500 text-red-700";
        }
        $stmt->close();
    }
}

// FETCH CURRENT USER DATA for display
$stmt = $conn->prepare("SELECT Name, Email, Phone_Number, Birthdate, profile_picture FROM user WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($user_name, $user_email, $user_phone, $user_dob, $user_pfp);
$stmt->fetch();
$stmt->close();
$conn->close();

// Ensure the session variable for the picture is always up-to-date
$_SESSION['profile_picture'] = $user_pfp;
if (empty($user_pfp)) {
    $user_pfp = 'uploads/default_avatar.png';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account | Wildlife Guardians</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100">

    <!-- Navigation Bar -->
    <nav class="fixed top-0 z-50 w-full bg-black bg-opacity-40 backdrop-blur-md shadow-md">
         <div class="container mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <a href="index.php"><img src="Images/MainTitle.png" alt="Wildlife Guardians logo" class="h-12"></a>
                <div class="hidden md:flex space-x-8 items-center">
                    <div class="flex items-center space-x-3">
                        <?php
                        // Cek dulu apakah session profile_picture ada & tidak kosong. Jika tidak, gunakan path default.
                        $pfp_path = !empty($_SESSION['profile_picture']) ? $_SESSION['profile_picture'] : 'uploads/default_avatar.png';
                        ?>
                        <img src="<?php echo htmlspecialchars($pfp_path); ?>" alt="Profile Picture" class="h-10 w-10 rounded-full border-2 border-white object-cover">
                        <span class="font-lexend text-white font-bold"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                        <a href="logout.php" class="font-lexend text-gray-300 hover:text-white text-sm">(Logout)</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="pt-32 pb-16">
        <div class="container mx-auto px-6">
            <div class="w-full max-w-2xl mx-auto bg-white p-8 rounded-xl shadow-lg">
                <h1 class="text-3xl font-bold text-gray-800 mb-6">My Account</h1>

                <?php if (!empty($update_message)): ?>
                <div class="border <?php echo $update_class; ?> px-4 py-3 rounded relative mb-6" role="alert">
                    <p class="text-sm"><?php echo $update_message; ?></p>
                </div>
                <?php endif; ?>
                
                <form action="account.php" method="post" enctype="multipart/form-data">
                    <div class="space-y-6">
                        <div class="text-center">
                            <img src="<?php echo htmlspecialchars($user_pfp); ?>" alt="Current Profile Picture" class="w-32 h-32 rounded-full mx-auto mb-4 object-cover border-4 border-gray-200">
                            <label for="profile_pic_upload" class="cursor-pointer text-sm font-medium text-green-700 hover:text-green-800">
                                Change Profile Picture
                            </label>
                            <input type="file" name="profile_pic_upload" id="profile_pic_upload" class="hidden">
                        </div>
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                            <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($user_name); ?>" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user_email); ?>" readonly class="mt-1 block w-full px-3 py-2 bg-gray-100 border-gray-300 rounded-md shadow-sm cursor-not-allowed">
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                            <input type="tel" name="phone" id="phone" value="<?php echo htmlspecialchars($user_phone); ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                        </div>
                        <div>
                            <label for="dob" class="block text-sm font-medium text-gray-700">Birthdate</label>
                            <input type="date" name="dob" id="dob" value="<?php echo htmlspecialchars($user_dob); ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                        </div>
                    </div>
                    <div class="mt-8 border-t pt-6">
                        <button type="submit" class="w-full bg-green-700 hover:bg-green-800 text-white font-bold py-3 px-6 rounded-lg transition">
                            Save Changes
                        </button>
                    </div>
                </form>
                 <div class="mt-10 border-t-2 border-red-200 pt-6">
                    <h2 class="text-xl font-bold text-red-800">Danger Zone</h2>
                    <p class="text-gray-600 mt-2 mb-4">After deleting this account, there will be no turning back. Make sure to not regret this decision.</p>
                    
                    <form action="delete_account.php" method="post" onsubmit="return confirm('Are you sure to permanently delete this account?');">
                        <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg transition">
                            Delete My Account
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>

</body>
</html>


<?php
session_start();
// profile.php

// --- DATABASE CONNECTION ---
$servername = "localhost"; // Biasanya "localhost"
$username = "root";      // Default username untuk XAMPP/local server
$password_db = "";         // Default password kosong untuk XAMPP/local server
$dbname = "echoark_base"; // Nama database Anda dari gambar

// Membuat koneksi
$conn = new mysqli($servername, $username, $password_db, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to safely retrieve form data
function get_post_data($field) {
    // Check if the field exists and sanitize it to prevent XSS attacks
    return isset($_POST[$field]) ? htmlspecialchars(trim($_POST[$field])) : '';
}

// Check if the request method is POST, meaning a form was submitted
if (!empty($_POST['name'])) {
        $fullName = get_post_data('name');
        $email = get_post_data('email');
        $phone = get_post_data('phone');
        $dob = get_post_data('dob');
        $password = get_post_data('password');

        if (empty($fullName) || empty($email) || empty($password)) {
            // Handle error...
        } else {
            $stmt = $conn->prepare("SELECT id FROM user WHERE Email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $status_message = "Error: This Email is already registered.";
                $status_class = "bg-red-100 border-red-500 text-red-700";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $insert_stmt = $conn->prepare("INSERT INTO user (Name, Email, Phone_Number, Birthdate, Password) VALUES (?, ?, ?, ?, ?)");
                $insert_stmt->bind_param("sssss", $fullName, $email, $phone, $dob, $hashed_password);

                if ($insert_stmt->execute()) {
                     $new_user_id = $insert_stmt->insert_id; // Ambil ID user yang baru dibuat

                     // FIX: Ambil path gambar profil default dari user yang baru saja dibuat
                     $pfp_stmt = $conn->prepare("SELECT profile_picture FROM user WHERE id = ?");
                     $pfp_stmt->bind_param("i", $new_user_id);
                     $pfp_stmt->execute();
                     $pfp_stmt->bind_result($pfp_path);
                     $pfp_stmt->fetch();
                     $pfp_stmt->close();

                     // SET SESSION SETELAH SIGN UP BERHASIL
                     $_SESSION['loggedin'] = true;
                     $_SESSION['id'] = $new_user_id;
                     $_SESSION['name'] = $fullName;
                     // FIX: Tambahkan profile_picture ke session
                     $_SESSION['profile_picture'] = $pfp_path; 
    
                     // Redirect ke halaman utama
                     header("Location: index.php");
                     exit();
                    }  else {
                    // Handle error...
                    }
                      $insert_stmt->close();
                }
            $stmt->close();
        }
    }
    // --- LOGIN LOGIC ---
    // Otherwise, it's a login form
    else {
    $email = get_post_data('email');
    $password = get_post_data('password');

    if (empty($email) || empty($password)) {
        // Handle error...
    } else {
        // FIX: Tambahkan 'profile_picture' ke query SELECT
        $stmt = $conn->prepare("SELECT id, Name, Password, profile_picture, role FROM user WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            // FIX: Tambahkan variabel $pfp_path untuk menampung hasil
            $stmt->bind_result($user_id, $fullName, $hashed_password_from_db, $pfp_path, $user_role);
            $stmt->fetch();

            if (password_verify($password, $hashed_password_from_db)) {
                // SET SEMUA SESSION setelah login berhasil
                $_SESSION['loggedin'] = true;
                $_SESSION['id'] = $user_id;
                $_SESSION['name'] = $fullName;
                // FIX: Gunakan variabel yang sudah diambil dari database
                $_SESSION['profile_picture'] = $pfp_path; 
                $_SESSION['role'] = $user_role;

                // Redirect ke halaman utama
                header("Location: index.php");
                exit();
            } else {
                $status_message = "Error: The Email or the Password is wrong, Please Try Again.";
                $status_class = "bg-red-100 border-red-500 text-red-700";
            }
        }
        $stmt->close();
    }
}

$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processing... | Wildlife Guardians</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="w-full max-w-lg mx-auto">
        <div class="bg-white p-8 rounded-xl shadow-lg text-center">
            <img src="Images/MainTitle.png" alt="Wildlife Guardians logo" class="h-16 mx-auto mb-6">
            
            <div class="border <?php echo $status_class; ?> px-4 py-3 rounded relative mb-6" role="alert">
                <p class="font-bold">Status</p>
                <p class="text-sm"><?php echo $status_message; ?></p>
            </div>
            
            <a href="profile.html" class="inline-block bg-green-700 hover:bg-green-800 text-white font-bold py-3 px-6 rounded-lg transition duration-300">
                ← Go Back
            </a>
        </div>
    </div>

</body>
</html>
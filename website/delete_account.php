<?php
session_start();

// 1. Keamanan: Pastikan pengguna sudah login sebelum melakukan apapun
// Jika tidak ada sesi login, arahkan kembali ke halaman login/profile
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: profile.html");
    exit;
}

// 2. Definisi Detail Koneksi Database (menggantikan config.php)
$servername = "localhost";
$username = "root";
$password_db = ""; // Ganti dengan password database Anda jika ada
$dbname = "echoark_base";

// Buat Koneksi
$conn = new mysqli($servername, $username, $password_db, $dbname);

// Cek Koneksi
if ($conn->connect_error) {
    // Jika koneksi gagal, tampilkan pesan error yang aman
    die("Server Error: Unable to connect to database.");
}

// 3. Logika Penghapusan Akun
$userId = $_SESSION['id']; // Ambil ID pengguna dari sesi yang aktif

// Siapkan statement DELETE untuk mencegah SQL Injection
// Hapus semua data pengguna dari tabel 'user' berdasarkan ID
$stmt = $conn->prepare("DELETE FROM user WHERE id = ?");
$stmt->bind_param("i", $userId); // "i" menandakan bahwa ID adalah integer

// Jalankan statement
if ($stmt->execute()) {
    
    // 4. Jika penghapusan berhasil, hancurkan semua data sesi (logout paksa)
    session_unset();
    session_destroy();
    
    // 5. Arahkan pengguna ke halaman utama (atau halaman sukses)
    header("location: index.php");
    exit();

} else {
    // Jika terjadi error saat menghapus dari database
    echo "Error: Terjadi kesalahan saat menghapus data. Silakan coba lagi nanti.";
}

$stmt->close();
$conn->close();
?>
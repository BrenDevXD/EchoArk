<?php
// =======================================================
// === VERSI DEBUG UNTUK MENEMUKAN MASALAH UPLOAD ===
// =======================================================

// Tampilkan semua error
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
echo "Skrip dimulai...<br>";

// Keamanan: Cek sesi admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["role"] !== 'admin') {
    die("ERROR: Akses ditolak. Anda bukan admin atau belum login.");
}
echo "Pengecekan admin berhasil...<br>";

// Cek apakah ini request POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    echo "Metode POST terdeteksi. Memproses form...<br>";

    // Koneksi ke Database
    $servername = "localhost";
    $username = "root";
    $password_db = "";
    $dbname = "echoark_base";
    $conn = new mysqli($servername, $username, $password_db, $dbname);

    if ($conn->connect_error) {
        die("FATAL ERROR: Koneksi ke database gagal! Pesan: " . $conn->connect_error);
    }
    echo "Koneksi database berhasil...<br>";

    // Cek data form
    if (empty($_POST['species_name']) || empty($_POST['species_fact'])) {
        die("FATAL ERROR: Nama atau fakta spesies tidak boleh kosong.");
    }
    $species_name = trim($_POST['species_name']);
    $species_fact = trim($_POST['species_fact']);
    echo "Data form diterima: Nama='{$species_name}', Fakta='{$species_fact}'<br>";

    // Cek file upload
    if (!isset($_FILES["species_image"]) || $_FILES["species_image"]["error"] != 0) {
        die("FATAL ERROR: Tidak ada file yang diunggah atau terjadi error saat upload. Kode Error: " . $_FILES["species_image"]["error"]);
    }
    echo "File terdeteksi. Memproses gambar...<br>";

    $target_dir = "uploads/species/";
    // Cek apakah folder uploads/species ada dan bisa ditulis
    if (!is_dir($target_dir)) {
        if (!mkdir($target_dir, 0755, true)) {
            die("FATAL ERROR: Gagal membuat folder '{$target_dir}'. Periksa izin (permission) folder 'uploads'.");
        }
    }
    if (!is_writable($target_dir)) {
        die("FATAL ERROR: Folder '{$target_dir}' tidak bisa ditulis. Periksa izin (permission) folder.");
    }
    echo "Folder tujuan '{$target_dir}' siap...<br>";

    $imageFileType = strtolower(pathinfo($_FILES["species_image"]["name"], PATHINFO_EXTENSION));
    $unique_filename = uniqid() . '_' . time() . '.' . $imageFileType;
    $target_file = $target_dir . $unique_filename;

    // Memindahkan file yang di-upload
    echo "Mencoba memindahkan file ke '{$target_file}'...<br>";
    if (move_uploaded_file($_FILES["species_image"]["tmp_name"], $target_file)) {
        echo "File berhasil dipindahkan. Memasukkan data ke database...<br>";
        
        // Memasukkan data ke database
        $stmt = $conn->prepare("INSERT INTO species (name, fact, image_path) VALUES (?, ?, ?)");
        if ($stmt === false) {
            die("FATAL ERROR: Gagal mempersiapkan statement SQL. Pesan: " . $conn->error);
        }

        $stmt->bind_param("sss", $species_name, $species_fact, $target_file);
        
        if ($stmt->execute()) {
            echo "BERHASIL! Data berhasil dimasukkan ke database.<br>";
            $stmt->close();
            $conn->close();
            // Jika berhasil, arahkan kembali setelah 3 detik
            header("refresh:3;url=admin_dashboard.php?status=success&message=New species added successfully!");
            exit();
        } else {
            die("FATAL ERROR: Gagal memasukkan data ke database. Pesan: " . $stmt->error);
        }

    } else {
        die("FATAL ERROR: Gagal memindahkan file yang di-upload. Periksa kembali izin (permission) pada folder 'uploads/species/'.");
    }

} else {
    die("ERROR: File ini tidak bisa diakses langsung.");
}
?>

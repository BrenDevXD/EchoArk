<?php
session_start();

// Keamanan: Cek apakah pengguna sudah login DAN perannya adalah 'admin'
if (!isset($_SESSION["loggedin"]) || $_SESSION["role"] !== 'admin') {
    header("location: index.php");
    exit;
}

// Koneksi ke Database
$servername = "localhost";
$username = "root";
$password_db = "";
$dbname = "echoark_base";

$conn = new mysqli($servername, $username, $password_db, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Mengambil data pengguna dan spesies untuk ditampilkan
$users = [];
$sql_users = "SELECT id, Name, Email, role FROM user ORDER BY id ASC";
$result_users = $conn->query($sql_users);
if ($result_users->num_rows > 0) {
    while($row = $result_users->fetch_assoc()) {
        $users[] = $row;
    }
}

$species_list = [];
$sql_species = "SELECT id, name, fact, image_path FROM species ORDER BY created_at DESC";
$result_species = $conn->query($sql_species);
if ($result_species->num_rows > 0) {
    while($row = $result_species->fetch_assoc()) {
        $species_list[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-200 p-8">
    <div class="container mx-auto space-y-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Admin Dashboard</h1>
            <a href="index.php" class="text-blue-500 hover:underline">Back To The Main Page</a>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h2 class="text-2xl font-bold mb-4">Add New At-Risk Species</h2>
            <?php if(isset($_GET['status'])): ?>
                <div class="mb-4 p-3 rounded-md <?php echo $_GET['status'] == 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                    <?php echo htmlspecialchars($_GET['message']); ?>
                </div>
            <?php endif; ?>
            
            <form action="add_species.php" method="post" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label for="species_name" class="block text-sm font-medium text-gray-700">Species Name</label>
                    <input type="text" name="species_name" id="species_name" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="species_fact" class="block text-sm font-medium text-gray-700">Short Fact</label>
                    <input type="text" name="species_fact" id="species_fact" required placeholder="e.g., Fewer than 100 remain" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="species_image" class="block text-sm font-medium text-gray-700">Species Image</label>
                    <input type="file" name="species_image" id="species_image" required class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                </div>
                <div>
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-md transition">Add Species</button>
                </div>
            </form>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-lg">
    <h2 class="text-2xl font-bold mb-4">Existing Species</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Image</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fact</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($species_list as $species): ?>
                    <tr>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900"><?php echo $species['id']; ?></td>
                        <td class="px-6 py-4"><img src="<?php echo htmlspecialchars($species['image_path']); ?>" alt="<?php echo htmlspecialchars($species['name']); ?>" class="h-12 w-16 object-cover rounded-md"></td>
                        <td class="px-6 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($species['name']); ?></td>
                        <td class="px-6 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($species['fact']); ?></td>
                        <td class="px-6 py-4 text-sm font-medium">
                            <a href="delete_species.php?id=<?php echo $species['id']; ?>" class="text-red-600 hover:text-red-900 transition-colors" onclick="return confirm('Are you sure you want to delete this species?');">
                                Delete
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

    <div class="bg-white p-6 rounded-lg shadow-lg">
        <h2 class="text-2xl font-bold mb-4">User Management</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['id']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($user['Name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($user['Email']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($user['role']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>


<?php

// Keamanan: Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION["loggedin"]) || $_SESSION["role"] !== 'admin') {
    header("location: index.php");
    exit;
}

// Pastikan request adalah POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Koneksi ke Database
    $servername = "localhost";
    $username = "root";
    $password_db = "";
    $dbname = "echoark_base";
    $conn = new mysqli($servername, $username, $password_db, $dbname);

    // Ambil data dari form
    $species_name = trim($_POST['species_name']);
    $species_fact = trim($_POST['species_fact']);
    $message = '';
    $status = 'error';

    // Logika Upload Gambar
    if (isset($_FILES["species_image"]) && $_FILES["species_image"]["error"] == 0) {
        $target_dir = "uploads/species/";
        $imageFileType = strtolower(pathinfo($_FILES["species_image"]["name"], PATHINFO_EXTENSION));
        $unique_filename = uniqid() . '_' . time() . '.' . $imageFileType;
        $target_file = $target_dir . $unique_filename;
        $uploadOk = 1;

        // Validasi file (ukuran, tipe, dll.)
        $check = getimagesize($_FILES["species_image"]["tmp_name"]);
        if ($check === false) { $message = "File is not an image."; $uploadOk = 0; }
        if ($_FILES["species_image"]["size"] > 5000000) { $message = "Sorry, your file is too large."; $uploadOk = 0; }
        if (!in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) { $message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed."; $uploadOk = 0; }

        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["species_image"]["tmp_name"], $target_file)) {
                // Gambar berhasil di-upload, sekarang masukkan data ke database
                $stmt = $conn->prepare("INSERT INTO species (name, fact, image_path) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $species_name, $species_fact, $target_file);
                
                if ($stmt->execute()) {
                    $message = "New species added successfully!";
                    $status = 'success';
                } else {
                    $message = "Error inserting data into database.";
                }
                $stmt->close();
            } else {
                 $message = "Sorry, there was an error uploading your file.";
            }
        }
    } else {
        $message = "No image was uploaded or an error occurred.";
    }

    $conn->close();
    // Redirect kembali ke dashboard dengan pesan status
    header("location: admin_dashboard.php?status=" . $status . "&message=" . urlencode($message));
    exit();
}
?>


<?php

// Koneksi ke Database untuk mengambil data spesies
$servername = "localhost";
$username = "root";
$password_db = "";
$dbname = "echoark_base";
$conn = new mysqli($servername, $username, $password_db, $dbname);

$species_list = [];
if (!$conn->connect_error) {
    $sql_species = "SELECT name, fact, image_path FROM species ORDER BY created_at DESC LIMIT 10";
    $result_species = $conn->query($sql_species);
    if ($result_species->num_rows > 0) {
        while($row = $result_species->fetch_assoc()) {
            $species_list[] = $row;
        }
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    </head>
<body class="bg-gray-50">

</body>
</html>

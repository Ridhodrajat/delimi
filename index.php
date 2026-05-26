<?php
session_start();
include 'koneksi.php';

// Cek Login
if (isset($_SESSION['nim'])) { header("Location: fitur/dashboard.php"); exit; }

$error = "";

if (isset($_POST['login'])) {
    $nim = $_POST['nim'];
    $pass = $_POST['password'];

    $q = mysqli_query($conn, "SELECT * FROM mahasiswa WHERE NIM = '$nim'");
    
    if ($user = mysqli_fetch_assoc($q)) {
        // Logika Password Ringkas: (NamaDepan + NIM mulai dr 24 atau 5 digit akhir)
        $nama_depan = strtolower(strtok($user['nama_mhs'], " "));
        // strstr mencari '24' dan mengambil sisanya. Jika tidak ada, ambil 5 digit terakhir.
        $nim_ekor   = strstr($user['NIM'], '24') ?: substr($user['NIM'], -5);
        
        if ($pass == $nama_depan . $nim_ekor) {
            $_SESSION['nim'] = $user['NIM'];
            $_SESSION['nama'] = $user['nama_mhs'];
            header("Location: fitur/dashboard.php"); exit;
        } 
        $error = "Password salah. Format: nama depan (kecil) + angka nim dari 24.";
    } else {
        $error = "NIM tidak ditemukan.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Delimi</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-login">
    <div class="login-box">
        <div class="logo-area">
            <h1>Delimi</h1>
            <p>Productivity & Growth</p>
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label>NIM</label>
                <input type="text" name="nim" placeholder="Nim Kamu" required>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Nama Depan + NIM(24...)" required>
            </div>
            
            <button type="submit" name="login">Masuk Sekarang</button>
        </form>

        <div style="margin-top: 20px; font-size: 0.9em; color: #666;">
            Belum terdaftar? <a href="register.php" class="link-register">Buat Akun Baru</a>
        </div>

        <?php if($error): ?>
            <div class="alert-error"><?= $error ?></div>
        <?php endif; ?>
    </div>
</body>
</html>
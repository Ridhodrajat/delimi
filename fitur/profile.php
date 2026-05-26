<?php
session_start();
include '../koneksi.php';
if (!isset($_SESSION['nim'])) { header("Location: ../index.php"); exit; }

$nim = $_SESSION['nim'];
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM mahasiswa WHERE NIM = '$nim'"));
$foto_profil = $user['foto'] ? "../uploads/$user[foto]" : "https://via.placeholder.com/150/A5D6A7/ffffff?text=".strtoupper($user['nama_mhs'][0]);

// 1. UPDATE PROFIL
if (isset($_POST['update_profile'])) {
    $nama = $_POST['nama_mhs'];
    $panggilan = $_POST['nama_panggilan'];
    $foto_sql = "";

    if (!empty($_POST['cropped_image'])) {
        $data = base64_decode(explode(",", $_POST['cropped_image'])[1]);
        $file = $nim . "_" . time() . ".png";
        if (!file_exists('../uploads')) mkdir('../uploads', 0777, true);
        file_put_contents("../uploads/$file", $data);
        if ($user['foto'] && file_exists("../uploads/$user[foto]")) unlink("../uploads/$user[foto]");
        $foto_sql = ", foto='$file'";
    }

    if (mysqli_query($conn, "UPDATE mahasiswa SET nama_mhs='$nama', nama_panggilan='$panggilan' $foto_sql WHERE NIM='$nim'")) {
        $_SESSION['nama'] = $nama;
        header("Location: profile.php?status=success");
    }
}

// 2. HAPUS FOTO
if (isset($_GET['hapus_foto'])) {
    if ($user['foto'] && file_exists("../uploads/$user[foto]")) unlink("../uploads/$user[foto]");
    mysqli_query($conn, "UPDATE mahasiswa SET foto=NULL WHERE NIM='$nim'");
    header("Location: profile.php?status=deleted");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Edit Profil - Delimi</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
</head>
<body>
<div class="app-container">
    <?php include '../sidebar.php'; ?>
    <main class="content">
        <h2>⚙️ Edit Profil</h2>
        
        <?php if (isset($_GET['status'])): 
            $msg = ($_GET['status'] == 'success') ? ['Berhasil Disimpan!', 'Data profilmu sudah diperbarui.'] : ['Foto Dihapus!', 'Foto profil kembali ke default.'];
        ?>
            <div id="toast" class="toast-box">
                <i class="fa-solid fa-circle-check"></i>
                <div class="toast-text"><strong><?= $msg[0] ?></strong><small><?= $msg[1] ?></small></div>
            </div>
            <script>setTimeout(() => { document.getElementById('toast').style.display='none'; history.replaceState(null,null,location.pathname); }, 3000);</script>
        <?php endif; ?>

        <div class="card">
            <form method="POST" enctype="multipart/form-data">
                <div style="text-align:center; margin-bottom:20px;">
                    <div class="img-preview-container"><img src="<?= $foto_profil ?>" class="avatar-preview"></div>
                    <div id="crop-area" style="display:none; margin:20px auto; max-width:400px;"><img id="img-crop" style="max-width:100%;"></div>
                    <input type="hidden" name="cropped_image" id="cropped_image">
                    
                    <div class="profile-actions">
                        <label class="btn-upload">Pilih Foto Baru <input type="file" id="fotoInput" style="display:none;" accept="image/*"></label>
                        <?php if($user['foto']): ?>
                            <button type="button" class="btn-delete-photo" onclick="conf('?hapus_foto=true')">Hapus Foto</button>
                        <?php endif; ?>
                    </div>
                </div>
                
                <label>Nama Lengkap</label>
                <input type="text" name="nama_mhs" value="<?= $user['nama_mhs'] ?>" required style="width:100%; margin-bottom: 20px;">
                <label>Nama Panggilan</label>
                <input type="text" name="nama_panggilan" value="<?= $user['nama_panggilan'] ?>" placeholder="Contoh: Ridho" style="width:100%">
                <button type="submit" name="update_profile" class="btn-save">Simpan Perubahan</button>
            </form>
        </div>
    </main>
</div>

<div id="modal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <h3>Hapus Foto?</h3> <p>Foto akan kembali ke default.</p>
        <div class="modal-actions">
            <button onclick="closeM()" class="btn-modal-no">Batal</button>
            <a id="delLink" href="#" class="btn-modal-yes">Ya, Hapus</a>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script>
    let cropper, img = document.getElementById('img-crop');
    document.getElementById('fotoInput').onchange = e => {
        if(e.target.files.length) {
            img.src = URL.createObjectURL(e.target.files[0]);
            document.getElementById('crop-area').style.display = 'block';
            if(cropper) cropper.destroy();
            cropper = new Cropper(img, { aspectRatio: 1, viewMode: 1 });
        }
    };
    document.querySelector('form').onsubmit = () => {
        if(cropper) document.getElementById('cropped_image').value = cropper.getCroppedCanvas({width:300,height:300}).toDataURL("image/png");
    };
    
    // Modal Script
    function conf(url) { document.getElementById('delLink').href = url; document.getElementById('modal').style.display = 'flex'; }
    function closeM() { document.getElementById('modal').style.display = 'none'; }
    window.onclick = e => { if(e.target == document.getElementById('modal')) closeM(); }
</script>
</body>
</html>
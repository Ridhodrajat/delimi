<?php
session_start();
include '../koneksi.php';
if (!isset($_SESSION['nim'])) { header("Location: ../index.php"); exit; }

$nim = $_SESSION['nim'];

// 1. LOGIC SIMPAN / UPDATE (Lebih Ringkas)
if (isset($_POST['simpan'])) {
    $judul = $_POST['judul']; $isi = $_POST['isi']; $mood = $_POST['mood'];
    
    if(!empty($_POST['id'])) {
        $q = "UPDATE jurnal SET judul='$judul', isi='$isi', mood='$mood' WHERE id_jurnal='$_POST[id]' AND NIM='$nim'";
    } else {
        $q = "INSERT INTO jurnal (NIM, judul, isi, mood) VALUES ('$nim','$judul','$isi','$mood')";
    }
    mysqli_query($conn, $q);
    header("Location: jurnal.php");
}

// 2. LOGIC HAPUS
if (isset($_GET['hapus'])) {
    mysqli_query($conn, "DELETE FROM jurnal WHERE id_jurnal='$_GET[hapus]' AND NIM='$nim'");
    header("Location: jurnal.php");
}

// 3. AMBIL DATA EDIT & LIST
$edit = isset($_GET['edit']) ? mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM jurnal WHERE id_jurnal='$_GET[id]'")) : null;
$list_jurnal = mysqli_query($conn, "SELECT * FROM jurnal WHERE NIM='$nim' ORDER BY tanggal DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Jurnal - Delimi</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<div class="app-container">
    <?php include '../sidebar.php'; ?>
    <main class="content">
        <h2><?= $edit ? '✏️ Edit Jurnal' : 'Jurnal Harian 📔' ?></h2>
        
        <div class="card">
            <form method="POST">
                <input type="hidden" name="id" value="<?= $edit['id_jurnal'] ?? '' ?>">
                <input type="text" name="judul" value="<?= $edit['judul'] ?? '' ?>" placeholder="Judul cerita..." required style="width:100%">
                <textarea name="isi" placeholder="Tulis apa yang kamu rasakan..." rows="3" required style="width:100%"><?= $edit['isi'] ?? '' ?></textarea>
                
                <select name="mood">
                    <?php 
                    $moods = ['Senang'=>'😊', 'Biasa'=>'😐', 'Sedih'=>'😢', 'Marah'=>'😡'];
                    foreach($moods as $key => $icon) {
                        $selected = ($edit && $edit['mood'] == $key) ? 'selected' : '';
                        echo "<option value='$key' $selected>$icon $key</option>";
                    }
                    ?>
                </select>
                
                <button type="submit" name="simpan" style="margin-top:10px;"><?= $edit ? 'Update' : 'Simpan' ?></button>
                <?php if($edit): ?><a href="jurnal.php" class="btn-cancel" style="float:right; margin-top:10px;">Batal</a><?php endif; ?>
            </form>
        </div>
        
        <?php if(!$edit): ?>
        <div class="feed">
            <?php while($j = mysqli_fetch_assoc($list_jurnal)): ?>
            <div class="card post">
                <div class="post-header">
                    <b><?= date('d M Y', strtotime($j['tanggal'])) ?></b> 
                    <span class="badge"><?= $moods[$j['mood']] ?? '' ?> <?= $j['mood'] ?></span>
                    <div>
                        <a href="?edit=1&id=<?= $j['id_jurnal'] ?>" class="btn-icon edit"><i class="fa-solid fa-pen"></i></a>
                        <a href="#" onclick="openModal('?hapus=<?= $j['id_jurnal'] ?>')" class="btn-icon del"><i class="fa-solid fa-times"></i></a>
                    </div>
                </div>
                <h4><?= $j['judul'] ?></h4>
                <p><?= nl2br($j['isi']) ?></p>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>
    </main>
</div>

<div id="confirmModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <h3>Hapus Jurnal?</h3> <p>Kenangan ini akan hilang selamanya.</p>
        <div class="modal-actions">
            <button onclick="closeModal()" class="btn-modal-no">Batal</button>
            <a id="deleteLink" href="#" class="btn-modal-yes">Ya, Hapus</a>
        </div>
    </div>
</div>

<script>
    function openModal(url) { document.getElementById('deleteLink').href = url; document.getElementById('confirmModal').style.display = 'flex'; }
    function closeModal() { document.getElementById('confirmModal').style.display = 'none'; }
    window.onclick = e => { if (e.target == document.getElementById('confirmModal')) closeModal(); }
</script>
</body>
</html>
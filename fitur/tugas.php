<?php
session_start();
include '../koneksi.php';
if (!isset($_SESSION['nim'])) { header("Location: ../index.php"); exit; }

$nim = $_SESSION['nim'];

// 1. SIMPAN & UPDATE
if (isset($_POST['simpan'])) {
    $judul = $_POST['judul']; $desk = $_POST['deskripsi']; $dl = $_POST['deadline'];
    if (!empty($_POST['id'])) {
        $q = "UPDATE tugas SET judul='$judul', deskripsi='$desk', deadline='$dl' WHERE id_tugas='$_POST[id]' AND NIM='$nim'";
    } else {
        $q = "INSERT INTO tugas (NIM, judul, deskripsi, deadline) VALUES ('$nim','$judul','$desk','$dl')";
    }
    mysqli_query($conn, $q);
    header("Location: tugas.php");
}

// 2. HAPUS
if (isset($_GET['hapus'])) {
    mysqli_query($conn, "DELETE FROM tugas WHERE id_tugas='$_GET[hapus]' AND NIM='$nim'");
    header("Location: tugas.php");
}

// 3. GET DATA (EDIT & LIST)
$edit = isset($_GET['edit']) ? mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tugas WHERE id_tugas='$_GET[id]'")) : null;
$list = mysqli_query($conn, "SELECT * FROM tugas WHERE NIM='$nim' ORDER BY deadline ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Tugas - Delimi</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/airbnb.css">
    <style>
        .post-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f0f0f0; padding-bottom: 10px; margin-bottom: 10px; }
        .task-status { font-size: 0.75em; padding: 4px 12px; border-radius: 20px; font-weight: 700; text-transform: uppercase; margin-left: 10px; }
        .status-danger { background: #ffebee; color: #c62828; }
        .status-warn { background: #fff3e0; color: #ef6c00; }
        .status-safe { background: #e8f5e9; color: #2e7d32; }
        .card.post { margin-bottom: 20px; border-left: 5px solid #4CAF50; }
        .input-box { width: 100%; padding: 15px; border: 1px solid #ddd; border-radius: 10px; font-family: 'Poppins', sans-serif; margin-bottom: 15px; }
        .input-box:focus { border-color: #4CAF50; outline: none; }
    </style>
</head>
<body>
<div class="app-container">
    <?php include '../sidebar.php'; ?>
    <main class="content">
        <h2><?= $edit ? '✏️ Edit Tugas' : 'Daftar Tugas 📝' ?></h2>
        
        <div class="card">
            <form method="POST">
                <input type="hidden" name="id" value="<?= $edit['id_tugas'] ?? '' ?>">
                <input type="text" name="judul" class="input-box" value="<?= $edit['judul'] ?? '' ?>" placeholder="Judul Tugas..." required>
                <textarea name="deskripsi" class="input-box" rows="3" placeholder="Deskripsi tugas..."><?= $edit['deskripsi'] ?? '' ?></textarea>
                <div style="display:flex; gap:10px;">
                    <input type="text" id="dl" name="deadline" class="input-box" style="flex:1; margin:0;" value="<?= $edit['deadline'] ?? '' ?>" placeholder="Deadline" required>
                    <button type="submit" name="simpan" style="margin:0; height:50px;"><?= $edit ? 'Update' : 'Tambah' ?></button>
                    <?php if($edit): ?><a href="tugas.php" class="btn-cancel" style="height:50px; line-height:25px;">Batal</a><?php endif; ?>
                </div>
            </form>
        </div>
            
        <?php if(!$edit): ?>
        <div class="feed">
            <?php while($r = mysqli_fetch_assoc($list)): 
                $diff = strtotime($r['deadline']) - time();
                $days = floor($diff / 86400);
                $st = ($diff < 0) ? ['Terlewat!', 'danger'] : (($days == 0) ? ['Hari Ini!', 'warn'] : ["$days Hari Lagi", 'safe']);
            ?>
            <div class="card post">
                <div class="post-header">
                    <div><i class="fa-regular fa-calendar"></i> <b><?= date('d M Y', strtotime($r['deadline'])) ?></b> <span class="task-status status-<?= $st[1] ?>"><?= $st[0] ?></span></div>
                    <div><a href="?edit=1&id=<?= $r['id_tugas'] ?>" class="btn-icon edit"><i class="fa-solid fa-pen"></i></a> <a href="#" onclick="conf('?hapus=<?= $r['id_tugas'] ?>')" class="btn-icon del"><i class="fa-solid fa-trash"></i></a></div>
                </div>
                <h3 style="margin:0 0 10px 0; color:#333;"><?= $r['judul'] ?></h3>
                <?php if($r['deskripsi']): ?><p style="color:#555; margin-bottom:15px;"><?= nl2br($r['deskripsi']) ?></p><?php endif; ?>
                <p style="color:#888; font-size:0.85em; border-top:1px dashed #eee; padding-top:10px;"><i class="fa-regular fa-clock"></i> Deadline: <b><?= date('H:i', strtotime($r['deadline'])) ?></b></p>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>
    </main>
</div>

<div id="modal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <h3>Hapus Tugas?</h3> <p>Data akan hilang permanen.</p>
        <div class="modal-actions">
            <button onclick="closeM()" class="btn-modal-no">Batal</button>
            <a id="delLink" href="#" class="btn-modal-yes">Ya, Hapus</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    flatpickr("#dl", { enableTime: true, dateFormat: "Y-m-d H:i", time_24hr: true, minDate: "today" });
    function conf(url) { document.getElementById('delLink').href = url; document.getElementById('modal').style.display = 'flex'; }
    function closeM() { document.getElementById('modal').style.display = 'none'; }
    window.onclick = e => { if(e.target == document.getElementById('modal')) closeM(); }
</script>
</body>
</html>
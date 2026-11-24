<?php
// index.php - Halaman Utama Dinamis BagiPangan
session_start();
$root_path = './';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth_check.php';

$user = current_user();

// Ambil kategori dari database
$kategori_list = [];
try {
    $kategori_list = $pdo->query("SELECT * FROM kategori ORDER BY id ASC")->fetchAll();
} catch (Exception $e) {
    // Fallback jika query error
}

// Search & Filter handling
$search = trim($_GET['search'] ?? '');
$kategori_filter = $_GET['kategori'] ?? '';

// Build query with search and filter
$where_conditions = ["d.status = 'tersedia'", "d.batas_waktu > NOW()"];
$params = [];

if ($search !== '') {
    $where_conditions[] = "(d.nama_makanan LIKE ? OR d.deskripsi LIKE ? OR u.nama LIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term, $search_term]);
}

if ($kategori_filter !== '') {
    $where_conditions[] = "k.nama_kategori = ?";
    $params[] = $kategori_filter;
}

$where_sql = implode(' AND ', $where_conditions);

// Ambil donasi aktif dari database
$donasi_list = [];
$total_terselamatkan = 0;
$total_penyedia = 0;
$total_penerima = 0;
try {
    $sql = "SELECT d.*, k.nama_kategori, u.nama AS nama_penyedia 
            FROM donasi d 
            JOIN kategori k ON d.kategori_id = k.id 
            JOIN users u ON d.user_id = u.id 
            WHERE $where_sql 
            ORDER BY d.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $donasi_list = $stmt->fetchAll();
    
    // Hitung statistik riil dari database
    $total_terselamatkan = (int)$pdo->query("SELECT COALESCE(SUM(jumlah_porsi), 0) FROM pesanan WHERE status = 'selesai'")->fetchColumn();
    $total_penyedia = (int)$pdo->query("SELECT COUNT(DISTINCT user_id) FROM donasi")->fetchColumn();
    $total_penerima = (int)$pdo->query("SELECT COUNT(DISTINCT user_id) FROM pesanan WHERE status = 'selesai'")->fetchColumn();
} catch (Exception $e) {
    // Fallback jika database belum ada data
}

$page_title = 'Berbagi Makanan Surplus';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>BagiPangan | Berbagi makanan surplus</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Anton&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
<style>
/* Style tambahan khusus layout landing page agar identik dengan index.html */
.hero { text-align: center; padding: 40px 0 80px; }
.hero h1 {
    font-size: clamp(96px, 21vw, 240px);
    line-height: .88;
    background:
        radial-gradient(circle at 18% 30%, #e8452c 0 22%, transparent 23%),
        radial-gradient(circle at 48% 70%, #f2b134 0 24%, transparent 25%),
        radial-gradient(circle at 80% 28%, #6a8f3a 0 20%, transparent 21%),
        radial-gradient(circle at 62% 20%, #7a3b1d 0 18%, transparent 19%),
        linear-gradient(120deg, #d9532b, #f08a3c);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    -webkit-text-fill-color: transparent;
}
.hero p { max-width: 520px; margin: 28px auto 32px; color: var(--muted); }
.hero .row { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }

.head { text-align: center; max-width: 620px; margin: 0 auto 48px; }
.head h2 { font-size: clamp(36px, 6vw, 56px); }
.head h2 b { color: var(--orange); font-weight: 400; }
.head p { margin-top: 16px; color: var(--muted); font-size: 15px; }

.stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
.stat { background: var(--white); border-radius: var(--radius-card); padding: 28px; min-height: 280px; display: flex; flex-direction: column; justify-content: space-between; }
.stat small { font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
.stat .n { font-family: 'Anton', Impact, sans-serif; font-size: 72px; line-height: 1; }
.stat .n span { color: var(--orange); }
.stat p { color: var(--muted); font-size: 13px; margin-top: 12px; }

.chips { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; margin-bottom: 32px; }
.chip { border: 1.5px solid var(--ink); background: transparent; border-radius: var(--radius-pill); padding: 9px 20px; font: 600 12px 'Inter', sans-serif; cursor: pointer; transition: all .2s; }
.chip.active, .chip[aria-pressed=true] { background: var(--ink); color: var(--cream); }

.pills { display: flex; gap: 12px; height: 380px; }
.pill { flex: 0 0 72px; border-radius: var(--radius-pill); border: 0; cursor: pointer; position: relative; overflow: hidden; color: var(--white); text-align: left; transition: flex-basis .4s ease; padding: 0; background: var(--bg); }
.pill .t { position: absolute; inset: auto 0 0 0; padding: 24px 28px; background: linear-gradient(transparent, rgba(0, 0, 0, .65)); opacity: 0; transition: opacity .3s; }
.pill .t b { display: block; font-size: 20px; margin-bottom: 6px; }
.pill .t span { font-size: 14px; display: block; max-width: 300px; }
.pill[aria-expanded=true] { flex: 1 1 360px; border-radius: 40px; }
.pill[aria-expanded=true] .t { opacity: 1; }

.closing { background: var(--orange); color: var(--white); border-radius: 48px; margin: 0 24px; text-align: center; padding: 80px 24px; }
.closing h2 { font-size: clamp(48px, 10vw, 110px); line-height: .95; }
.closing h2 span { display: block; }
.closing h2 .o { color: transparent; -webkit-text-stroke: 2px rgba(255, 255, 255, .75); }
.sdg { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; margin: 36px 0; }
.sdg span { background: var(--white); color: var(--ink); border-radius: var(--radius-pill); padding: 8px 18px; font-size: 13px; font-weight: 600; }
.closing .btn { border-color: var(--white); color: var(--white); }
.closing .btn:hover { background: var(--white); color: var(--rust); }
</style>
</head>
<body>

<div class="wrap">
    <nav>
        <a class="logo" href="index.php"><i></i>BagiPangan</a>
        <div class="nav-links">
            <a href="#makanan">Makanan</a>
            <a href="#dampak">Dampak</a>
            <a href="#cerita">Cerita</a>
            <a href="#sdgs">SDGs</a>
            <?php if ($user && in_array($user['role'], ['penyedia', 'admin'])): ?>
                <a href="donasi/tambah.php" style="color: var(--orange);">+ Tambah Donasi</a>
                <a href="donasi/riwayat.php">Kelola Donasi</a>
            <?php endif; ?>
        </div>
        
        <div class="nav-user">
            <?php if ($user): ?>
                <a href="profil/index.php" class="user-badge">
                    <span><?= htmlspecialchars($user['nama']) ?></span>
                    <span class="role-tag"><?= htmlspecialchars($user['role']) ?></span>
                </a>
                <a class="btn sm" href="profil/index.php">Profil</a>
                <a class="btn sm fill" href="auth/logout.php">Keluar</a>
            <?php else: ?>
                <a class="btn sm" href="auth/login.php">Masuk</a>
                <a class="btn sm fill" href="auth/register.php">Daftar</a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Flash message -->
    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success" style="margin-top: 16px;">
            <?= htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger" style="margin-top: 16px;">
            <?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?>
        </div>
    <?php endif; ?>

    <header class="hero">
        <h1>Bagi<br>Pangan</h1>
        <p>Makanan layak makan dari kafe, katering, dan dapur rumah dibagikan ke orang yang membutuhkan, sebelum terbuang.</p>
        <div class="row">
            <a class="btn fill" href="#makanan">Cari makanan</a>
            <?php if ($user && in_array($user['role'], ['penyedia', 'admin'])): ?>
                <a class="btn" href="donasi/tambah.php">+ Buat donasi makanan</a>
            <?php else: ?>
                <a class="btn" href="auth/register.php">Jadi penyedia</a>
            <?php endif; ?>
        </div>
    </header>
</div>

<section id="dampak" style="padding: 60px 0;">
    <div class="wrap">
        <div class="head">
            <h2>Dampak kita <b>nyata</b></h2>
            <p>BagiPangan menghubungkan penyedia makanan dan penerima di Purwokerto. Setiap pesanan tercatat sebagai porsi yang terselamatkan.</p>
        </div>
        <div class="stats">
            <div class="stat">
                <small>Porsi terselamatkan</small>
                <div>
                    <div class="n"><b id="saved" style="font-weight:400"><?= number_format($total_terselamatkan, 0, ',', '.') ?></b><span>+</span></div>
                    <p>Angka ini bertambah saat kamu memesan makanan di bawah.</p>
                </div>
            </div>
            <div class="stat">
                <small>Penyedia bergabung</small>
                <div>
                    <div class="n"><?= number_format($total_penyedia, 0, ',', '.') ?><span>+</span></div>
                    <p>Restoran, toko roti, katering, dan rumah tangga.</p>
                </div>
            </div>
            <div class="stat">
                <small>Penerima terlayani</small>
                <div>
                    <div class="n"><?= number_format($total_penerima, 0, ',', '.') ?><span>+</span></div>
                    <p>Mahasiswa, warga, dan panti yang mengambil makanan.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="makanan" style="padding: 40px 0 80px;">
    <div class="wrap">
        <div class="head">
            <h2>Makanan tersedia <b>hari ini</b></h2>
            <p>Pilih makanan, pesan porsi yang kamu perlukan, lalu ambil sebelum batas waktunya.</p>
        </div>

        <!-- Search & Filter -->
        <div style="display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 24px; justify-content: center;">
            <form method="GET" style="flex: 1; max-width: 400px;">
                <input type="text" name="search" placeholder="Cari makanan, penyedia, deskripsi..."
                       value="<?= htmlspecialchars($search) ?>"
                       style="width: 100%; padding: 12px 16px; border: 1.5px solid var(--ink); border-radius: var(--radius-pill); font: 600 14px 'Inter', sans-serif;">
            </form>
            <div class="chips" id="chips" role="group" aria-label="Filter kategori">
                <a href="?search=<?= urlencode($search) ?>&kategori=" class="chip <?= $kategori_filter === '' ? 'active' : '' ?>">Semua</a>
                <?php foreach ($kategori_list as $kat): ?>
                    <a href="?search=<?= urlencode($search) ?>&kategori=<?= urlencode($kat['nama_kategori']) ?>"
                       class="chip <?= $kategori_filter === $kat['nama_kategori'] ? 'active' : '' ?>">
                        <?= htmlspecialchars($kat['nama_kategori']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="grid" id="grid">
            <?php if (!empty($donasi_list)): ?>
                <?php foreach ($donasi_list as $row): 
                    $foto_src = !empty($row['foto']) && file_exists(__DIR__ . '/uploads/makanan/' . $row['foto'])
                        ? 'uploads/makanan/' . htmlspecialchars($row['foto'])
                        : null;
                    $bg_style = $foto_src ? "background-image: url('{$foto_src}'); background-size: cover; background-position: center;" : "background: linear-gradient(135deg, #FF6B3D, #f2b134);";
                    $until_time = date('H.i', strtotime($row['batas_waktu']));
                ?>
                    <article class="food-card" data-category="<?= htmlspecialchars($row['nama_kategori']) ?>">
                        <div class="food-thumb" style="<?= $bg_style ?>">
                            <span class="tag"><?= htmlspecialchars($row['nama_kategori']) ?></span>
                        </div>
                        <div class="food-body">
                            <h3><?= htmlspecialchars($row['nama_makanan']) ?></h3>
                            <span class="food-by"><?= htmlspecialchars($row['nama_penyedia']) ?></span>
                            <?php if (!empty($row['deskripsi'])): ?>
                                <p class="food-desc"><?= htmlspecialchars(substr($row['deskripsi'], 0, 90)) ?>...</p>
                            <?php endif; ?>
                            <div class="food-meta">
                                <span><strong><?= $row['porsi_tersisa'] ?></strong> porsi tersisa</span>
                                <span>Ambil s.d <?= $until_time ?></span>
                            </div>
                            <div class="food-actions">
                                <?php if ($row['porsi_tersisa'] > 0): ?>
                                    <button class="btn fill btn-order" style="width: 100%;" 
                                            data-id="<?= $row['id'] ?>" 
                                            data-nama="<?= htmlspecialchars($row['nama_makanan']) ?>" 
                                            data-penyedia="<?= htmlspecialchars($row['nama_penyedia']) ?>" 
                                            data-until="<?= $until_time ?>">
                                        Pesan 1 porsi
                                    </button>
                                <?php else: ?>
                                    <button class="btn" style="width: 100%;" disabled>Habis</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Fallback card jika database belum diimport -->
                <article class="food-card" data-category="Makanan berat">
                    <div class="food-thumb" style="background: linear-gradient(135deg,#e8452c,#f2b134)">
                        <span class="tag">Makanan berat</span>
                    </div>
                    <div class="food-body">
                        <h3>Nasi kotak ayam bakar</h3>
                        <span class="food-by">Katering Sari Boga</span>
                        <div class="food-meta"><span><strong>12</strong> porsi tersisa</span><span>Ambil sebelum 20.00</span></div>
                        <button class="btn fill btn-order" data-nama="Nasi kotak ayam bakar" data-penyedia="Katering Sari Boga" data-until="20.00">Pesan 1 porsi</button>
                    </div>
                </article>
            <?php endif; ?>
        </div>
    </div>
</section>

<section id="cerita" style="padding: 40px 0 80px;">
    <div class="wrap">
        <div class="head">
            <h2>Cerita dari <b>komunitas</b></h2>
            <p>Klik salah satu untuk membaca kisah penyelamatan pangan.</p>
        </div>
        <div class="pills" id="pills">
            <button class="pill" style="--bg: linear-gradient(160deg,#c8431a,#7a3b1d)" aria-expanded="false">
                <div class="t"><b class="display">Bu Tin, penjual soto</b><span>Sisa soto tiap malam kini sampai ke mahasiswa sekitar kampus, bukan ke tempat sampah.</span></div>
            </button>
            <button class="pill" style="--bg: linear-gradient(160deg,#f2b134,#e8452c)" aria-expanded="true">
                <div class="t"><b class="display">Rani, mahasiswa</b><span>Dua kali seminggu ambil makan malam gratis di akhir bulan. Uangnya cukup untuk buku.</span></div>
            </button>
            <button class="pill" style="--bg: linear-gradient(160deg,#6a8f3a,#2f5a2c)" aria-expanded="false">
                <div class="t"><b class="display">Panti Harapan</b><span>Roti dari toko sekitar rutin datang tiap sore dan dinikmati anak-anak panti.</span></div>
            </button>
            <button class="pill" style="--bg: linear-gradient(160deg,#d96b8a,#a83a5a)" aria-expanded="false">
                <div class="t"><b class="display">Toko Roti Kenanga</b><span>Roti sisa hari ini ditandai di BagiPangan. Biasanya habis terselamatkan dalam satu jam.</span></div>
            </button>
        </div>
    </div>
</section>

<section id="sdgs" style="padding-top:0">
    <div class="closing">
        <h2><span>Berbagi</span><span class="o">Bukan membuang</span><span class="o">Selamatkan pangan</span></h2>
        <div class="sdg">
            <span>SDG 12 Konsumsi bertanggung jawab</span>
            <span>SDG 2 Tanpa kelaparan</span>
            <span>SDG 13 Aksi iklim</span>
        </div>
        <a class="btn" href="#makanan">Mulai berbagi</a>
    </div>
</section>

<div class="wrap">
    <footer>
        <span>BagiPangan, proyek Responsi Praktikum Pemrograman Web</span>
        <span>Informatika, Universitas Jenderal Soedirman, 2026</span>
    </footer>
</div>

<div id="toast" role="status" aria-live="polite"></div>

<script src="assets/js/script.js"></script>
<script>
// Filter chips kategori (simpler - CSS handles this)
const chips = document.querySelectorAll('.chips a');
const cards = document.querySelectorAll('.food-card');

chips.forEach(chip => {
    chip.addEventListener('click', function(e) {
        // Only prevent default if not search input
        if (this.tagName !== 'A') return;
        e.preventDefault();

        chips.forEach(c => c.classList.remove('active'));
        this.classList.add('active');

        const selected = this.textContent.trim();

        cards.forEach(card => {
            if (selected === 'Semua' || card.dataset.category === selected) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    });
});

// Interactive pills cerita
const pillButtons = document.querySelectorAll('.pill');
pillButtons.forEach(pill => {
    pill.addEventListener('click', function() {
        pillButtons.forEach(p => p.setAttribute('aria-expanded', 'false'));
        this.setAttribute('aria-expanded', 'true');
    });
});

// Pesan porsi logic with actual backend
const isUserLoggedIn = <?= $user ? 'true' : 'false' ?>;
document.querySelectorAll('.btn-order').forEach(btn => {
    btn.addEventListener('click', function() {
        const donasiId = this.dataset.id;

        if (!isUserLoggedIn) {
            showToast('Silakan masuk akun terlebih dahulu untuk memesan.');
            setTimeout(() => {
                window.location.href = 'auth/login.php';
            }, 1200);
            return;
        }

        // Redirect to actual order creation page with donasi ID
        window.location.href = 'pesanan/buat.php?id=' + donasiId;
    });
});
</script>
</body>
</html>

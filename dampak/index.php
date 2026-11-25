<?php
// dampak/index.php - Dashboard Dampak SDGs
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

$root_path = base_url('');
$page_title = 'Dashboard Dampak SDGs | BagiPangan';

// Hitung statistik dampak
try {
    // Porsi Terselamatkan
    $porsi_terselamatkan = (int)$pdo->query("SELECT COALESCE(SUM(jumlah_porsi), 0) FROM pesanan WHERE status = 'selesai'")->fetchColumn();
    
    // Kilogram Sampah Pangan Dicegah (1 porsi = 0.35 kg)
    $kg_sampah_dicegah = round($porsi_terselamatkan * 0.35, 1);
    
    // Emisi CO2 Dicegah (1 kg sampah = 2.5 kg CO2e)
    $co2_dicegah = round($kg_sampah_dicegah * 2.5, 1);
    
    // Penyedia Terlibat
    $penyedia_terlibat = (int)$pdo->query("SELECT COUNT(DISTINCT user_id) FROM donasi")->fetchColumn();
    
    // Penerima Terbantu
    $penerima_terbantu = (int)$pdo->query("SELECT COUNT(DISTINCT user_id) FROM pesanan WHERE status = 'selesai'")->fetchColumn();
    
    // Total donasi makanan
    $total_donasi = (int)$pdo->query("SELECT COUNT(*) FROM donasi")->fetchColumn();
    
    // Donasi aktif
    $donasi_aktif = (int)$pdo->query("SELECT COUNT(*) FROM donasi WHERE status = 'tersedia' AND batas_waktu > NOW()")->fetchColumn();
    
    // Donasi habis
    $donasi_habis = (int)$pdo->query("SELECT COUNT(*) FROM donasi WHERE status = 'habis'")->fetchColumn();
    
    // Rating rata-rata
    $avg_rating = $pdo->query("SELECT COALESCE(AVG(rating), 0) FROM ulasan")->fetchColumn();
    $total_ulasan = (int)$pdo->query("SELECT COUNT(*) FROM ulasan")->fetchColumn();
    
    // Data untuk chart - Porsi per bulan (6 bulan terakhir)
    $chart_data = [];
    for ($i = 5; $i >= 0; $i--) {
        $month_start = date('Y-m-01', strtotime("-$i months"));
        $month_end = date('Y-m-t 23:59:59', strtotime("-$i months"));
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(jumlah_porsi), 0) FROM pesanan WHERE status = 'selesai' AND waktu_ambil BETWEEN ? AND ?");
        $stmt->execute([$month_start, $month_end]);
        $value = (int)$stmt->fetchColumn();
        $chart_data[] = [
            'bulan' => date('M Y', strtotime("-$i months")),
            'porsi' => $value
        ];
    }
    
    // Data kategori terpopuler
    $stmt = $pdo->query("
        SELECT k.nama_kategori, COUNT(p.id) as total_pesanan
        FROM pesanan p
        JOIN donasi d ON p.donasi_id = d.id
        JOIN kategori k ON d.kategori_id = k.id
        WHERE p.status = 'selesai'
        GROUP BY k.id
        ORDER BY total_pesanan DESC
    ");
    $kategori_stats = $stmt->fetchAll();
    
} catch (Exception $e) {
    $porsi_terselamatkan = $kg_sampah_dicegah = $co2_dicegah = $penyedia_terlibat = $penerima_terbantu = 0;
    $total_donasi = $donasi_aktif = $donasi_habis = 0;
    $avg_rating = 0;
    $total_ulasan = 0;
    $chart_data = [];
    $kategori_stats = [];
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="wrap" style="max-width: 1120px;">
    <h1>Dashboard Dampak <span style="color: var(--orange);">SDGs</span></h1>
    <p class="text-muted" style="margin-bottom: 32px;">Visualisasi real-time dampak penyelamatan pangan oleh komunitas BagiPangan</p>

    <!-- Hero Stats -->
    <div class="stats" style="margin-bottom: 40px;">
        <div class="stat">
            <small>Porsi Terselamatkan</small>
            <div>
                <div class="n"><b><?= number_format($porsi_terselamatkan, 0, ',', '.') ?></b><span>+</span></div>
                <p>Total porsi makanan terselamatkan dari pembuangan</p>
            </div>
        </div>
        <div class="stat">
            <small>Sampah Pangan Dicegah</small>
            <div>
                <div class="n"><b><?= number_format($kg_sampah_dicegah, 1, ',', '.') ?></b><span> kg</span></div>
                <p>Asumsi 1 porsi = 0,35 kg makanan</p>
            </div>
        </div>
        <div class="stat">
            <small>Emisi CO₂ Dicegah</small>
            <div>
                <div class="n"><b><?= number_format($co2_dicegah, 1, ',', '.') ?></b><span> kg</span></div>
                <p>Asumsi 1 kg sampah = 2,5 kg CO₂e</p>
            </div>
        </div>
    </div>

    <!-- Secondary Stats -->
    <div class="stats" style="margin-bottom: 40px;">
        <div class="stat">
            <small>Penyedia Terlibat</small>
            <div>
                <div class="n"><?= number_format($penyedia_terlibat, 0, ',', '.') ?><span>+</span></div>
                <p>Restoran, toko roti, katering, rumah tangga</p>
            </div>
        </div>
        <div class="stat">
            <small>Penerima Terbantu</small>
            <div>
                <div class="n"><?= number_format($penerima_terbantu, 0, ',', '.') ?><span>+</span></div>
                <p>Mahasiswa, warga, panti yang mengambil makanan</p>
            </div>
        </div>
        <div class="stat">
            <small>Rating Rata-rata</small>
            <div>
                <div class="n"><?= number_format($avg_rating, 1) ?><span>/5.0</span></div>
                <p>Dari <?= $total_ulasan ?> ulasan komunitas</p>
            </div>
        </div>
    </div>

    <!-- Chart: Porsi per Bulan -->
    <div class="card" style="padding: 24px; margin-bottom: 32px;">
        <h3 style="margin-bottom: 24px;">Porsi Terselamatkan per Bulan (6 Bulan Terakhir)</h3>
        <div style="display: flex; align-items: end; gap: 16px; height: 280px; padding: 20px 0;">
            <?php 
            $max_porsi = max(array_column($chart_data, 'porsi'));
            $max_porsi = max($max_porsi, 1); // avoid division by zero
            foreach ($chart_data as $data): 
                $height = $max_porsi > 0 ? ($data['porsi'] / $max_porsi) * 100 : 0;
            ?>
                <div style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 8px;">
                    <div style="width: 100%; background: var(--line); border-radius: 4px; height: 200px; position: relative;">
                        <div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(180deg, #FF6B3D, #f2b134); border-radius: 4px 4px 0 0; height: <?= $height ?>%; transition: height 0.5s ease;"></div>
                    </div>
                    <span style="font-weight: 600; font-family: 'Anton', sans-serif; color: var(--orange);"><?= $data['porsi'] ?></span>
                    <span class="text-muted" style="font-size: 11px; text-align: center;"><?= $data['bulan'] ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Kategori Terpopuler & SDGs -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
        <!-- Kategori Terpopuler -->
        <div class="card" style="padding: 24px;">
            <h3 style="margin-bottom: 24px;">Kategori Makanan Terpopuler</h3>
            <?php if (empty($kategori_stats)): ?>
                <p class="text-muted" style="text-align: center; padding: 40px 0;">Belum ada data pesanan selesai</p>
            <?php else: 
                $max_cat = max(array_column($kategori_stats, 'total_pesanan'));
            ?>
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <?php foreach ($kategori_stats as $cat): 
                        $pct = $max_cat > 0 ? ($cat['total_pesanan'] / $max_cat) * 100 : 0;
                    ?>
                        <div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                                <span class="text-muted" style="text-transform: capitalize;"><?= htmlspecialchars($cat['nama_kategori']) ?></span>
                                <span style="font-weight: 600; font-family: 'Anton', sans-serif;"><?= $cat['total_pesanan'] ?> pesanan</span>
                            </div>
                            <div style="height: 8px; background: var(--line); border-radius: 4px; overflow: hidden;">
                                <div style="width: <?= $pct ?>%; height: 100%; background: linear-gradient(90deg, #FF6B3D, #f2b134); border-radius: 4px;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- SDGs Contribution -->
        <div class="card" style="padding: 24px;">
            <h3 style="margin-bottom: 24px;">Kontribusi SDGs</h3>
            <div style="display: flex; flex-direction: column; gap: 16px;">
                <div style="display: flex; gap: 16px; align-items: center; padding: 16px; background: var(--green-bg); border-radius: 12px; border: 1px solid rgba(45, 138, 78, 0.2);">
                    <div style="width: 56px; height: 56px; background: var(--green); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px;">🍽️</div>
                    <div>
                        <div style="font-weight: 600; text-transform: uppercase; color: var(--green); font-size: 12px;">SDG 2: Zero Hunger</div>
                        <div style="font-size: 13px; color: var(--muted); margin-top: 4px;">Mengurangi kelaparan dengan menyelamatkan <?= number_format($porsi_terselamatkan, 0, ',', '.') ?> porsi makanan untuk masyarakat.</div>
                    </div>
                </div>
                
                <div style="display: flex; gap: 16px; align-items: center; padding: 16px; background: #e8f5e9; border-radius: 12px; border: 1px solid rgba(45, 138, 78, 0.1);">
                    <div style="width: 56px; height: 56px; background: #43a047; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px;">♻️</div>
                    <div>
                        <div style="font-weight: 600; text-transform: uppercase; color: #2e7d32; font-size: 12px;">SDG 12: Responsible Consumption</div>
                        <div style="font-size: 13px; color: var(--muted); margin-top: 4px;">Mencegah <?= number_format($kg_sampah_dicegah, 1, ',', '.') ?> kg sampah pangan dari TPA melalui redistribusi surplus makanan.</div>
                    </div>
                </div>
                
                <div style="display: flex; gap: 16px; align-items: center; padding: 16px; background: #e3f2fd; border-radius: 12px; border: 1px solid rgba(33, 150, 243, 0.1);">
                    <div style="width: 56px; height: 56px; background: #1e88e5; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px;">🌍</div>
                    <div>
                        <div style="font-weight: 600; text-transform: uppercase; color: #1565c0; font-size: 12px;">SDG 13: Climate Action</div>
                        <div style="font-size: 13px; color: var(--muted); margin-top: 4px;">Mengurangi emisi <?= number_format($co2_dicegah, 1, ',', '.') ?> kg CO₂e dari dekomposisi makanan di TPA.</div>
                    </div>
                </div>
                
                <div style="display: flex; gap: 16px; align-items: center; padding: 16px; background: #fff3e0; border-radius: 12px; border: 1px solid rgba(255, 152, 0, 0.1);">
                    <div style="width: 56px; height: 56px; background: #f57c00; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px;">🤝</div>
                    <div>
                        <div style="font-weight: 600; text-transform: uppercase; color: #e65100; font-size: 12px;">SDG 17: Partnerships</div>
                        <div style="font-size: 13px; color: var(--muted); margin-top: 4px;">Menghubungkan <?= number_format($penyedia_terlibat, 0, ',', '.') ?> penyedia & <?= number_format($penerima_terbantu, 0, ',', '.') ?> penerima dalam ekosistem berbagi pangan.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Overview Summary -->
    <div class="card" style="padding: 24px; margin-top: 32px;">
        <h3 style="margin-bottom: 24px;">Ringkasan Operasional</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 24px;">
            <div style="text-align: center; padding: 20px; background: var(--cream); border-radius: 16px;">
                <div style="font-family: 'Anton', sans-serif; font-size: 48px; color: var(--orange);"><?= $total_donasi ?></div>
                <div class="text-muted" style="text-transform: uppercase; font-size: 12px; font-weight: 600;">Total Donasi Dibuat</div>
            </div>
            <div style="text-align: center; padding: 20px; background: var(--cream); border-radius: 16px;">
                <div style="font-family: 'Anton', sans-serif; font-size: 48px; color: var(--green);"><?= $donasi_aktif ?></div>
                <div class="text-muted" style="text-transform: uppercase; font-size: 12px; font-weight: 600;">Donasi Aktif Sekarang</div>
            </div>
            <div style="text-align: center; padding: 20px; background: var(--cream); border-radius: 16px;">
                <div style="font-family: 'Anton', sans-serif; font-size: 48px; color: #6c757d;"><?= $donasi_habis ?></div>
                <div class="text-muted" style="text-transform: uppercase; font-size: 12px; font-weight: 600;">Donasi Habis Terselamatkan</div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
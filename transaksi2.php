<?php
/**
 * Program Manajemen Data Transaksi Pinjol Berbasis PHP
 * Memenuhi kriteria:
 * - Minimal 4 variabel dengan tipe data berbeda
 * - Minimal 1 percabangan (if-else)
 * - Minimal 1 perulangan (while / foreach)
 * - Minimal 1 function buatan sendiri
 * - Output berjalan lancar di localhost
 * - Tampilan menarik dan rapi
 */

// Inisialisasi Session untuk penyimpanan data sementara di server lokal
session_start();

if (!isset($_SESSION['data_pinjol'])) {
    // Data awal dummy untuk demonstrasi perulangan
    $_SESSION['data_pinjol'] = [
        ["id" => "PCL-001", "nama" => "Siti Rahma", "jumlah" => 2500000.00, "tenor" => 6, "status" => "Lancar"],
        ["id" => "PCL-002", "nama" => "Ahmad Fauzi", "jumlah" => 5000000.00, "tenor" => 12, "status" => "Macet"]
    ];
}

/**
 * Function buatan sendiri untuk menghitung estimasi total pengembalian
 * @param float $jumlah_pinjaman
 * @param int $tenor_bulan
 * @return float
 */
function hitungTotalPengembalian($jumlah_pinjaman, $tenor_bulan) {
    // Suku bunga tetap per tahun diasumsikan 12% (1% per bulan)
    $bunga_per_bulan = 0.01;
    $total_bunga = $jumlah_pinjaman * $bunga_per_bulan * $tenor_bulan;
    return $jumlah_pinjaman + $total_bunga;
}

// Penanganan Form Tambah Data
$pesan_sukses = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_data'])) {
    // 1. Variabel String ($nama_peminjam, $status_kredit)
    $nama_peminjam = trim($_POST['nama']);
    $status_kredit = "Lancar"; 

    // 2. Variabel Float / Double ($jumlah_pinjaman)
    $jumlah_pinjaman = (float) $_POST['jumlah'];

    // 3. Variabel Integer ($tenor_bulan)
    $tenor_bulan = (int) $_POST['tenor'];

    // 4. Variabel Boolean ($is_valid) untuk validasi percabangan
    $is_valid = true;

    if (empty($nama_peminjam) || $jumlah_pinjaman <= 0 || $tenor_bulan <= 0) {
        $is_valid = false;
    }

    // Percabangan (If-Else)
    if ($is_valid) {
        $nomor_urut = count($_SESSION['data_pinjol']) + 1;
        $id_transaksi = "PCL-" . str_pad($nomor_urut, 3, "0", STR_PAD_LEFT);

        $transaksi_baru = [
            "id" => $id_transaksi,
            "nama" => $nama_peminjam,
            "jumlah" => $jumlah_pinjaman,
            "tenor" => $tenor_bulan,
            "status" => $status_kredit
        ];

        // Menambahkan data ke array session
        $_SESSION['data_pinjol'][] = $transaksi_baru;
        $pesan_sukses = "Data transaksi berhasil ditambahkan!";
    }
}

// Penanganan Hapus Data
if (isset($_GET['hapus'])) {
    $index_hapus = (int) $_GET['hapus'];
    if (isset($_SESSION['data_pinjol'][$index_hapus])) {
        unset($_SESSION['data_pinjol'][$index_hapus]);
        $_SESSION['data_pinjol'] = array_values($_SESSION['data_pinjol']);
        header("Location: " . strtok($_SERVER['REQUEST_URI'], '?'));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Manajemen Data Transaksi Pinjol</title>
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --text-color: #1e293b;
            --border-color: #e2e8f0;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            margin: 0;
            padding: 30px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: var(--card-bg);
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
        }
        h2 {
            margin-top: 0;
            color: var(--primary);
            text-align: center;
            font-size: 28px;
            margin-bottom: 25px;
        }
        .alert {
            background-color: #d1fae5;
            color: #065f46;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
            background: #f1f5f9;
            padding: 20px;
            border-radius: 12px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group.full {
            grid-column: span 2;
        }
        label {
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }
        input, select {
            padding: 10px 14px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
        }
        input:focus {
            border-color: var(--primary);
        }
        button {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 12px 20px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        button:hover {
            background-color: var(--primary-hover);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 14px 16px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        th {
            background-color: #f8fafc;
            font-weight: 600;
            color: #475569;
        }
        tr:hover {
            background-color: #f8fafc;
        }
        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-lancar { background: #d1fae5; color: #065f46; }
        .badge-macet { background: #fee2e2; color: #991b1b; }
        .btn-delete {
            color: #ef4444;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
        }
        .btn-delete:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>MANAJEMEN DATA TRANSAKSI PINJAMAN ONLINE</h2>

    <?php if (!empty($pesan_sukses)): ?>
        <div class="alert"><?= $pesan_sukses ?></div>
    <?php endif; ?>

    <!-- Form Input Data -->
    <form action="" method="POST" class="form-grid">
        <div class="form-group">
            <label>Nama Peminjam</label>
            <input type="text" name="nama" placeholder="Masukkan nama lengkap" required>
        </div>
        <div class="form-group">
            <label>Jumlah Pinjaman (Rp)</label>
            <input type="number" name="jumlah" step="50000" placeholder="Contoh: 3000000" required>
        </div>
        <div class="form-group">
            <label>Tenor (Bulan)</label>
            <input type="number" name="tenor" placeholder="Contoh: 12" required>
        </div>
        <div class="form-group" style="justify-content: flex-end;">
            <button type="submit" name="tambah_data">Simpan Transaksi</button>
        </div>
    </form>

    <!-- Tabel Data Peminjam (Menggunakan Perulangan / Loop) -->
    <h3>Daftar Transaksi Aktif</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Pinjaman Awal</th>
                <th>Tenor</th>
                <th>Estimasi Total Pengembalian</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            // Perulangan minimal 1 (foreach loop untuk menampilkan data)
            if (empty($_SESSION['data_pinjol'])) {
                echo '<tr><td colspan="7" style="text-align: center; color: #64748b;">Belum ada data transaksi.</td></tr>';
            } else {
                foreach ($_SESSION['data_pinjol'] as $index => $item) {
                    // Memanggil function buatan sendiri: hitungTotalPengembalian()
                    $total_kembali = hitungTotalPengembalian($item['jumlah'], $item['tenor']);
                    
                    // Percabangan untuk menentukan kelas badge status
                    $badge_class = ($item['status'] == 'Lancar') ? 'badge-lancar' : 'badge-macet';
                    
                    echo "<tr>";
                    echo "<td><strong>{$item['id']}</strong></td>";
                    echo "<td>" . htmlspecialchars($item['nama']) . "</td>";
                    echo "<td>Rp " . number_format($item['jumlah'], 0, ',', '.') . "</td>";
                    echo "<td>{$item['tenor']} Bulan</td>";
                    echo "<td>Rp " . number_format($total_kembali, 0, ',', '.') . "</td>";
                    echo "<td><span class='badge {$badge_class}'>{$item['status']}</span></td>";
                    echo "<td><a href='?hapus={$index}' class='btn-delete' onclick=\"return confirm('Yakin ingin menghapus data ini?')\">Hapus</a></td>";
                    echo "</tr>";
                }
            }
            ?>
        </tbody>
    </table>
</div>
</body>
</html>
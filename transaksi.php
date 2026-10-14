<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi Manajemen Data Pinjol</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background-color: #f4f6f9; }
        .container { max-width: 900px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #333; }
        form { margin-bottom: 30px; display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .form-group { display: flex; flex-direction: column; }
        .form-group label { margin-bottom: 5px; font-weight: bold; }
        .form-group input { padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        .full-width { grid-column: span 2; }
        button { padding: 10px; background-color: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background-color: #218838; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 10px; text-align: left; }
        th { background-color: #007bff; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .btn-hapus { color: red; text-decoration: none; font-weight: bold; cursor: pointer; border: none; background: none; font-size: 14px; }
        .btn-hapus:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="container">
    <h2>Aplikasi Manajemen Data Pinjol</h2>

    <!-- Form Input -->
    <form id="formPinjol">
        <div class="form-group">
            <label>Nama Peminjam:</label>
            <input type="text" id="nama" required>
        </div>
        <div class="form-group">
            <label>Jumlah Pinjaman (Rp):</label>
            <input type="number" id="jumlah" min="1" required>
        </div>
        <div class="form-group">
            <label>Bunga (% per tahun):</label>
            <input type="number" id="bunga" step="0.1" min="0" required>
        </div>
        <div class="form-group">
            <label>Tenor (Bulan):</label>
            <input type="number" id="tenor" min="1" required>
        </div>
        <div class="form-group full-width">
            <button type="submit">Tambah Transaksi</button>
        </div>
    </form>

    <!-- Tabel Data -->
    <h3>Daftar Transaksi</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Pinjaman</th>
                <th>Bunga</th>
                <th>Tenor</th>
                <th>Total Bayar</th>
                <th>Cicilan/Bln</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody id="tabelData">
            <!-- Data akan masuk lewat JavaScript -->
        </tbody>
    </table>
</div>

<script>
    // Ambil data dari localStorage atau buat array kosong
    let daftarTransaksi = JSON.parse(localStorage.getItem('transaksi_pinjol')) || [];

    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
    }

    function renderTabel() {
        const tbody = document.getElementById('tabelData');
        tbody.innerHTML = '';

        if (daftarTransaksi.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8" style="text-align: center;">Belum ada data transaksi.</td></tr>`;
            return;
        }

        daftarTransaksi.forEach((t, index) => {
            let row = `<tr>
                <td>${t.id}</td>
                <td>${t.nama}</td>
                <td>${formatRupiah(t.jumlah)}</td>
                <td>${t.bunga}%</td>
                <td>${t.tenor} Bln</td>
                <td>${formatRupiah(t.totalBayar)}</td>
                <td>${formatRupiah(t.cicilanBulan)}</td>
                <td><button class="btn-hapus" onclick="hapusData(${index})">Hapus</button></td>
            </tr>`;
            tbody.innerHTML += row;
        });
    }

    document.getElementById('formPinjol').addEventListener('submit', function(e) {
        e.preventDefault();

        const nama = document.getElementById('nama').value;
        const jumlah = parseFloat(document.getElementById('jumlah').value);
        const bunga = parseFloat(document.getElementById('bunga').value);
        const tenor = parseInt(document.getElementById('tenor').value);

        const idTransaksi = "PCL-" + String(daftarTransaksi.length + 1).padStart(3, '0');
        
        // Perhitungan total bunga dan cicilan
        const totalBunga = jumlah * (bunga / 100) * (tenor / 12);
        const totalBayar = jumlah + totalBunga;
        const cicilanBulan = totalBayar / tenor;

        const transaksiBaru = {
            id: idTransaksi,
            nama: nama,
            jumlah: jumlah,
            bunga: bunga,
            tenor: tenor,
            totalBayar: totalBayar,
            cicilanBulan: cicilanBulan
        };

        daftarTransaksi.push(transaksiBaru);
        localStorage.setItem('transaksi_pinjol', JSON.stringify(daftarTransaksi));
        
        renderTabel();
        this.reset();
    });

    function hapusData(index) {
        if (confirm('Yakin ingin menghapus data ini?')) {
            daftarTransaksi.splice(index, 1);
            localStorage.setItem('transaksi_pinjol', JSON.stringify(daftarTransaksi));
            renderTabel();
        }
    }

    // Jalankan render saat halaman dimuat
    renderTabel();
</script>

</body>
</html>
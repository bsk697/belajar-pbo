<?php
// Class harus dimuat SEBELUM session_start() agar objek di session bisa dibaca
require_once __DIR__ . '/DataMahasiswa.php';
session_start();

function aman(mixed $v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

$repo  = new DataMahasiswa($_SESSION['mahasiswa'] ?? []);
$error = '';
$pesan = $_SESSION['pesan'] ?? '';
unset($_SESSION['pesan']);

$form = ['aksi' => 'tambah', 'nim_lama' => '', 'nim' => '', 'nama' => '', 'prodi' => '',
         'semester' => '', 'ukt' => '', 'jenis' => 'reguler', 'potongan' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!hash_equals($_SESSION['csrf'], (string)($_POST['csrf'] ?? ''))) {
            throw new InvalidArgumentException('Token tidak valid, muat ulang halaman');
        }
        $aksi = (string)($_POST['aksi'] ?? 'tambah');

        if ($aksi === 'hapus') {
            $nim = (string)($_POST['nim'] ?? '');
            if (!$repo->hapus($nim)) throw new InvalidArgumentException('NIM tidak ditemukan');
            $_SESSION['mahasiswa'] = $repo->semua();
            $_SESSION['pesan'] = 'Data berhasil dihapus';
            header('Location: index.php'); exit;
        }

        // Simpan input untuk mengisi ulang form bila ada error
        $form = array_merge($form, [
            'aksi'     => $aksi,
            'nim_lama' => (string)($_POST['nim_lama'] ?? ''),
            'nim'      => trim((string)($_POST['nim'] ?? '')),
            'nama'     => trim((string)($_POST['nama'] ?? '')),
            'prodi'    => trim((string)($_POST['prodi'] ?? '')),
            'semester' => (string)($_POST['semester'] ?? ''),
            'ukt'      => (string)($_POST['ukt'] ?? ''),
            'jenis'    => (string)($_POST['jenis'] ?? ''),
            'potongan' => (string)($_POST['potongan'] ?? ''),
        ]);

        $semester = filter_var($form['semester'], FILTER_VALIDATE_INT);
        $ukt      = filter_var($form['ukt'], FILTER_VALIDATE_INT);
        if ($semester === false || $ukt === false) {
            throw new InvalidArgumentException('Semester dan UKT harus bilangan bulat');
        }

        if ($form['jenis'] === 'reguler') {
            $m = new MahasiswaReguler($form['nim'], $form['nama'], $form['prodi'], $semester, $ukt);
        } elseif ($form['jenis'] === 'beasiswa') {
            $potongan = filter_var($form['potongan'], FILTER_VALIDATE_INT);
            if ($potongan === false) throw new InvalidArgumentException('Potongan harus bilangan bulat');
            $m = new MahasiswaBeasiswa($form['nim'], $form['nama'], $form['prodi'], $semester, $ukt, $potongan);
        } else {
            throw new InvalidArgumentException('Jenis mahasiswa tidak dikenal');
        }

        if ($aksi === 'ubah') {
            $ok = $repo->ubah($form['nim_lama'], $m);
            $gagal = 'Data edit tidak cocok: NIM tidak boleh diubah';
            $sukses = 'Data berhasil diubah';
        } else {
            $ok = $repo->tambah($m);
            $gagal = 'NIM sudah terdaftar';
            $sukses = 'Data berhasil ditambahkan';
        }
        if (!$ok) throw new InvalidArgumentException($gagal);

        $_SESSION['mahasiswa'] = $repo->semua();
        $_SESSION['pesan'] = $sukses;
        header('Location: index.php'); exit;
    } catch (InvalidArgumentException $e) {
        $error = $e->getMessage();
    }
} elseif (isset($_GET['edit'])) {
    // Mode edit: isi form dari data yang ada
    $m = $repo->cari((string)$_GET['edit']);
    if ($m !== null) {
        $form = [
            'aksi' => 'ubah', 'nim_lama' => $m->getNim(), 'nim' => $m->getNim(),
            'nama' => $m->getNama(), 'prodi' => $m->getProdi(),
            'semester' => (string)$m->getSemester(),
            'ukt' => (string)$m->getUkt(),
            'jenis' => $m instanceof MahasiswaBeasiswa ? 'beasiswa' : 'reguler',
            'potongan' => $m instanceof MahasiswaBeasiswa ? (string)$m->getPotonganPersen() : '',
        ];
    }
}

$q = trim((string)($_GET['q'] ?? ''));
$daftar = $repo->filter($q);
$mode = $form['aksi'] === 'ubah';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>CRUD Mahasiswa – Inheritance</title>
<style>
  :root { --ink:#1f2933; --muted:#667085; --line:#d9dee5; --bg:#f6f7f9; --accent:#1d4e89; --bad:#b42318; --ok:#067647; }
  * { box-sizing: border-box; }
  body { margin:0; font:16px/1.5 system-ui, "Segoe UI", sans-serif; color:var(--ink); background:var(--bg); }
  main { max-width: 960px; margin: 0 auto; padding: 24px 16px 48px; }
  h1 { font-size: 1.5rem; margin: 0 0 4px; }
  h2 { font-size: 1.1rem; margin: 0 0 12px; }
  p.sub { margin: 0 0 20px; color: var(--muted); }
  section { background:#fff; border:1px solid var(--line); border-radius:8px; padding:20px; margin-bottom:20px; }
  .grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:12px; }
  label { display:block; font-size:.9rem; font-weight:600; margin-bottom:4px; }
  input, select { width:100%; padding:8px 10px; border:1px solid var(--line); border-radius:6px; font:inherit; background:#fff; }
  input[readonly] { background:#eef1f5; color:var(--muted); }
  input:focus, select:focus, button:focus-visible, a:focus-visible { outline:2px solid var(--accent); outline-offset:1px; }
  button, .btn { display:inline-block; padding:8px 14px; border:1px solid var(--accent); background:var(--accent); color:#fff; border-radius:6px; font:inherit; cursor:pointer; text-decoration:none; }
  button.ghost, .btn.ghost { background:#fff; color:var(--accent); }
  button.danger { background:#fff; color:var(--bad); border-color:var(--bad); }
  .actions { margin-top:16px; display:flex; gap:8px; flex-wrap:wrap; }
  .alert { padding:10px 12px; border-radius:6px; margin-bottom:16px; }
  .alert.err { background:#fef3f2; color:var(--bad); border:1px solid #fecdca; }
  .alert.ok { background:#ecfdf3; color:var(--ok); border:1px solid #abefc6; }
  .tablewrap { overflow-x:auto; }
  table { width:100%; border-collapse:collapse; }
  th, td { text-align:left; padding:8px 10px; border-bottom:1px solid var(--line); white-space:nowrap; }
  td.num, th.num { text-align:right; }
  .row-actions { display:flex; gap:6px; }
  .row-actions form { margin:0; }
  .row-actions button, .row-actions .btn { padding:4px 10px; font-size:.9rem; }
  .search { display:flex; gap:8px; margin-bottom:12px; }
  .search input { max-width:320px; }
  .hint { font-size:.85rem; color:var(--muted); margin-top:4px; }
  .empty { color:var(--muted); padding:16px 0; }
  [hidden] { display:none !important; }
</style>
</head>
<body>
<main>
  <h1>Data Mahasiswa</h1>
  <p class="sub">Reguler dan Beasiswa, dibuat dengan inheritance (parent: Mahasiswa).</p>

  <?php if ($pesan): ?><div class="alert ok" role="status"><?= aman($pesan) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert err" role="alert"><?= aman($error) ?></div><?php endif; ?>

  <section>
    <h2><?= $mode ? 'Edit mahasiswa' : 'Tambah mahasiswa' ?></h2>
    <form method="post" action="index.php">
      <input type="hidden" name="csrf" value="<?= aman($_SESSION['csrf']) ?>">
      <input type="hidden" name="aksi" value="<?= $mode ? 'ubah' : 'tambah' ?>">
      <input type="hidden" name="nim_lama" value="<?= aman($form['nim_lama']) ?>">
      <div class="grid">
        <div>
          <label for="nim">NIM</label>
          <input id="nim" name="nim" value="<?= aman($form['nim']) ?>" <?= $mode ? 'readonly' : '' ?> required>
          <?php if ($mode): ?><div class="hint">NIM tidak dapat diubah.</div><?php endif; ?>
        </div>
        <div>
          <label for="nama">Nama</label>
          <input id="nama" name="nama" value="<?= aman($form['nama']) ?>" required>
        </div>
        <div>
          <label for="prodi">Program studi</label>
          <input id="prodi" name="prodi" value="<?= aman($form['prodi']) ?>" required>
        </div>
        <div>
          <label for="semester">Semester (1–14)</label>
          <input id="semester" name="semester" type="number" min="1" max="14" value="<?= aman($form['semester']) ?>" required>
        </div>
        <div>
          <label for="jenis">Jenis mahasiswa</label>
          <select id="jenis" name="jenis">
            <option value="reguler" <?= $form['jenis'] === 'reguler' ? 'selected' : '' ?>>Reguler</option>
            <option value="beasiswa" <?= $form['jenis'] === 'beasiswa' ? 'selected' : '' ?>>Beasiswa</option>
          </select>
        </div>
        <div>
          <label for="ukt">UKT (Rp)</label>
          <input id="ukt" name="ukt" type="number" min="0" value="<?= aman($form['ukt']) ?>" required>
        </div>
        <div id="boxPotongan" <?= $form['jenis'] === 'beasiswa' ? '' : 'hidden' ?>>
          <label for="potongan">Potongan beasiswa (%)</label>
          <input id="potongan" name="potongan" type="number" min="0" max="100" value="<?= aman($form['potongan']) ?>">
        </div>
      </div>
      <div class="actions">
        <button type="submit"><?= $mode ? 'Simpan perubahan' : 'Tambah data' ?></button>
        <?php if ($mode): ?><a class="btn ghost" href="index.php">Batal</a><?php endif; ?>
      </div>
    </form>
  </section>

  <section>
    <h2>Daftar mahasiswa</h2>
    <form class="search" method="get" action="index.php">
      <input type="search" name="q" value="<?= aman($q) ?>" placeholder="Cari NIM atau nama" aria-label="Cari NIM atau nama">
      <button type="submit" class="ghost">Cari</button>
      <?php if ($q !== ''): ?><a class="btn ghost" href="index.php">Reset</a><?php endif; ?>
    </form>

    <?php if (!$daftar): ?>
      <p class="empty"><?= $q !== '' ? 'Tidak ada data yang cocok dengan pencarian.' : 'Belum ada data. Tambahkan mahasiswa lewat form di atas.' ?></p>
    <?php else: ?>
    <div class="tablewrap">
      <table>
        <thead>
          <tr>
            <th>NIM</th><th>Nama</th><th>Prodi</th><th class="num">Smt</th>
            <th>Jenis</th><th class="num">UKT</th><th class="num">Potongan</th><th class="num">Biaya akhir</th><th>Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($daftar as $m): ?>
          <tr>
            <td><?= aman($m->getNim()) ?></td>
            <td><?= aman($m->getNama()) ?></td>
            <td><?= aman($m->getProdi()) ?></td>
            <td class="num"><?= aman($m->getSemester()) ?></td>
            <td><?= aman($m->getJenis()) ?></td>
            <td class="num"><?= aman(number_format($m->getUkt(), 0, ',', '.')) ?></td>
            <td class="num"><?= $m instanceof MahasiswaBeasiswa ? aman($m->getPotonganPersen()) . '%' : '-' ?></td>
            <td class="num"><?= aman(number_format($m->hitungBiaya(), 0, ',', '.')) ?></td>
            <td>
              <div class="row-actions">
                <a class="btn ghost" href="index.php?edit=<?= urlencode($m->getNim()) ?>">Edit</a>
                <form method="post" action="index.php" onsubmit="return confirm('Hapus data ini?');">
                  <input type="hidden" name="csrf" value="<?= aman($_SESSION['csrf']) ?>">
                  <input type="hidden" name="aksi" value="hapus">
                  <input type="hidden" name="nim" value="<?= aman($m->getNim()) ?>">
                  <button type="submit" class="danger">Hapus</button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </section>
</main>

<script>
  const jenis = document.getElementById('jenis');
  const box = document.getElementById('boxPotongan');
  const potongan = document.getElementById('potongan');
  function toggle() {
    const b = jenis.value === 'beasiswa';
    box.hidden = !b;
    potongan.required = b;
  }
  jenis.addEventListener('change', toggle);
  toggle();
</script>
</body>
</html>
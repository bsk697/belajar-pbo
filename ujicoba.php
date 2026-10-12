
<?php
// Menyiapkan variabel
$nim = "";
$nama = "";
$fakultas = "";
$prodi = "";
$asal_sekolah = "";
$semester = "";
$hobi = [];

// Mengecek apakah form sudah dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nim = $_POST["nim"];
    $nama = $_POST["nama"];
    $fakultas = $_POST["fakultas"];
    $prodi = $_POST["prodi"];
    $asal_sekolah = $_POST["asal_sekolah"];
    $semester = $_POST["semester"];

    // Mengambil hobi
    if (isset($_POST["hobi"])) {
        $hobi = $_POST["hobi"];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Data Mahasiswa</title>

    <style>
        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            margin: 0;
            padding: 0;
            /* Background diganti dengan gambar tema anime */
            background: url("belajar-pbo/anime.jpg") no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
        }

        .container {
            width: 90%;
            max-width: 600px;
            margin: 50px auto;
            padding-bottom: 50px;
        }

        .card {
            background: url("https://i.pinimg.com/736x/50/78/fb/5078fbb6a3a481c4448f6b22d9b8c881.jpg") no-repeat center center;
            background-size: cover;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            color: white; /* Supaya teks tulisannya otomatis jadi warna putih agar terbaca */
        }

        h1 {
            text-align: center;
            color: #fffefe;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-top: 15px;
            margin-bottom: 7px;
            font-weight: bold;
            color: #f7f5f5;
        }

        input[type="text"],
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 15px;
            background: #fff;
        }

        input[type="text"]:focus,
        select:focus {
            outline: none;
            border-color: #667eea;
        }

        .hobi {
            margin-top: 10px;
        }

        .hobi label {
            display: inline-block;
            margin-right: 15px;
            font-weight: normal;
        }

        .button {
            width: 100%;
            margin-top: 25px;
            padding: 13px;
            border: none;
            border-radius: 8px;
            background: #667eea;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        .button:hover {
            background: #5568d9;
        }

        .hasil {
            margin-top: 25px;
            padding: 20px;
            background: #f3f4ff;
            border-left: 5px solid #667eea;
            border-radius: 8px;
        }

        .hasil h2 {
            margin-top: 0;
            color: #f8f8f8;
        }

        .hasil p {
            margin: 8px 0;
        }
    </style>
</head>

<body>

<div class="container">
    <div class="card">

        <h1>Form Data Mahasiswa</h1>

        <form method="POST" action="">

            <label for="nim">NIM</label>
            <input 
                type="text" 
                id="nim" 
                name="nim"
                placeholder="Masukkan NIM"
                value="<?php echo htmlspecialchars($nim); ?>"
                required
            >

            <label for="nama">Nama</label>
            <input 
                type="text" 
                id="nama" 
                name="nama"
                placeholder="Masukkan nama lengkap"
                value="<?php echo htmlspecialchars($nama); ?>"
                required
            >

            <label for="fakultas">Fakultas</label>
            <select id="fakultas" name="fakultas" required>
                <option value="">-- Pilih Fakultas --</option>
                <option value="Fakultas Teknik" <?php if($fakultas == "Fakultas Teknik") echo "selected"; ?>>Fakultas Teknik</option>
                <option value="Fakultas Ekonomi" <?php if($fakultas == "Fakultas Ekonomi") echo "selected"; ?>>Fakultas Ekonomi</option>
                <option value="Fakultas Hukum" <?php if($fakultas == "Fakultas Hukum") echo "selected"; ?>>Fakultas Hukum</option>
                <option value="Fakultas Keguruan" <?php if($fakultas == "Fakultas Keguruan") echo "selected"; ?>>Fakultas Keguruan</option>
            </select>

            <label for="prodi">Program Studi</label>
            <select id="prodi" name="prodi" required>
                <option value="">-- Pilih Program Studi --</option>
                <option value="Informatika" <?php if($prodi == "Informatika") echo "selected"; ?>>Informatika</option>
                <option value="Sistem Informasi" <?php if($prodi == "Sistem Informasi") echo "selected"; ?>>Sistem Informasi</option>
                <option value="Teknik Industri" <?php if($prodi == "Teknik Industri") echo "selected"; ?>>Teknik Industri</option>
                <option value="Manajemen" <?php if($prodi == "Manajemen") echo "selected"; ?>>Manajemen</option>
            </select>

            <label for="asal_sekolah">Asal Sekolah</label>
            <input 
                type="text" 
                id="asal_sekolah" 
                name="asal_sekolah"
                placeholder="Contoh: SMA Negeri 1 Bandung"
                value="<?php echo htmlspecialchars($asal_sekolah); ?>"
                required
            >

            <label for="semester">Semester</label>
            <select id="semester" name="semester" required>
                <option value="">-- Pilih Semester --</option>
                <?php for($i=1; $i<=8; $i++): ?>
                    <option value="<?php echo $i; ?>" <?php if($semester == $i) echo "selected"; ?>>Semester <?php echo $i; ?></option>
                <?php endfor; ?>
            </select>

            <label>Hobi</label>
            <div class="hobi">
                <?php 
                $daftar_hobi = ["Membaca", "Gaming", "Olahraga", "Musik"];
                foreach($daftar_hobi as $item): 
                    $checked = in_array($item, $hobi) ? "checked" : "";
                ?>
                    <label>
                        <input type="checkbox" name="hobi[]" value="<?php echo $item; ?>" <?php echo $checked; ?>>
                        <?php echo $item; ?>
                    </label>
                <?php endforeach; ?>
            </div>

            <button type="submit" class="button">
                Simpan Data
            </button>

        </form>

        <?php if ($_SERVER["REQUEST_METHOD"] == "POST") { ?>

            <div class="hasil">
                <h2>Data Mahasiswa</h2>
                <p><strong>NIM:</strong> <?php echo htmlspecialchars($nim); ?></p>
                <p><strong>Nama:</strong> <?php echo htmlspecialchars($nama); ?></p>
                <p><strong>Fakultas:</strong> <?php echo htmlspecialchars($fakultas); ?></p>
                <p><strong>Program Studi:</strong> <?php echo htmlspecialchars($prodi); ?></p>
                <p><strong>Asal Sekolah:</strong> <?php echo htmlspecialchars($asal_sekolah); ?></p>
                <p><strong>Semester:</strong> <?php echo htmlspecialchars($semester); ?></p>
                <p>
                    <strong>Hobi:</strong>
                    <?php
                    if (!empty($hobi)) {
                        echo htmlspecialchars(implode(", ", $hobi));
                    } else {
                        echo "Tidak ada hobi yang dipilih";
                    }
                    ?>
                </p>
            </div>

        <?php } ?>

    </div>
</div>

</body>
</html>
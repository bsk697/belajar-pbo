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
            background: linear-gradient(135deg, #667eea, #764ba2);
            min-height: 100vh;
        }

        .container {
            width: 90%;
            max-width: 600px;
            margin: 50px auto;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-top: 15px;
            margin-bottom: 7px;
            font-weight: bold;
            color: #444;
        }

        input[type="text"],
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 15px;
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
            color: #333;
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
                required
            >

            <label for="nama">Nama</label>
            <input 
                type="text" 
                id="nama" 
                name="nama"
                placeholder="Masukkan nama lengkap"
                required
            >

            <label for="fakultas">Fakultas</label>
            <select id="fakultas" name="fakultas" required>
                <option value="">-- Pilih Fakultas --</option>
                <option value="Fakultas Teknik">Fakultas Teknik</option>
                <option value="Fakultas Ekonomi">Fakultas Ekonomi</option>
                <option value="Fakultas Hukum">Fakultas Hukum</option>
                <option value="Fakultas Keguruan">Fakultas Keguruan</option>
            </select>

            <label for="prodi">Program Studi</label>
            <select id="prodi" name="prodi" required>
                <option value="">-- Pilih Program Studi --</option>
                <option value="Informatika">Informatika</option>
                <option value="Sistem Informasi">Sistem Informasi</option>
                <option value="Teknik Industri">Teknik Industri</option>
                <option value="Manajemen">Manajemen</option>
            </select>

            <label for="asal_sekolah">Asal Sekolah</label>
            <input 
                type="text" 
                id="asal_sekolah" 
                name="asal_sekolah"
                placeholder="Contoh: SMA Negeri 1 Bandung"
                required
            >

            <label for="semester">Semester</label>
            <select id="semester" name="semester" required>
                <option value="">-- Pilih Semester --</option>
                <option value="1">Semester 1</option>
                <option value="2">Semester 2</option>
                <option value="3">Semester 3</option>
                <option value="4">Semester 4</option>
                <option value="5">Semester 5</option>
                <option value="6">Semester 6</option>
                <option value="7">Semester 7</option>
                <option value="8">Semester 8</option>
            </select>

            <label>Hobi</label>

            <div class="hobi">
                <label>
                    <input type="checkbox" name="hobi[]" value="Membaca">
                    Membaca
                </label>

                <label>
                    <input type="checkbox" name="hobi[]" value="Gaming">
                    Gaming
                </label>

                <label>
                    <input type="checkbox" name="hobi[]" value="Olahraga">
                    Olahraga
                </label>

                <label>
                    <input type="checkbox" name="hobi[]" value="Musik">
                    Musik
                </label>
            </div>

            <button type="submit" class="button">
                Simpan Data
            </button>

        </form>


        <?php if ($_SERVER["REQUEST_METHOD"] == "POST") { ?>

            <div class="hasil">

                <h2>Data Mahasiswa</h2>

                <p>
                    <strong>NIM:</strong>
                    <?php echo htmlspecialchars($nim); ?>
                </p>

                <p>
                    <strong>Nama:</strong>
                    <?php echo htmlspecialchars($nama); ?>
                </p>

                <p>
                    <strong>Fakultas:</strong>
                    <?php echo htmlspecialchars($fakultas); ?>
                </p>

                <p>
                    <strong>Program Studi:</strong>
                    <?php echo htmlspecialchars($prodi); ?>
                </p>

                <p>
                    <strong>Asal Sekolah:</strong>
                    <?php echo htmlspecialchars($asal_sekolah); ?>
                </p>

                <p>
                    <strong>Semester:</strong>
                    <?php echo htmlspecialchars($semester); ?>
                </p>

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
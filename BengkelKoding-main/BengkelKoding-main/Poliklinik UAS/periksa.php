<?php

// Memeriksa apakah pengguna sudah login, jika tidak, arahkan kembali ke halaman login
if (!isset($_SESSION["username"])) {
    header("location: index.php?page=loginUser");
    exit;
}
?>

<!-- FORM -->
<div class="form-group mx-sm-3 mb-2">
    <form action="" onsubmit="return(validate());" method="post">
        <!-- AMBIL DATA UNTUK UBAH -->
        <?php
        include('koneksi.php');
        $id_pasien = '';
        $id_dokter = '';
        $tgl_periksa = '';
        $catatan = '';
        $biaya_periksa = '';
        $obat = [];
        if (isset($_GET['id'])) {
            $ambil = mysqli_query(
                $mysqli,
                "SELECT * FROM periksa 
                WHERE id='" . $_GET['id'] . "'"
            );
            while ($row = mysqli_fetch_array($ambil)) {
                $id_pasien = $row['id_pasien'];
                $id_dokter = $row['id_dokter'];
                $tgl_periksa = $row['tgl_periksa'];
                $catatan = $row['catatan'];
                $biaya_periksa = $row['biaya_periksa'];
                $detail_periksa = mysqli_query($mysqli, "SELECT * FROM detail_periksa WHERE id_periksa='" . $_GET['id'] . "'");
                while ($row = mysqli_fetch_array($detail_periksa)) {
                    $obat[] = $row['id_obat'];
                }
            }
        ?>
            <input type=hidden name="id" value="<?php echo $_GET['id'] ?>">
        <?php
        }
        ?>
        <!-- SELECT PASIEN -->
        <label class="fw-bold">Pasien</label>
        <select class="form-control my-2" name="id_pasien">
            <?php
            include('koneksi.php');
            $selected = '';
            $periksa = mysqli_query($mysqli, "SELECT * FROM pasien");
            while ($data = mysqli_fetch_array($periksa)) {
                if ($data['id'] == $id_pasien) {
                    $selected = 'selected="selected"';
                } else {
                    $selected = '';
                }
            ?>
                <option value="<?php echo $data['id'] ?>" <?php echo $selected ?>> <?php echo $data['nama'] ?></option>
            <?php
            }
            ?>
        </select>

        <!-- SELECT DOKTER -->
        <label class="fw-bold">Dokter</label>
        <select class="form-control my-2" name="id_dokter">
            <?php
            include('koneksi.php');
            $selected = '';
            $dokter = mysqli_query($mysqli, "SELECT * FROM dokter");
            while ($data = mysqli_fetch_array($dokter)) {
                if ($data['id'] == $id_dokter) {
                    $selected = 'selected="selected"';
                } else {
                    $selected = '';
                }
            ?>
                <option value="<?php echo $data['id'] ?>" <?php echo $selected ?>><?php echo $data['nama'] ?></option>
            <?php
            }
            ?>
        </select>

        <!-- COLOM INSERT DATETIME DAN TEXT -->
        <label class="fw-bold">Tanggal Periksa</label>
        <input type="datetime-local" name="tgl_periksa" value="<?php echo $tgl_periksa ?>" class="form-control my-2" required>

        <label class="fw-bold">Catatan</label>
        <input type="text" class="form-control my-2" name="catatan" value="<?php echo $catatan ?>">

        <label class="fw-bold">Obat</label>
        <select class="form-control my-2 js-example-basic-multiple" name="obat[]" multiple="multiple">
            <?php
            $selected = '';
            $all_obat = mysqli_query($mysqli, "SELECT * FROM obat");
            while ($data = mysqli_fetch_array($all_obat)) {
                for ($i = 0; $i < count($obat); $i++) {
                    $obat_terpilih = mysqli_fetch_array(mysqli_query($mysqli, "SELECT * FROM obat WHERE id='" . $obat[$i] . "'"));
                    if ($obat_terpilih['id'] == $data['id']) {
                        $selected = 'selected="selected"';
                    } else {
                        $selected = '';
                    }

                    if ($_GET['id'] != null) {
                        if ($obat_terpilih == null) {
            ?>
                            <option value="<?php echo $data['id'] ?>"><?= $data['nama_obat'] ?></option>
                        <?php
                        }

                        if ($data['id'] == $obat_terpilih['id']) {
                        ?>
                            <option value="<?php echo $data['id'] ?>" <?php echo $selected ?>><?= $data['nama_obat'] ?></option>
                        <?php
                        } else {
                        ?>
                            <option value="<?php echo $data['id'] ?>" <?php echo $selected ?>><?= $data['nama_obat'] ?></option>
                    <?php
                        }
                    }
                }

                if ($_GET['id'] == null) {
                    ?>
                    <option value="<?php echo $data['id'] ?>" <?php echo $selected ?>><?= $data['nama_obat'] ?></option>
            <?php
                }
            }
            ?>
        </select>

        <button class="btn btn-primary rounded-pill px-3" type="submit" name="simpan">Submit</button>
    </form>

    <!-- TABLE -->
    <div class="table-responsive my-4">
        <table class="table" id="myTable">
            <thead>
                <tr>
                    <th scope="col">No</th>
                    <th scope="col">Nama</th>
                    <th scope="col">Dokter</th>
                    <th scope="col">Tanggal Periksa</th>
                    <th scope="col">Catatan</th>
                    <th scope="col">Obat</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>
            <?php
            include('koneksi.php');
            date_default_timezone_set("Asia/Jakarta");
            $result = mysqli_query(
                $mysqli,
                "SELECT 
                        pr.*,
                        d.nama as 'nama_dokter', 
                        p.nama as 'nama_pasien',
                        GROUP_CONCAT(o.nama_obat SEPARATOR ', ') AS 'obat'
                    FROM periksa pr
                    LEFT JOIN dokter d ON (pr.id_dokter=d.id) 
                    LEFT JOIN pasien p ON (pr.id_pasien=p.id)
                    LEFT JOIN detail_periksa dp ON (pr.id=dp.id_periksa)
                    LEFT JOIN obat o ON (dp.id_obat=o.id)
                    GROUP BY pr.id
                    ORDER BY pr.tgl_periksa DESC"
            );
            $no = 1;
            while ($data = mysqli_fetch_array($result)) {
            ?>
                <tr>
                    <td><?php echo $no++ ?></td>
                    <td><?php echo $data['nama_pasien'] ?></td>
                    <td><?php echo $data['nama_dokter'] ?></td>
                    <td><?php echo date('d-M-Y H:i:s', strtotime($data['tgl_periksa'])) ?></td>
                    <td><?php echo $data['catatan'] ?></td>
                    <td style="width: 25%"><?php echo $data['obat'] == null ? 'Tidak ada obat' : $data['obat']; ?></td>
                    <td>
                        <a class="btn btn-success rounded-pill px-3" href="index.php?page=periksa&id=<?php echo $data['id'] ?>">
                            Ubah</a>
                        <a class="btn btn-danger rounded-pill px-3" href="index.php?page=periksa&id=<?php echo $data['id'] ?>&aksi=hapus">Hapus</a>
                        <a class="btn btn-warning rounded-pill px-3" href="invoice.php?id=<?php echo $data['id'] ?>">Invoice</a>
                    </td>
                </tr>
            <?php
            }
            ?>
        </table>
    </div>
</div>
<Script>
    $(document).ready(function() {
        $('.js-example-basic-multiple').select2();
    });
</Script>

<!-- FUNGSI CRUD -->
<?php
include('koneksi.php');
if (isset($_POST['simpan'])) {
    if (isset($_POST['id'])) {
        $ubah = mysqli_query($mysqli, "UPDATE periksa SET 
                                            id_pasien = '" . $_POST['id_pasien'] . "',
                                            id_dokter = '" . $_POST['id_dokter'] . "',
                                            tgl_periksa = '" . $_POST['tgl_periksa'] . "',
                                            catatan = '" . $_POST['catatan'] . "'
                                            WHERE id = '" . $_POST['id'] . "'");
        $hapus_detail = mysqli_query($mysqli, "DELETE FROM detail_periksa WHERE id_periksa = '" . $_POST['id'] . "'");
        foreach ($_POST['obat'] as $obat) {
            $tambah_detail = mysqli_query($mysqli, "INSERT INTO detail_periksa (id_periksa, id_obat) 
                                            VALUES (
                                                '" . $_POST['id'] . "',
                                                '" . $obat . "'
                                            )");
        }
    } else {
        $tambah = mysqli_query($mysqli, "INSERT INTO periksa (id_pasien, id_dokter, tgl_periksa, catatan) 
                                            VALUES (
                                                '" . $_POST['id_pasien'] . "',
                                                '" . $_POST['id_dokter'] . "',
                                                '" . $_POST['tgl_periksa'] . "',
                                                '" . $_POST['catatan'] . "'
                                            )");
        $periksa_id = mysqli_insert_id($mysqli);
        foreach ($_POST['obat'] as $obat) {
            $tambah_detail = mysqli_query($mysqli, "INSERT INTO detail_periksa (id_periksa, id_obat) 
                                            VALUES (
                                                '" . $periksa_id . "',
                                                '" . $obat . "'
                                            )");
        }
    }


    echo "<script> 
        document.location='index.php?page=periksa';
        </script>";
}

if (isset($_GET['aksi'])) {
    if ($_GET['aksi'] == 'hapus') {
        $hapus = mysqli_query($mysqli, "DELETE FROM detail_periksa WHERE id_periksa = '" . $_GET['id'] . "'") && mysqli_query($mysqli, "DELETE FROM periksa WHERE id = '" . $_GET['id'] . "'");
    }

    echo "<script> 
            alert('Data Berhasil Dihapus');
            document.location='index.php?page=periksa';
            </script>";
}
?>
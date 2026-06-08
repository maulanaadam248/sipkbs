<?php
session_start();
require __DIR__ . '/../config/database.php';
global $conn;

if(!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'operator' && $_SESSION['role'] != 'admin')) {
    header("Location: ../index.php"); exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file_excel'])) {
    
    $bulan_import = $_POST['bulan']; 
    $tahun_import = $_POST['tahun'];
    $default_balai_id = (int)$_POST['target_balai_id']; 
    
    $redirect_url = ($_SESSION['role'] == 'admin') ? "tambah_laporan.php?balai_id=$default_balai_id" : "tambah_laporan.php";
    $file_mimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');

    if(isset($_FILES['file_excel']['name']) && in_array($_FILES['file_excel']['type'], $file_mimes)) {
        $arr_file = explode('.', $_FILES['file_excel']['name']);
        $extension = end($arr_file);

        if('csv' == strtolower($extension)) {
            $reader = fopen($_FILES['file_excel']['tmp_name'], 'r');
            $berhasil = 0; $gagal = 0; $mulai_baca_data = false;
            
            $memori_balai_id = $default_balai_id;
            $memori_komoditas = ''; $memori_kelompok = ''; $memori_kelas_benih = ''; 
            $memori_satuan_mentah = ''; $memori_harga_mentah = ''; 

            $bulan_terdeteksi = ''; $tahun_terdeteksi = '';

            // --- PERBAIKAN: NILAI DEFAULT VARIABEL BULAN & TAHUN ---
            $db_bulan = ($bulan_import === 'Auto' || empty($bulan_import)) ? date('F') : $bulan_import;
            $db_tahun = empty($tahun_import) ? date('Y') : $tahun_import;
            // -------------------------------------------------------

            $balai_map = [];
            $query_b = mysqli_query($conn, "SELECT id_balai, nama_balai FROM balai");
            while ($b = mysqli_fetch_assoc($query_b)) {
                $balai_map[strtolower(trim($b['nama_balai']))] = $b['id_balai'];
            }

            $data_siap_insert = [];

            while (($row = fgetcsv($reader, 1000, ",")) !== FALSE) {
                if(count($row) == 1 && strpos($row[0], ';') !== false) { $row = explode(';', $row[0]); }
                
                if (!$mulai_baca_data) {
                    $row_text = implode(" ", $row); 
                    
                    if (preg_match('/\b(Januari|Februari|Maret|April|Mei|Juni|Juli|Agustus|September|Oktober|November|Desember)\b/i', $row_text, $matches)) {
                        $bulan_terdeteksi = ucfirst(strtolower($matches[1]));
                        if (preg_match('/\b(20[2-9][0-9])\b/', $row_text, $t_matches)) {
                            $tahun_terdeteksi = $t_matches[1]; 
                        }
                    }

                    $cek_judul = strtolower(trim($row[2] ?? ''));
                    if ($cek_judul == 'komoditas' || in_array('Komoditas', $row)) { $mulai_baca_data = true; }
                    continue; 
                }

                if (empty(array_filter($row)) || strtolower(trim($row[1] ?? '')) == 'dst') continue;

                $nama_balai_csv = strtolower(trim($row[0] ?? ''));
                if (!empty($nama_balai_csv)) {
                    $ketemu_balai = false;
                    foreach ($balai_map as $nama_db => $id_db) {
                        if (strpos($nama_balai_csv, $nama_db) !== false || strpos($nama_db, $nama_balai_csv) !== false) {
                            $memori_balai_id = $id_db; $ketemu_balai = true; break;
                        }
                    }
                    if (!$ketemu_balai) { $memori_balai_id = $default_balai_id; }
                }
                $current_balai_id = $memori_balai_id;

                $current_komoditas = trim($row[2] ?? '');
                if (!empty($current_komoditas)) $memori_komoditas = $current_komoditas; else $current_komoditas = $memori_komoditas; 

                $current_kelompok = trim($row[3] ?? '');
                if (!empty($current_kelompok)) $memori_kelompok = $current_kelompok; else $current_kelompok = $memori_kelompok; 

                $varietas = trim($row[4] ?? '');
                $current_kelas = trim($row[5] ?? '');
                if (!empty($current_kelas)) $memori_kelas_benih = $current_kelas; else $current_kelas = $memori_kelas_benih; 
                
                if (empty($current_komoditas) && empty($varietas) && empty($current_kelas)) continue;

                $komoditas = mysqli_real_escape_string($conn, $current_komoditas);
                $kelompok = mysqli_real_escape_string($conn, $current_kelompok);
                $varietas_db = mysqli_real_escape_string($conn, $varietas);
                $kelas = mysqli_real_escape_string($conn, $current_kelas);
                
                $raw_jumlah = trim($row[6] ?? '0');
                $jumlah = (int)preg_replace('/[^0-9]/', '', $raw_jumlah);
                $teks_stok = trim(preg_replace('/[0-9\s\.,]/', '', $raw_jumlah)); 
                
                $satuan_mentah = trim($row[7] ?? '');
                if (!empty($satuan_mentah)) $memori_satuan_mentah = $satuan_mentah; else $satuan_mentah = $memori_satuan_mentah;
                $satuan = mysqli_real_escape_string($conn, $satuan_mentah);
                
                $harga_mentah = trim($row[8] ?? '');
                if (!empty($harga_mentah)) $memori_harga_mentah = $harga_mentah; else $harga_mentah = $memori_harga_mentah;
                $harga = (int)preg_replace('/[^0-9]/', '', $harga_mentah); 
                $teks_harga = trim(preg_replace('/[0-9\s\.,]/', '', str_ireplace('rp', '', $harga_mentah))); 
                
                // === EKSTRAKSI KOLOM BARU (Distribusi & Lokasi) ===
                $raw_vol_distribusi = trim($row[9] ?? '0');
                $volume_penyaluran = (int)preg_replace('/[^0-9]/', '', $raw_vol_distribusi); // Tarik angkanya saja
                
                $lokasi_mentah = trim($row[10] ?? '');
                $lokasi_distribusi = mysqli_real_escape_string($conn, $lokasi_mentah);
                // ==================================================

                // === BACA STATUS / KETERANGAN SECARA PASTI ===
                $status_mentah = trim($row[11] ?? '');
                if (empty($status_mentah)) { $status_mentah = '-'; }
                
                $final_status = ($status_mentah === '-') ? '-' : ucwords(strtolower($status_mentah));
                $status = mysqli_real_escape_string($conn, $final_status);
                // =============================================

                $meta_stok = !empty($teks_stok) ? $teks_stok : '-';
                $meta_harga = !empty($teks_harga) ? $teks_harga : '-';
                $deskripsi_db = mysqli_real_escape_string($conn, "MetaUnit=[$meta_stok|$meta_harga]");
                
                if ($bulan_import === 'Auto' || empty($bulan_import)) {
                    $final_bulan = !empty($bulan_terdeteksi) ? $bulan_terdeteksi : date('F');
                    $final_tahun = !empty($tahun_terdeteksi) ? $tahun_terdeteksi : $tahun_import;
                } else {
                    $final_bulan = $bulan_import;
                    $final_tahun = $tahun_import;
                }
                
                $db_bulan = mysqli_real_escape_string($conn, $final_bulan);
                $db_tahun = mysqli_real_escape_string($conn, $final_tahun);

                // Tambahkan data baru ke array insert
                $data_siap_insert[] = [
                    'balai_id' => $current_balai_id, 'komoditas' => $komoditas, 'kelompok' => $kelompok, 
                    'varietas' => $varietas_db, 'kelas' => $kelas, 'jumlah' => $jumlah, 'satuan' => $satuan, 
                    'harga' => $harga, 'status' => $status, 'bulan' => $db_bulan, 'tahun' => $db_tahun, 
                    'deskripsi' => $deskripsi_db, 'volume_penyaluran' => $volume_penyaluran, 'lokasi_distribusi' => $lokasi_distribusi
                ];
            }
            fclose($reader);

            $data_siap_insert = array_reverse($data_siap_insert);

            // === UPDATE QUERY INSERT AGAR MENANGKAP KOLOM BARU ===
            foreach ($data_siap_insert as $d) {
                $query = "INSERT INTO laporan 
                          (balai_id, komoditas, kelompok_komoditas, varietas, kelas_benih, jumlah_benih, satuan, harga_satuan, status_ketersediaan, bulan, tahun, deskripsi, volume_penyaluran, lokasi_distribusi) 
                          VALUES 
                          ({$d['balai_id']}, '{$d['komoditas']}', '{$d['kelompok']}', '{$d['varietas']}', '{$d['kelas']}', {$d['jumlah']}, '{$d['satuan']}', {$d['harga']}, '{$d['status']}', '{$d['bulan']}', '{$d['tahun']}', '{$d['deskripsi']}', {$d['volume_penyaluran']}, '{$d['lokasi_distribusi']}')";
                if(mysqli_query($conn, $query)) $berhasil++; else $gagal++;
            }
            
            $sumber_bulan = ($bulan_import === 'Auto') ? "Auto-Deteksi Excel" : "Pilihan Manual";
            $_SESSION['success'] = "Import Sukses! Data disimpan sebagai bulan <strong>$db_bulan $db_tahun</strong> ($sumber_bulan). Total: $berhasil data.";
            
            if($_SESSION['role'] == 'admin') header("Location: ../admin/semua_laporan.php");
            else header("Location: riwayat_laporan.php");
            exit();

        } else {
            $_SESSION['error'] = "Format file tidak valid."; header("Location: $redirect_url"); exit();
        }
    } else {
        $_SESSION['error'] = "Gagal mengunggah file."; header("Location: $redirect_url"); exit();
    }
}
?>
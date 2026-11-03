<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

$email = 'autotest_script@example.com';
$existing = User::where('email', $email)->first();
if ($existing) {
    $user = $existing;
} else {
    $user = User::create([
        'name' => 'autotest',
        'email' => $email,
        'password' => bcrypt('password'),
        'role' => 'user',
        'avatar' => '',
        'isActive' => true,
    ]);
}

$token = JWTAuth::fromUser($user);
echo $token . PHP_EOL;

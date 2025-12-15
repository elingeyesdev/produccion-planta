<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$operator = App\Models\Operator::where('email', 'admin@admin.com')->first();

if ($operator) {
    echo "Usuario encontrado: " . $operator->username . "\n";
    echo "Email: " . $operator->email . "\n";
    echo "Hash guardado: " . $operator->password_hash . "\n";
    
    $passwordCheck = Hash::check('admin123', $operator->password_hash);
    echo "Password check: " . ($passwordCheck ? 'OK ✓' : 'FAIL ✗') . "\n";
} else {
    echo "Usuario no encontrado\n";
}

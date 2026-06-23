<?php
require __DIR__.'/vendor/autoload.php';
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

$options = new QROptions([
    'outputType' => 'png',
    'outputBase64' => true,
    'eccLevel' => QRCode::ECC_L,
    'scale' => 5,
]);

$qrcode = new QRCode($options);
echo $qrcode->render('F;NIM12345678;1234;132000;20250826120000;abcdef');

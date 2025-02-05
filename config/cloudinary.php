<?php
use Cloudinary\Cloudinary;

$config = [
    'cloud_name' => env('CLOUDINARY_CLOUD_NAME', 'dazmnh6fr'),
    'api_key'    => env('CLOUDINARY_API_KEY', '843717747511777'),
    'api_secret' => env('CLOUDINARY_API_SECRET', 'ypTArLdjrwO1f4qm-g2b3ebF2Mo'),
    'secure'     => true,
];

$cloudinary = new Cloudinary($config);

return $config;
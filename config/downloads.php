<?php

return [
    'archive_path' => env(
        'DOWNLOAD_ARCHIVE_PATH',
        storage_path('app/downloads/AidatPro-v1.0.0-full.zip'),
    ),
    'filename' => env('DOWNLOAD_ARCHIVE_NAME', 'AidatPro-v1.0.0-full.zip'),
    'password_hash' => env('DOWNLOAD_PASSWORD_HASH'),
];

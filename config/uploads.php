<?php

return [
    'instructor_profile' => [
        // Allowed image extensions for instructor profile uploads
        'allowed_types' => ['jpg', 'jpeg', 'png', 'webp'],
        // Maximum file size in KB (10 MB)
        'max_kb' => 10 * 1024,
        // MIME types for Validator ('mimes' or 'mimetypes' いずれかで利用)
        'allowed_mimes' => ['image/jpeg', 'image/png', 'image/webp'],
        // 画像寸法上限（圧縮爆弾対策）
        'max_width' => 6000,
        'max_height' => 6000,
        // 保存先の一元化
        'disk' => 'public',
        'directory' => 'instructors',
        // 画像処理ポリシー（任意）
        'strip_exif' => true,
    ],
];

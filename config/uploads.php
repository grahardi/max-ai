<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Ekstensi yang diizinkan untuk Member Area (upload/download file)
    |--------------------------------------------------------------------------
    | Sengaja TIDAK menyertakan ekstensi eksekutabel/script seperti:
    | php, phtml, phar, exe, sh, bat, js, html, htm, svg, py, pl, cgi, dll.
    | SVG & HTML dikeluarkan karena bisa membawa script (XSS) saat dibuka.
    */
    'safe_extensions' => [
        // gambar
        'jpg', 'jpeg', 'png', 'webp', 'gif',
        // dokumen
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'rtf',
        // arsip
        'zip', 'rar', '7z',
        // audio/video umum
        'mp3', 'wav', 'mp4', 'mov',
    ],

    'max_size_kb' => 51200, // 50 MB per file

    'quota_mb_per_user' => 1024, // total 1 GB per member

];

<?php

return [
    'show_warnings' => false,
    'convert_entities' => false,
    'options' => [
        'font_dir' => storage_path('app/pdf/fonts'),
        'font_cache' => storage_path('app/pdf/fonts'),
        'temp_dir' => storage_path('app/pdf/temp'),
        'chroot' => [public_path(), storage_path('app'), resource_path(), base_path('vendor/dompdf/dompdf/lib/fonts')],
        'default_font' => 'DejaVu Sans',
        'default_paper_size' => 'a4',
        'default_paper_orientation' => 'portrait',
        'default_media_type' => 'print',
        'dpi' => 96,
        'enable_font_subsetting' => true,
        'pdf_backend' => 'CPDF',
        'enable_php' => false,
        'enable_javascript' => false,
        // Document assets are local files or embedded data URIs; no external service is required.
        'enable_remote' => false,
        'allowed_remote_hosts' => [],
    ],
];

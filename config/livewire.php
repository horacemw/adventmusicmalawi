<?php

return [
    'class_namespace' => 'App\\Livewire',

    'view_path' => resource_path('views/livewire'),

    'layout' => 'components.layouts.app',

    'lazy_placeholder' => null,

    'temporary_file_upload' => [
        // Store Livewire temp uploads on the private local disk (storage/app/private/livewire-tmp)
        // rather than the public disk. This isolates them from final asset storage so the temp file
        // isn't affected by public-disk mounts or storage:link resets, and keeps large staging
        // files off any public route.
        'disk' => 'local',
        // 210 MB — matches nginx client_max_body_size (220 MB) minus safety margin. Album ZIP
        // submissions are the biggest expected upload.
        'rules' => ['required', 'file', 'max:215040'],
        'directory' => null,
        'middleware' => null,
        'preview_mimes' => [
            'png', 'gif', 'bmp', 'svg', 'wav', 'mp4',
            'mov', 'avi', 'wmv', 'mp3', 'm4a',
            'jpg', 'jpeg', 'mpga', 'webp', 'wma',
        ],
        'max_upload_time' => 10,
        'cleanup' => true,
    ],

    'render_on_redirect' => false,

    'legacy_model_binding' => false,

    'inject_assets' => true,

    'navigate' => [
        'show_progress_bar' => true,
        'progress_bar_color' => '#16a34a',
    ],

    'inject_morph_markers' => true,

    'pagination_theme' => 'tailwind',
];

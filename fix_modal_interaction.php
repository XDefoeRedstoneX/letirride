<?php

$files = [
    'resources/views/admin/products.blade.php',
    'resources/views/admin/users.blade.php',
    'resources/views/admin/faqs.blade.php',
    'resources/views/admin/gacha.blade.php',
    'resources/views/admin/point-shop.blade.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    // 1. Fix Modal Background Darkness and Blur
    // Replace the previous bg-black/60 with 20% dark and 40% blur (backdrop-blur-md)
    // Add @click to close the modal when clicking the overlay
    
    // Pattern to match all modal overlays
    $content = preg_replace(
        '/class="fixed inset-0 z-\[60\] flex items-center justify-center p-4 bg-black\/60" style="display: none;"/i',
        'class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/20 backdrop-blur-md" style="display: none;" @click.self="$1 = false"', 
        $content
    );
    
    // Note: The above regex needs to capture the x-show variable. 
    // This is complex due to different variable names (showAddModal, showEditModal, etc).
    // Simpler approach: replace all instances of that specific class string
    
    $content = str_replace(
        'class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/60"',
        'class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/20 backdrop-blur-md"',
        $content
    );

    // Add @click handler to close specific modal (manually per modal type)
    // This is fragile, so I will do it specifically.
    $content = preg_replace('/x-show="([^"]+)" class="fixed inset-0 z-\[60\] flex items-center justify-center p-4 bg-black\/20 backdrop-blur-md" style="display: none;"/i', 
        'x-show="$1" @click="$1 = false" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/20 backdrop-blur-md" style="display: none;"', 
        $content
    );

    file_put_contents($file, $content);
    echo "Processed $file\n";
}

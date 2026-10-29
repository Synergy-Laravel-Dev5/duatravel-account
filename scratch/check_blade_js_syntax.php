<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function checkFile($path) {
    echo "Checking {$path}...\n";
    $content = file_get_contents($path);
    // Find script tags
    preg_match_all('/<script\b[^>]*>(.*?)<\/script>/is', $content, $matches);
    echo "Found " . count($matches[1]) . " script blocks.\n";
    
    // Replace Blade tags {{ ... }} and @json(...) with safe JS
    foreach ($matches[1] as $idx => $script) {
        $clean = preg_replace('/\{\{.*?\}\}/s', '"blade_var"', $script);
        $clean = preg_replace('/@json\(.*?\)/s', '[]', $clean);
        $clean = preg_replace('/@if\(.*?\)/s', '', $clean);
        $clean = preg_replace('/@else/s', '', $clean);
        $clean = preg_replace('/@endif/s', '', $clean);
        $clean = preg_replace('/@foreach\(.*?\)/s', '', $clean);
        $clean = preg_replace('/@endforeach/s', '', $clean);
        
        $tmpFile = __DIR__ . "/tmp_script_{$idx}.js";
        file_put_contents($tmpFile, $clean);
        $output = [];
        $ret = 0;
        exec("node --check \"{$tmpFile}\" 2>&1", $output, $ret);
        if ($ret !== 0) {
            echo "Script block {$idx} has JS syntax ERROR:\n" . implode("\n", $output) . "\n";
        } else {
            echo "Script block {$idx} JS syntax OK.\n";
        }
        @unlink($tmpFile);
    }
}

checkFile(__DIR__ . '/../resources/views/booking/create.blade.php');
checkFile(__DIR__ . '/../resources/views/booking/edit.blade.php');

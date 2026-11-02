<?php

$jsonFiles = glob(__DIR__ . '/storage/*.json');

function cleanUrl($url, $key = '') {
    if (!is_string($url) || !str_starts_with($url, 'http')) {
        return $url;
    }

    $path = parse_url($url, PHP_URL_PATH);
    $filename = basename($path);
    if (!$filename) return $url;

    if ($key === 'banner') {
        return "stores/banners/{$filename}";
    } elseif ($key === 'logo' || str_contains($url, '/storage/markets/')) {
        return "stores/logos/{$filename}";
    } elseif (str_contains($url, '/storage/sections/')) {
        return "categories/{$filename}";
    } else {
        return "products/{$filename}";
    }
}

function cleanData(&$data, $parentKey = '') {
    if (is_array($data)) {
        foreach ($data as $k => &$v) {
            if (is_string($v) && str_starts_with($v, 'http')) {
                $v = cleanUrl($v, is_string($k) ? $k : $parentKey);
            } elseif (is_array($v)) {
                cleanData($v, is_string($k) ? $k : $parentKey);
            }
        }
    }
}

echo "=== Cleaning All JSON Files in storage/ ===\n";
foreach ($jsonFiles as $file) {
    $basename = basename($file);
    $content = json_decode(file_get_contents($file), true);
    if (!$content) {
        echo "Skipping $basename (not valid JSON)\n";
        continue;
    }

    cleanData($content);
    file_put_contents($file, json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    echo "Cleaned $basename\n";
}

echo "\nVerification of JSON files:\n";
$remainingHttp = 0;
foreach ($jsonFiles as $file) {
    $str = file_get_contents($file);
    preg_match_all('#https?://[^\s"]+#', $str, $matches);
    $count = count($matches[0]);
    if ($count > 0) {
        echo "WARNING: " . basename($file) . " still has $count HTTP URLs.\n";
        $remainingHttp += $count;
    }
}

if ($remainingHttp === 0) {
    echo "SUCCESS: 0 HTTP URLs remaining across ALL JSON files!\n";
} else {
    echo "Total remaining HTTP URLs: $remainingHttp\n";
}

<?php
/**
 * SSL Certificate Test & Fix
 */

echo "╔══════════════════════════════════════════╗\n";
echo "║  SSL Certificate Troubleshooting Tool   ║\n";
echo "╚══════════════════════════════════════════╝\n\n";

// ۱. نمایش موقعیت CA Bundle
echo "1️⃣  CA Bundle Locations:\n";
echo "──────────────────────────────────────────\n";

$commonPaths = [
    'Windows' => [
        'C:\\php\\extras\\ssl\\cacert.pem',
        'C:\\xampp\\php\\extras\\ssl\\cacert.pem',
        'C:\\wamp\\bin\\php\\php7.4.0\\extras\\ssl\\cacert.pem',
        'C:\\laragon\\bin\\php\\php7.4.0\\extras\\ssl\\cacert.pem',
    ],
    'Linux' => [
        '/etc/ssl/certs/ca-certificates.crt',
        '/etc/pki/tls/certs/ca-bundle.crt',
        '/usr/share/ca-certificates/ca-bundle.crt',
    ],
    'macOS' => [
        '/usr/local/etc/openssl/cert.pem',
        '/opt/local/etc/openssl/cert.pem',
        '/usr/local/etc/openssl@1.1/cert.pem',
    ],
];

$foundPath = null;

foreach ($commonPaths as $os => $paths) {
    echo "\n$os:\n";
    foreach ($paths as $path) {
        $exists = file_exists($path);
        $status = $exists ? '✓' : '✗';
        echo "  $status $path\n";
        if ($exists && !$foundPath) {
            $foundPath = $path;
        }
    }
}

echo "\n\n2️⃣  OpenSSL Information:\n";
echo "──────────────────────────────────────────\n";

$locations = openssl_get_cert_locations();
foreach ($locations as $key => $value) {
    echo "$key: " . ($value ?: 'N/A') . "\n";
}


echo "\n\n3️⃣  cURL Information:\n";
echo "──────────────────────────────────────────\n";

$version = curl_version();
echo "Version: " . $version['version'] . "\n";
echo "SSL Version: " . $version['ssl_version'] . "\n";
echo "Libz Version: " . $version['libz_version'] . "\n";

echo "\n\n4️⃣  Auto-Detected CA Bundle:\n";
echo "──────────────────────────────────────────\n";

if ($foundPath) {
    echo "✓ Found: $foundPath\n";
    echo "✓ File size: " . filesize($foundPath) . " bytes\n";
    echo "✓ Last modified: " . date('Y-m-d H:i:s', filemtime($foundPath)) . "\n";
} else {
    echo "✗ No CA bundle found in common locations\n";
    echo "ℹ️  Download from: https://curl.se/ca/cacert.pem\n";
}

echo "\n\n5️⃣  Connection Test:\n";
echo "──────────────────────────────────────────\n";

$testUrls = [
    'https://news.ycombinator.com/rss',
    'https://www.php.net/feed.atom',
];

foreach ($testUrls as $url) {
    echo "\nTesting: $url\n";
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_NOBODY => true,
    ]);
    
    if ($foundPath) {
        curl_setopt($ch, CURLOPT_CAINFO, $foundPath);
    }
    
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "  ✗ Error: $error\n";
    } else {
        echo "  ✓ HTTP $httpCode\n";
    }
}

echo "\n\n6️⃣  Recommended Solutions:\n";
echo "──────────────────────────────────────────\n";

if ($foundPath) {
    echo "✓ CA Bundle found. Use this in code:\n\n";
    echo "  \$fetcher = new FeedFetcher();\n";
    echo "  \$fetcher->setCaBundlePath('$foundPath');\n";
} else {
    echo "✗ CA Bundle not found. Download and use:\n\n";
    echo "  1. Download: curl https://curl.se/ca/cacert.pem -o cacert.pem\n";
    echo "  2. Place in project or PHP directory\n";
    echo "  3. Use in code:\n";
    echo "     \$fetcher = new FeedFetcher();\n";
    echo "     \$fetcher->setCaBundlePath('/path/to/cacert.pem');\n\n";
    echo "  OR for development only:\n";
    echo "     \$fetcher->setVerifySSL(false); // ⚠️ NOT for production!\n";
}

echo "\n7️⃣  php.ini Configuration (Optional):\n";
echo "──────────────────────────────────────────\n";

if ($foundPath) {
    echo "Add to php.ini:\n";
    echo "  curl.cainfo = \"$foundPath\"\n";
    echo "  openssl.cafile = \"$foundPath\"\n";
}

echo "\n╔══════════════════════════════════════════╗\n";
echo "║  Troubleshooting Complete!             ║\n";
echo "╚══════════════════════════════════════════╝\n";

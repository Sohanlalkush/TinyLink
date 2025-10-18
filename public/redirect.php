<?php
include __DIR__ . '/../db.php';

$shortCode = $_GET['code'] ?? '';

if ($shortCode) {
    $stmt = $db->prepare("SELECT long_url FROM links WHERE short_code = ?");
    $stmt->execute([$shortCode]);
    $link = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($link) {
        $stmt = $db->prepare("UPDATE links SET click_count = click_count + 1 WHERE short_code = ?");
        $stmt->execute([$shortCode]);
        
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'];
        header("Location: " . $baseUrl . "/widgets/page1.html?ref=" . urlencode($shortCode));
        exit;
    }
}

http_response_code(404);
echo "Link not found.";

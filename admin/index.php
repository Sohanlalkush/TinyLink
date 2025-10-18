<?php
session_start();
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header('Location: ../user/login.php');
    exit;
}

include __DIR__ . '/../db.php';

if (isset($_GET['delete_link'])) {
    $linkId = (int)$_GET['delete_link'];
    $stmt = $db->prepare("DELETE FROM links WHERE id = ?");
    $stmt->execute([$linkId]);
    header('Location: index.php');
    exit;
}

if (isset($_GET['delete_user'])) {
    $userId = (int)$_GET['delete_user'];
    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_link'])) {
    $longUrl = trim($_POST['long_url']);
    $shortCode = trim($_POST['short_code']);
    
    if ($longUrl && $shortCode) {
        $stmt = $db->prepare("INSERT INTO links (long_url, short_code) VALUES (?, ?)");
        try {
            $stmt->execute([$longUrl, $shortCode]);
            $message = 'Link created successfully!';
        } catch (PDOException $e) {
            $message = 'Error: Short code already exists.';
        }
    }
}

$search = $_GET['search'] ?? '';
$linkQuery = "SELECT l.*, u.email FROM links l LEFT JOIN users u ON l.user_id = u.id";
if ($search) {
    $linkQuery .= " WHERE l.long_url LIKE ? OR l.short_code LIKE ?";
    $stmt = $db->prepare($linkQuery . " ORDER BY l.created_at DESC");
    $searchTerm = "%$search%";
    $stmt->execute([$searchTerm, $searchTerm]);
} else {
    $stmt = $db->query($linkQuery . " ORDER BY l.created_at DESC");
}
$links = $stmt->fetchAll(PDO::FETCH_ASSOC);

$users = $db->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .header { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; padding: 20px; text-align: center; }
        .container { max-width: 1400px; margin: 40px auto; padding: 0 20px; }
        .section { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 30px; }
        h2 { color: #333; margin-bottom: 20px; }
        table { width: 100%; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: bold; }
        .actions a { color: #dc3545; text-decoration: none; margin-right: 10px; }
        .actions a:hover { text-decoration: underline; }
        .btn { display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; border: none; cursor: pointer; }
        .btn:hover { background: #5568d3; }
        input[type="text"] { padding: 10px; border: 2px solid #ddd; border-radius: 5px; margin-right: 10px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: #555; }
        .form-group input { width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 5px; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px; }
        .stat-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; text-align: center; }
        .stat-card h3 { font-size: 36px; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔐 Admin Panel</h1>
        <p>Manage all links and users</p>
    </div>
    
    <div class="container">
        <div class="stats">
            <div class="stat-card">
                <h3><?php echo count($links); ?></h3>
                <p>Total Links</p>
            </div>
            <div class="stat-card">
                <h3><?php echo count($users); ?></h3>
                <p>Total Users</p>
            </div>
            <div class="stat-card">
                <h3><?php echo array_sum(array_column($links, 'click_count')); ?></h3>
                <p>Total Clicks</p>
            </div>
        </div>
        
        <a href="../public/index.php" class="btn">Home</a>
        <a href="../user/dashboard.php" class="btn">My Dashboard</a>
        <a href="../user/logout.php" class="btn">Logout</a>
        
        <?php if (isset($message)): ?>
            <div style="padding: 15px; background: #d4edda; color: #155724; border-radius: 5px; margin: 20px 0;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="section">
            <h2>Create New Link</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Long URL:</label>
                    <input type="text" name="long_url" required>
                </div>
                <div class="form-group">
                    <label>Short Code:</label>
                    <input type="text" name="short_code" required>
                </div>
                <button type="submit" name="create_link" class="btn">Create Link</button>
            </form>
        </div>
        
        <div class="section">
            <h2>All Links</h2>
            <form method="GET" style="margin-bottom: 20px;">
                <input type="text" name="search" placeholder="Search by URL or short code..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn">Search</button>
            </form>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Short Code</th>
                        <th>Long URL</th>
                        <th>User</th>
                        <th>Clicks</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($links as $link): ?>
                        <tr>
                            <td><?php echo $link['id']; ?></td>
                            <td><?php echo htmlspecialchars($link['short_code']); ?></td>
                            <td><?php echo htmlspecialchars(substr($link['long_url'], 0, 50)) . (strlen($link['long_url']) > 50 ? '...' : ''); ?></td>
                            <td><?php echo $link['email'] ?? 'Public'; ?></td>
                            <td><?php echo $link['click_count']; ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($link['created_at'])); ?></td>
                            <td class="actions">
                                <a href="?delete_link=<?php echo $link['id']; ?>" onclick="return confirm('Delete this link?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="section">
            <h2>All Users</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Email</th>
                        <th>Verified</th>
                        <th>Admin</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo $user['is_verified'] ? 'Yes' : 'No'; ?></td>
                            <td><?php echo $user['is_admin'] ? 'Yes' : 'No'; ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($user['created_at'])); ?></td>
                            <td class="actions">
                                <?php if (!$user['is_admin']): ?>
                                    <a href="?delete_user=<?php echo $user['id']; ?>" onclick="return confirm('Delete this user?')">Delete</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

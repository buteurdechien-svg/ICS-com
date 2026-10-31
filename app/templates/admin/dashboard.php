<?php
$pageTitle = 'Administration';
include __DIR__ . '/../partials/header.php';
$db = getDB();
$currentUser = getCurrentUser();

// VULN INTENTIONNELLE : vérification faible — seul le rôle en session est vérifié
// Le champ is_admin en base n'est pas vérifié ici
// Un utilisateur avec role=membre mais session modifiée peut accéder

$totalUsers = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$activeUsers = $db->query("SELECT COUNT(*) FROM users WHERE status = 'actif'")->fetchColumn();
$pendingUsers = $db->query("SELECT COUNT(*) FROM users WHERE status = 'en_attente'")->fetchColumn();
$totalPosts = $db->query("SELECT COUNT(*) FROM posts")->fetchColumn();
$totalMessages = $db->query("SELECT COUNT(*) FROM messages")->fetchColumn();
$totalDonations = (int)$db->query("SELECT setting_value FROM donation_settings WHERE setting_key = 'total_collected'")->fetchColumn();
$totalReports = $db->query("SELECT COUNT(*) FROM reports WHERE status = 'pending'")->fetchColumn();
$recentLogs = $db->query("SELECT a.*, u.display_name FROM audit_logs a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC LIMIT 10")->fetchAll();
?>

<div class="container page-content">
    <div class="flex flex-between flex-center mb-lg">
        <h2 style="font-family: var(--font-heading); font-size: 1.25rem;">Tableau de bord</h2>
        <div class="flex gap-sm">
            <a href="/admin/users" class="btn btn-sm btn-secondary">Utilisateurs</a>
            <a href="/admin/logs" class="btn btn-sm btn-secondary">Logs</a>
        </div>
    </div>

    <!-- Stats -->
    <div class="admin-grid">
        <div class="admin-stat electric">
            <div class="admin-stat-value"><?= $totalUsers ?></div>
            <div class="admin-stat-label">Utilisateurs</div>
        </div>
        <div class="admin-stat phosphor">
            <div class="admin-stat-value"><?= $activeUsers ?></div>
            <div class="admin-stat-label">Actifs</div>
        </div>
        <div class="admin-stat" style="--admin-stat-value-color: var(--amber);">
            <div class="admin-stat-value" style="color: var(--amber);"><?= $pendingUsers ?></div>
            <div class="admin-stat-label">En attente</div>
        </div>
        <div class="admin-stat iris">
            <div class="admin-stat-value"><?= $totalPosts ?></div>
            <div class="admin-stat-label">Publications</div>
        </div>
        <div class="admin-stat electric">
            <div class="admin-stat-value"><?= $totalMessages ?></div>
            <div class="admin-stat-label">Messages</div>
        </div>
        <div class="admin-stat phosphor">
            <div class="admin-stat-value"><?= formatCurrency($totalDonations) ?></div>
            <div class="admin-stat-label">Dons collectés</div>
        </div>
    </div>

    <!-- Activité récente -->
    <div class="card">
        <h3 class="card-title" style="margin-bottom: var(--space-md);">Activité récente</h3>
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Action</th>
                        <th>Cible</th>
                        <th>IP</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentLogs as $log): ?>
                        <tr>
                            <td><?= e($log['display_name'] ?? 'Système') ?></td>
                            <td><span class="font-mono" style="font-size: 0.8125rem;"><?= e($log['action']) ?></span></td>
                            <td><?= e(($log['target_type'] ?? '') . '#' . ($log['target_id'] ?? '')) ?></td>
                            <td class="font-mono" style="font-size: 0.8125rem;"><?= e($log['ip_address'] ?? '-') ?></td>
                            <td class="text-muted"><?= timeAgo($log['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>

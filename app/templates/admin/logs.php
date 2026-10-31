<?php
$pageTitle = 'Logs d\'audit';
include __DIR__ . '/../partials/header.php';
$db = getDB();

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 50;
$offset = ($page - 1) * $perPage;

$total = $db->query("SELECT COUNT(*) FROM audit_logs")->fetchColumn();
$logs = $db->query("SELECT a.*, u.display_name FROM audit_logs a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC LIMIT {$perPage} OFFSET {$offset}")->fetchAll();
$totalPages = ceil($total / $perPage);
?>

<div class="container page-content">
    <div class="flex flex-between flex-center mb-lg">
        <h2 style="font-family: var(--font-heading); font-size: 1.25rem;">Logs d'audit (<?= $total ?>)</h2>
        <a href="/admin" class="btn btn-sm btn-secondary">Retour au dashboard</a>
    </div>

    <div class="card">
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Utilisateur</th>
                        <th>Action</th>
                        <th>Cible</th>
                        <th>Détails</th>
                        <th>IP</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="font-mono"><?= $log['id'] ?></td>
                            <td><?= e($log['display_name'] ?? 'Système') ?></td>
                            <td><span class="font-mono" style="font-size: 0.8125rem;"><?= e($log['action']) ?></span></td>
                            <td class="font-mono" style="font-size: 0.8125rem;"><?= e(($log['target_type'] ?? '') . '#' . ($log['target_id'] ?? '')) ?></td>
                            <td class="font-mono" style="font-size: 0.75rem; max-width: 200px; overflow: hidden; text-overflow: ellipsis;"><?= e($log['details'] ?? '') ?></td>
                            <td class="font-mono" style="font-size: 0.8125rem;"><?= e($log['ip_address'] ?? '-') ?></td>
                            <td class="text-muted" style="white-space: nowrap;"><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="flex flex-center gap-sm mt-lg" style="justify-content: center;">
                <?php if ($page > 1): ?>
                    <a href="/admin/logs?page=<?= $page - 1 ?>" class="btn btn-sm btn-secondary">Précédent</a>
                <?php endif; ?>
                <span class="text-muted">Page <?= $page ?> / <?= $totalPages ?></span>
                <?php if ($page < $totalPages): ?>
                    <a href="/admin/logs?page=<?= $page + 1 ?>" class="btn btn-sm btn-secondary">Suivant</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>

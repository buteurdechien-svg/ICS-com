<?php
$pageTitle = 'Gestion des utilisateurs';
include __DIR__ . '/../partials/header.php';
$db = getDB();

$users = $db->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
?>

<div class="container page-content">
    <div class="flex flex-between flex-center mb-lg">
        <h2 style="font-family: var(--font-heading); font-size: 1.25rem;">Gestion des utilisateurs</h2>
        <a href="/admin" class="btn btn-sm btn-secondary">Retour au dashboard</a>
    </div>

    <div class="card">
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                        <th>Département</th>
                        <th>Inscrit le</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="font-mono"><?= $user['id'] ?></td>
                            <td>
                                <a href="/profile/<?= $user['id'] ?>"><?= e($user['display_name']) ?></a>
                                <?php if ($user['is_admin']): ?>
                                    <span class="notif-badge" style="background: var(--iris); margin-left: 4px;">admin</span>
                                <?php endif; ?>
                            </td>
                            <td class="font-mono" style="font-size: 0.8125rem;"><?= e($user['email']) ?></td>
                            <td><span class="post-badge badge-<?= $user['role'] ?>"><?= $user['role'] ?></span></td>
                            <td><span class="status status-<?= $user['status'] ?>"><?= $user['status'] ?></span></td>
                            <td><?= e($user['department'] ?? '-') ?></td>
                            <td class="text-muted"><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                            <td>
                                <a href="/api/users/<?= $user['id'] ?>" class="btn btn-sm btn-secondary" target="_blank">API</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>

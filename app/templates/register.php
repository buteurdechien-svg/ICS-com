<?php
$pageTitle = 'Inscription';
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $displayName = trim($_POST['display_name'] ?? '');

    if (strlen($password) < 6) {
        $error = 'Le mot de passe doit contenir au moins 6 caractères.';
    } elseif (empty($username) || empty($email) || empty($displayName)) {
        $error = 'Tous les champs sont obligatoires.';
    } else {
        $result = registerUser($username, $email, $password, $displayName);
        if ($result['success']) {
            $success = true;
        } else {
            $error = $result['error'];
        }
    }
}

include __DIR__ . '/partials/header.php';
?>

<div class="auth-container">
    <div class="auth-card animate-in">
        <h1>Rejoindre ICS</h1>

        <?php if ($success): ?>
            <div class="alert alert-success">
                Inscription réussie ! Votre compte sera activé automatiquement dans <?= AUTO_ACTIVATION_DELAY ?> secondes.
                <div class="mt-sm">
                    <a href="/login" class="btn btn-sm btn-primary">Se connecter</a>
                </div>
            </div>
            <div id="countdown" class="text-center mt-md" style="font-family: var(--font-mono); font-size: 1.25rem; color: var(--electric);">
                Activation dans <span id="timer"><?= AUTO_ACTIVATION_DELAY ?></span>s...
            </div>
            <script>
                let t = <?= AUTO_ACTIVATION_DELAY ?>;
                const el = document.getElementById('timer');
                const iv = setInterval(() => {
                    t--;
                    el.textContent = t;
                    if (t <= 0) {
                        clearInterval(iv);
                        document.getElementById('countdown').innerHTML = '<span style="color: var(--phosphor);">✓ Compte activé ! Vous pouvez vous connecter.</span>';
                    }
                }, 1000);
            </script>
        <?php else: ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="/register">
                <div class="form-group">
                    <label class="form-label" for="display_name">Nom complet</label>
                    <input type="text" id="display_name" name="display_name" class="form-input" required
                           value="<?= e($_POST['display_name'] ?? '') ?>"
                           placeholder="Prénom NOM">
                </div>

                <div class="form-group">
                    <label class="form-label" for="username">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" class="form-input" required
                           value="<?= e($_POST['username'] ?? '') ?>"
                           placeholder="votre_pseudo" pattern="[a-zA-Z0-9_]{3,30}">
                    <div class="form-hint">3 à 30 caractères, lettres, chiffres et underscore</div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Adresse email</label>
                    <input type="email" id="email" name="email" class="form-input" required
                           value="<?= e($_POST['email'] ?? '') ?>"
                           placeholder="vous@exemple.ci">
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" class="form-input" required
                           placeholder="Minimum 6 caractères" minlength="6">
                </div>

                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; justify-content: center;">
                    Créer mon compte
                </button>
            </form>

            <div class="auth-footer">
                Déjà membre ? <a href="/login">Se connecter</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>

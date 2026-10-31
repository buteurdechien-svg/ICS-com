<?php
$pageTitle = 'Connexion';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $result = loginUser($email, $password);
    if ($result['success']) {
        header('Location: /feed');
        exit;
    }
    $error = $result['error'];
}

include __DIR__ . '/partials/header.php';
?>

<div class="auth-container">
    <div class="auth-card animate-in">
        <h1>Connexion</h1>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="/login">
            <div class="form-group">
                <label class="form-label" for="email">Adresse email</label>
                <input type="email" id="email" name="email" class="form-input" required
                       value="<?= e($_POST['email'] ?? '') ?>"
                       placeholder="vous@exemple.ci">
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Mot de passe</label>
                <input type="password" id="password" name="password" class="form-input" required
                       placeholder="Votre mot de passe">
            </div>

            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; justify-content: center;">
                Se connecter
            </button>
        </form>

        <div class="auth-footer">
            Pas encore membre ? <a href="/register">Créer un compte</a>
        </div>

        <!-- VULN INFO: aide pour les pentesters -->
        <!-- Comptes de test: emails au format <pseudo>@ics-lab.ci (voir la page Membres) -->
    </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>

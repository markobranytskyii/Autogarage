<?php

// Gedeelde login-, rol- en CSRF-controles. Bovenaan elke pagina: require 'auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isIngelogd(): bool
{
    return isset($_SESSION['user_id']);
}

// Stuurt de bezoeker naar de loginpagina als hij niet is ingelogd.
function vereisLogin(): void
{
    if (!isIngelogd()) {
        header('Location: login.php?melding=inloggen');
        exit;
    }
}

// Alleen gebruikers met een van de opgegeven rollen mogen verder, bijvoorbeeld vereisRol('admin', 'medewerker').
function vereisRol(string ...$rollen): void
{
    vereisLogin();

    if (!in_array($_SESSION['role'] ?? '', $rollen, true)) {
        header('Location: 403.php');
        exit;
    }
}

// Formulierverwerking mag alleen via POST.
function vereisPost(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: index.php');
        exit;
    }
}

// CSRF-token: één willekeurige code per sessie.
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verborgen veld voor in elk formulier.
function csrfVeld(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrfToken()) . '">';
}

// Controleert of het meegestuurde token klopt (uit het formulier of uit de X-CSRF-Token header bij AJAX).
function controleerCsrf(): void
{
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

    if (!is_string($token) || !hash_equals(csrfToken(), $token)) {
        http_response_code(403);
        header('Location: 403.php');
        exit;
    }
}

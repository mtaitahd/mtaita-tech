<?php
// Public user session helpers
function isPublicLoggedIn(): bool {
    return isset($_SESSION['public_user_id']);
}

function getPublicUserId(): ?int {
    return $_SESSION['public_user_id'] ?? null;
}

function getPublicUser(): ?array {
    global $pdo;
    $id = getPublicUserId();
    if (!$id) return null;
    $stmt = $pdo->prepare("SELECT id, name, email, phone, profile_picture FROM public_users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function requirePublicLogin(): void {
    if (!isPublicLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: login');
        exit;
    }
}

function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(string $token): bool {
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

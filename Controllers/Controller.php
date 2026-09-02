<?php

namespace Project\Controllers;

abstract class Controller
{
    public function render(string $path, array $data = []): void
    {
        $data['csrfToken'] = $this->generateCsrfToken();
        extract($data);
        ob_start();
        include __DIR__ . '/../Views/' . $path . '.php';
        $content = ob_get_clean();
        include __DIR__ . '/../Views/home/base.php';
    }

    protected function redirect(string $url): void
    {
        header('Location: ' . BASE_URL . $url);
        exit;
    }

    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function requireUser(): void
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/index.php?controller=user&action=login');
        }
    }

    protected function requireAdmin(): void
    {
        $this->requireUser();
        if (($_SESSION['user_role'] ?? '') !== 'admin') {
            $this->redirect('/index.php?controller=home&action=index');
        }
    }

    protected function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    protected function generateCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    protected function verifyCsrfToken(string $token): void
    {
        if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            http_response_code(403);
            die('Token CSRF invalide.');
        }
    }

    protected function regenerateSession(): void
    {
        session_regenerate_id(true);
        unset($_SESSION['csrf_token']);
    }
}
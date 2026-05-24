<?php

class AuthController {

    public function loginForm(): void {
        if (isLoggedIn()) redirect('/');
        $pageTitle = 'Login';
        $content   = $this->renderView('auth/login');
        require __DIR__ . '/../Views/layouts/app.php';
    }

    public function login(): void {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            flash('error', 'Invalid email or password.');
            redirect('/auth/login');
        }

        $_SESSION['user_id'] = $user['id'];
        redirect('/');
    }

    public function registerForm(): void {
        if (isLoggedIn()) redirect('/');
        $pageTitle = 'Create Account';
        $content   = $this->renderView('auth/register');
        require __DIR__ . '/../Views/layouts/app.php';
    }

    public function register(): void {
        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$name || !$email || strlen($password) < 6) {
            flash('error', 'All fields required; password must be at least 6 characters.');
            redirect('/auth/register');
        }

        try {
            $stmt = db()->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
            $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
            $_SESSION['user_id'] = db()->lastInsertId();
            redirect('/');
        } catch (PDOException $e) {
            flash('error', 'Email already registered.');
            redirect('/auth/register');
        }
    }

    public function logout(): void {
        session_destroy();
        redirect('/auth/login');
    }

    private function renderView(string $view): string {
        ob_start();
        require __DIR__ . "/../Views/{$view}.php";
        return ob_get_clean();
    }
}

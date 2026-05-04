<?php
require_once CORE_PATH . '/Controller.php';
require_once APP_PATH  . '/models/User.php';

class AuthController extends Controller {

    public function loginForm(): void {
        if (!empty($_SESSION['user_id'])) $this->redirect('/dashboard');
        $this->render('auth/login');
    }

    public function login(): void {
        $this->requireCsrf();

        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $attemptKey = 'login_attempts_' . $ip;
        $lockKey    = 'login_locked_' . $ip;

        if (isset($_SESSION[$lockKey]) && $_SESSION[$lockKey] > time()) {
            $this->flash('error', 'Demasiados intentos. Espera ' . ceil(($_SESSION[$lockKey] - time()) / 60) . ' minutos.');
            $this->redirect('/login');
        }

        $maxAttempts = 5;
        $windowTime   = 900;

        if (!isset($_SESSION[$attemptKey])) {
            $_SESSION[$attemptKey] = ['count' => 0, 'first' => time()];
        }

        $attempts = &$_SESSION[$attemptKey];

        if (time() - $attempts['first'] > $windowTime) {
            $attempts = ['count' => 0, 'first' => time()];
        }

        if ($attempts['count'] >= $maxAttempts) {
            $_SESSION[$lockKey] = time() + 900;
            $this->flash('error', 'Demasiados intentos. Intenta de nuevo en 15 minutos.');
            $this->redirect('/login');
        }

        $credential = trim($_POST['credential'] ?? '');
        $password   = $_POST['password'] ?? '';

        if (empty($credential) || empty($password)) {
            $this->flash('error', 'Completa todos los campos.');
            $this->redirect('/login');
        }

        $user = (new User())->findByCredential($credential);

        if (!$user || !password_verify($password, $user['password'])) {
            $attempts['count']++;
            $this->flash('error', 'Credenciales incorrectas.');
            $this->redirect('/login');
        }

        unset($_SESSION[$attemptKey], $_SESSION[$lockKey]);

        if ($user['is_banned']) {
            $this->flash('error', 'Tu cuenta está suspendida.');
            $this->redirect('/login');
        }

        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_name']  = $user['name'];
        $_SESSION['user_role']  = $user['role'];
        $_SESSION['user_photo'] = $user['photo'] ?? 'default.png';
        session_regenerate_id(true);

        $this->flash('success', '¡Bienvenido, ' . $user['name'] . '!');
        $this->redirect('/dashboard');
    }

    public function registerForm(): void {
        if (!empty($_SESSION['user_id'])) $this->redirect('/dashboard');
        $this->render('auth/register');
    }

    public function register(): void {
        $this->requireCsrf();

        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['password_confirm'] ?? '';

        if (empty($name) || empty($email) || empty($password)) {
            $this->flash('error', 'Nombre, email y contraseña son obligatorios.');
            $this->redirect('/register');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flash('error', 'Email no válido.');
            $this->redirect('/register');
        }
        if (strlen($password) < 8) {
            $this->flash('error', 'La contraseña debe tener mínimo 8 caracteres.');
            $this->redirect('/register');
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $this->flash('error', 'La contraseña debe contener al menos una mayúscula.');
            $this->redirect('/register');
        }
        if (!preg_match('/[0-9]/', $password)) {
            $this->flash('error', 'La contraseña debe contener al menos un número.');
            $this->redirect('/register');
        }
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $this->flash('error', 'La contraseña debe contener al menos un carácter especial.');
            $this->redirect('/register');
        }
        if ($password !== $confirm) {
            $this->flash('error', 'Las contraseñas no coinciden.');
            $this->redirect('/register');
        }

        $userModel = new User();
        if ($userModel->findOneWhere(['email' => $email])) {
            $this->flash('error', 'Ese email ya está registrado.');
            $this->redirect('/register');
        }

        $age = (int)($_POST['age'] ?? 0);
        $age = ($age >= 16 && $age <= 99) ? $age : null;

        $id = $userModel->insert([
            'name'      => $name,
            'email'     => $email,
            'phone'     => trim($_POST['phone'] ?? ''),
            'password'  => password_hash($password, HASH_ALGO, ['cost' => HASH_COST]),
            'role'      => 'player',
            'position'  => $_POST['position'] ?? '',
            'city'      => trim($_POST['city'] ?? ''),
            'age'       => $age,
        ]);

        $_SESSION['user_id']    = $id;
        $_SESSION['user_name']  = $name;
        $_SESSION['user_role']  = 'player';
        $_SESSION['user_photo'] = 'default.png';
        session_regenerate_id(true);

        $this->flash('success', '¡Cuenta creada! Bienvenido a FastPlay.');
        $this->redirect('/dashboard');
    }

    public function logout(): void {
        $_SESSION = [];
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', [
                'expires'  => time() - 3600,
                'path'     => '/',
                'secure'   => isset($_SERVER['HTTPS']),
                'httponly'  => true,
                'samesite' => 'Strict',
            ]);
        }
        session_regenerate_id(true);
        session_destroy();
        $this->redirect('/');
    }
}

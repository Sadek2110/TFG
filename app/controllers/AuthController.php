<?php
// FastPlay · login / registro / logout
require_once APP_PATH . '/services/MailService.php';

class AuthController extends Controller
{
    public function login(): void
    {
        $this->requireGuest();
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_csrf();
            $usuario = $this->model('Usuario');
            $email = trim($_POST['email'] ?? '');
            $user = $usuario->login($email, $_POST['password'] ?? '', $_SERVER['REMOTE_ADDR'] ?? '');
            if ($user) {
                login_user($user);
                flash('ok', '¡Bienvenido de vuelta, ' . $user['name'] . '!');
                redirect('dashboard');
            }
            $errors['_'] = 'Email o contraseña incorrectos. Si fallas demasiadas veces, espera 10 min.';
            flash_old(['email' => $email]);
        }

        $this->view('auth/login', [
            'active' => 'login',
            'errors' => $errors,
            'title'  => 'Iniciar sesión — FastPlay',
        ], 'auth');
    }

    public function register(): void
    {
        $this->requireGuest();
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_csrf();
            $usuario = $this->model('Usuario');
            [$user, $errors] = $usuario->register($_POST);
            if ($user) {
                // Enviar correo de bienvenida y verificación
                $token = $user['verification_token'] ?? '';
                $verificationUrl = ($_SERVER['REQUEST_SCHEME'] ?? 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . url('auth/verify?token=' . $token);
                MailService::send($user['email'], '¡Bienvenido a FastPlay! Verifica tu correo', 'bienvenida_verificacion', [
                    'name' => $user['name'],
                    'url'  => $verificationUrl
                ]);

                login_user($user);
                flash('ok', '¡Cuenta creada! Hemos enviado un correo de bienvenida y verificación a ' . $user['email']);
                redirect('dashboard');
            }
            flash_old($_POST);
        }

        $this->view('auth/register', [
            'active' => 'register',
            'errors' => $errors,
            'title'  => 'Crear cuenta — FastPlay',
        ], 'auth');
    }

    public function logout(): void
    {
        $this->requirePost();
        logout_user();
        flash('ok', 'Has cerrado sesión.');
        redirect('');
    }

    public function google(): void
    {
        $this->requireGuest();
        $provider = $this->googleProvider();
        if (!$provider) {
            flash('warn', 'El acceso con Google no está configurado todavía.');
            $this->back('auth/login');
        }

        $authUrl = $provider->getAuthorizationUrl();
        $_SESSION['oauth2state'] = $provider->getState();
        redirect($authUrl);
    }

    public function googleCallback(): void
    {
        $this->requireGuest();
        $provider = $this->googleProvider();
        if (!$provider) {
            flash('warn', 'El acceso con Google no está configurado todavía.');
            redirect('auth/login');
        }

        if (empty($_GET['state']) || ($_GET['state'] !== ($_SESSION['oauth2state'] ?? ''))) {
            unset($_SESSION['oauth2state']);
            flash('warn', 'Estado de sesión inválido.');
            redirect('auth/login');
        }

        try {
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $_GET['code'] ?? ''
            ]);
            /** @var \League\OAuth2\Client\Provider\GoogleUser $ownerDetails */
            $ownerDetails = $provider->getResourceOwner($token);

            $usuario = $this->model('Usuario');
            [$user, $errors, $isNew] = $usuario->registerOrLoginWithGoogle([
                'id' => $ownerDetails->getId(),
                'email' => $ownerDetails->getEmail(),
                'name' => $ownerDetails->getName(),
                'avatar' => $ownerDetails->getAvatar(),
            ]);

            if ($user) {
                if ($isNew) {
                    $dashboardUrl = ($_SERVER['REQUEST_SCHEME'] ?? 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . url('dashboard');
                    MailService::send($user['email'], '¡Bienvenido a FastPlay!', 'bienvenida', [
                        'name' => $user['name'],
                        'url'  => $dashboardUrl
                    ]);
                }
                login_user($user);
                flash('ok', '¡Bienvenido ' . $user['name'] . '!');
                redirect('dashboard');
            } else {
                flash('warn', $errors['email'] ?? 'Error al procesar el inicio de sesión con Google.');
                redirect('auth/login');
            }
        } catch (\Exception $e) {
            flash('warn', 'Error de autenticación con Google.');
            redirect('auth/login');
        }
    }

    private function googleProvider(): ?\League\OAuth2\Client\Provider\Google
    {
        $clientId = trim((string) (getenv('GOOGLE_CLIENT_ID') ?: ''));
        $clientSecret = trim((string) (getenv('GOOGLE_CLIENT_SECRET') ?: ''));

        if ($clientId === '' || $clientSecret === '') {
            return null;
        }

        return new \League\OAuth2\Client\Provider\Google([
            'clientId'     => $clientId,
            'clientSecret' => $clientSecret,
            'redirectUri'  => absolute_url('auth/google/callback'),
        ]);
    }

    public function verify(): void
    {
        $token = trim((string) ($_GET['token'] ?? ''));
        if ($token === '') {
            flash('warn', 'Token de verificación no proporcionado.');
            redirect(is_auth() ? 'dashboard' : 'auth/login');
        }

        // Buscar el usuario por el token
        $user = Database::one('SELECT * FROM users WHERE verification_token = ?', [$token]);
        if (!$user) {
            flash('warn', 'El enlace de verificación no es válido o ya ha expirado.');
            redirect(is_auth() ? 'dashboard' : 'auth/login');
        }

        // Marcar al usuario como verificado
        Database::run('UPDATE users SET email_verified = 1, verification_token = NULL WHERE id = ?', [$user['id']]);

        // Si el usuario actual está logueado en la sesión y coincide, actualizamos su sesión
        $currentUser = current_user();
        if ($currentUser && (int) $currentUser['id'] === (int) $user['id']) {
            $user['email_verified'] = 1;
            $user['verification_token'] = null;
            unset($user['password_hash']);
            login_user($user);
        }

        if (!is_auth()) {
            flash_old(['email' => $user['email']]);
        }

        flash('ok', '¡Correo verificado con éxito! Tu cuenta está completamente activa.');
        redirect(is_auth() ? 'dashboard' : 'auth/login');
    }

    public function resendVerification(): void
    {
        $this->requireAuth();
        $this->requirePost();

        $user = current_user();
        // Cargar datos actualizados de la base de datos
        $dbUser = Database::one('SELECT * FROM users WHERE id = ?', [$user['id']]);
        if (!$dbUser) {
            flash('warn', 'Usuario no encontrado.');
            redirect('dashboard');
        }

        if ((int) ($dbUser['email_verified'] ?? 0) === 1) {
            flash('ok', 'Tu correo ya está verificado.');
            redirect('dashboard');
        }

        // Si no tiene token, generar uno nuevo
        $token = $dbUser['verification_token'] ?? '';
        if ($token === '') {
            $token = bin2hex(random_bytes(32));
            Database::run('UPDATE users SET verification_token = ? WHERE id = ?', [$token, $dbUser['id']]);
        }

        // Enviar el correo
        $verificationUrl = ($_SERVER['REQUEST_SCHEME'] ?? 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . url('auth/verify?token=' . $token);
        MailService::send($dbUser['email'], '¡Bienvenido a FastPlay! Verifica tu correo', 'bienvenida_verificacion', [
            'name' => $dbUser['name'],
            'url'  => $verificationUrl
        ]);

        flash('ok', 'Hemos reenviado el correo de verificación a ' . $dbUser['email']);
        redirect('dashboard');
    }
}

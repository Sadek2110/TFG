<?php
// FastPlay · login / registro / logout

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
                login_user($user);
                flash('ok', '¡Cuenta creada! Empieza a jugar.');
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
        $provider = new \League\OAuth2\Client\Provider\Google([
            'clientId'     => getenv('GOOGLE_CLIENT_ID') ?: 'DUMMY_ID',
            'clientSecret' => getenv('GOOGLE_CLIENT_SECRET') ?: 'DUMMY_SECRET',
            'redirectUri'  => url('auth/google/callback'),
        ]);
        $authUrl = $provider->getAuthorizationUrl();
        $_SESSION['oauth2state'] = $provider->getState();
        redirect($authUrl);
    }

    public function googleCallback(): void
    {
        $this->requireGuest();
        $provider = new \League\OAuth2\Client\Provider\Google([
            'clientId'     => getenv('GOOGLE_CLIENT_ID') ?: 'DUMMY_ID',
            'clientSecret' => getenv('GOOGLE_CLIENT_SECRET') ?: 'DUMMY_SECRET',
            'redirectUri'  => url('auth/google/callback'),
        ]);

        if (empty($_GET['state']) || ($_GET['state'] !== ($_SESSION['oauth2state'] ?? ''))) {
            unset($_SESSION['oauth2state']);
            flash('warn', 'Estado de sesión inválido.');
            redirect('login');
        }

        try {
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $_GET['code'] ?? ''
            ]);
            /** @var \League\OAuth2\Client\Provider\GoogleUser $ownerDetails */
            $ownerDetails = $provider->getResourceOwner($token);

            $usuario = $this->model('Usuario');
            [$user, $errors] = $usuario->registerOrLoginWithGoogle([
                'id' => $ownerDetails->getId(),
                'email' => $ownerDetails->getEmail(),
                'name' => $ownerDetails->getName(),
                'avatar' => $ownerDetails->getAvatar(),
            ]);

            if ($user) {
                login_user($user);
                flash('ok', '¡Bienvenido ' . $user['name'] . '!');
                redirect('dashboard');
            } else {
                flash('warn', $errors['email'] ?? 'Error al procesar el inicio de sesión con Google.');
                redirect('login');
            }
        } catch (\Exception $e) {
            flash('warn', 'Error de autenticación con Google.');
            redirect('login');
        }
    }
}
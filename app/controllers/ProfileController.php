<?php
// FastPlay · perfil del usuario

class ProfileController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $user = current_user();
        $usuario = $this->model('Usuario');
        $equipo  = $this->model('Equipo');
        $fresh = $usuario->find((int) $user['id']) ?? $user;

        $this->view('profile/index', [
            'active'       => 'profile',
            'profile'      => $fresh,
            'teams'        => $equipo->ofUser((int) $user['id']),
            'achievements' => $usuario->achievements((int) $user['id']),
            'title'        => 'Mi perfil — FastPlay',
        ]);
    }

    public function edit(): void
    {
        $this->requireAuth();
        $user = current_user();
        $errors = [];
        $usuario = $this->model('Usuario');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_csrf();
            $errors = $usuario->updateProfile((int) $user['id'], $_POST);
            if (!$errors && !empty($_FILES['avatar']['name'])) {
                [, $avErrors] = $usuario->updateAvatar((int) $user['id'], $_FILES['avatar']);
                if ($avErrors) {
                    $errors = array_merge($errors, $avErrors);
                }
            }
            if (!$errors) {
                $fresh = $usuario->find((int) $user['id']);
                if ($fresh) {
                    session_regenerate_id(true);
                    unset($fresh['password_hash']);
                    $_SESSION['user'] = $fresh;
                }
                flash('ok', 'Perfil actualizado.');
                redirect('profile');
            }
            flash_old($_POST);
        }

        $fresh = $usuario->find((int) $user['id']) ?? $user;
        $this->view('profile/edit', [
            'active'  => 'profile',
            'profile' => $fresh,
            'errors'  => $errors,
            'title'   => 'Editar perfil — FastPlay',
        ]);
    }

    public function password(): void
    {
        $this->requireAuth();
        $user = current_user();
        $errors = [];
        $usuario = $this->model('Usuario');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_csrf();
            $errors = $usuario->changePassword(
                (int) $user['id'],
                (string) ($_POST['current'] ?? ''),
                (string) ($_POST['new'] ?? ''),
                (string) ($_POST['confirm'] ?? '')
            );
            if (!$errors) {
                flash('ok', 'Contraseña actualizada.');
                redirect('profile');
            }
        }

        $this->view('profile/password', [
            'active' => 'profile',
            'errors' => $errors,
            'title'  => 'Cambiar contraseña — FastPlay',
        ]);
    }
}
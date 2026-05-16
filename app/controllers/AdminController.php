<?php
// FastPlay · panel admin

class AdminController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();
        $counts = [
            'users'    => (int) Database::value('SELECT COUNT(*) FROM users'),
            'teams'    => (int) Database::value('SELECT COUNT(*) FROM teams'),
            'leagues'  => (int) Database::value('SELECT COUNT(*) FROM leagues'),
            'matches'  => (int) Database::value('SELECT COUNT(*) FROM matches'),
            'fields'   => (int) Database::value('SELECT COUNT(*) FROM fields'),
            'rooms'    => (int) Database::value('SELECT COUNT(*) FROM chat_rooms'),
        ];
        $this->view('admin/index', [
            'active' => 'admin',
            'counts' => $counts,
            'recent' => Database::all("SELECT email, success, attempted_at FROM login_attempts ORDER BY id DESC LIMIT 12"),
            'title'  => 'Admin — FastPlay',
        ]);
    }

    public function users(): void
    {
        $this->requireAdmin();
        $usuario = $this->model('Usuario');
        $this->view('admin/users', [
            'active' => 'admin',
            'users'  => $usuario->all(),
            'title'  => 'Admin · Usuarios — FastPlay',
        ]);
    }

    public function setRole(string $id = ''): void
    {
        $this->requireAdmin();
        $this->requirePost();
        $targetId = (int) $id;
        $newRole = (string) ($_POST['role'] ?? 'player');
        if ($newRole !== 'admin') {
            $admins = (int) Database::value("SELECT COUNT(*) FROM users WHERE role='admin'");
            $current = Database::one('SELECT role FROM users WHERE id=?', [$targetId]);
            if ($current && $current['role'] === 'admin' && $admins <= 1) {
                flash('warn', 'No puedes degradar al último administrador.');
                redirect('admin/users');
                return;
            }
        }
        $this->model('Usuario')->setRole($targetId, $newRole);
        flash('ok', 'Rol actualizado.');
        redirect('admin/users');
    }

    public function deleteUser(string $id = ''): void
    {
        $this->requireAdmin();
        $this->requirePost();
        $targetId = (int) $id;
        if ($targetId === (int) current_user()['id']) {
            flash('warn', 'No puedes eliminar tu propia cuenta desde aquí.');
            redirect('admin/users');
            return;
        }

        $usuario = $this->model('Usuario');
        $target = $usuario->find($targetId);
        if (!$target) {
            flash('warn', 'Usuario no encontrado.');
            redirect('admin/users');
            return;
        }
        if (($target['role'] ?? 'player') === 'admin') {
            $admins = (int) Database::value("SELECT COUNT(*) FROM users WHERE role='admin'");
            if ($admins <= 1) {
                flash('warn', 'No puedes eliminar al último administrador del sistema.');
                redirect('admin/users');
                return;
            }
        }

        $captainOf = (int) Database::value('SELECT COUNT(*) FROM teams WHERE captain_id=?', [$targetId]);
        if ($captainOf > 0) {
            flash('warn', 'No puedes eliminar un usuario que capitanea equipos. Transfiere la capitania antes.');
            redirect('admin/users');
            return;
        }

        $usuario->delete($targetId);
        flash('ok', 'Usuario eliminado.');
        redirect('admin/users');
    }

    public function teams(): void
    {
        $this->requireAdmin();
        $this->view('admin/teams', [
            'active' => 'admin',
            'teams'  => $this->model('Equipo')->all(),
            'title'  => 'Admin · Equipos — FastPlay',
        ]);
    }

    public function leagues(): void
    {
        $this->requireAdmin();
        $this->view('admin/leagues', [
            'active'  => 'admin',
            'leagues' => $this->model('Liga')->all(),
            'title'   => 'Admin · Ligas — FastPlay',
        ]);
    }

    public function fields(): void
    {
        $this->requireAdmin();
        $this->view('admin/fields', [
            'active' => 'admin',
            'fields' => $this->model('Campo')->all(),
            'title'  => 'Admin · Campos — FastPlay',
        ]);
    }

    public function deleteLeague(string $id = ''): void
    {
        $this->requireAdmin();
        $this->requirePost();
        $this->model('Liga')->delete((int) $id);
        flash('ok', 'Liga eliminada.');
        redirect('admin/leagues');
    }

    public function deleteField(string $id = ''): void
    {
        $this->requireAdmin();
        $this->requirePost();
        $this->model('Campo')->delete((int) $id);
        flash('ok', 'Campo eliminado.');
        redirect('admin/fields');
    }
}

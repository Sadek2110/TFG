<?php
// FastPlay · equipos

class TeamsController extends Controller
{
    public function index(): void
    {
        $equipo = $this->model('Equipo');
        $this->view('teams/index', [
            'active' => 'teams',
            'teams'  => $equipo->all(),
            'title'  => 'Equipos — FastPlay',
        ]);
    }

    public function show(string $id = ''): void
    {
        $id = (int) $id;
        $equipo = $this->model('Equipo');
        $team = $equipo->find($id);
        if (!$team) { Router::notFound(); return; }

        $user = current_user();
        $isMember = false;
        if ($user) {
            $isMember = (bool) Database::value(
                'SELECT 1 FROM team_members WHERE team_id=? AND user_id=?',
                [$id, (int) $user['id']]
            );
        }

        $this->view('teams/show', [
            'active'   => 'teams',
            'team'     => $team,
            'members'  => $equipo->members($id),
            'isMember' => $isMember,
            'title'    => $team['name'] . ' — FastPlay',
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_csrf();
            $equipo = $this->model('Equipo');
            [$team, $errors] = $equipo->create((int) current_user()['id'], $_POST);
            if ($team) {
                flash('ok', 'Equipo creado: ' . $team['name']);
                redirect('teams/show/' . $team['id']);
            }
            flash_old($_POST);
        }

        $this->view('teams/create', [
            'active' => 'teams',
            'errors' => $errors,
            'title'  => 'Crear equipo — FastPlay',
        ]);
    }

    public function join(string $id = ''): void
    {
        $this->requireAuth();
        $this->requirePost();
        $id = (int) $id;

        $equipo = $this->model('Equipo');
        if ($equipo->join($id, (int) current_user()['id'])) {
            flash('ok', '¡Te has unido al equipo!');
        } else {
            flash('warn', 'Ya formas parte de ese equipo.');
        }
        redirect('teams/show/' . $id);
    }

    public function leave(string $id = ''): void
    {
        $this->requireAuth();
        $this->requirePost();
        $id = (int) $id;

        $equipo = $this->model('Equipo');
        if ($equipo->leave($id, (int) current_user()['id'])) {
            flash('ok', 'Has salido del equipo.');
        } else {
            flash('warn', 'No puedes salir del equipo siendo capitán. Transfiere la capitanía o elimina el equipo.');
        }
        redirect('teams/show/' . $id);
    }

    public function delete(string $id = ''): void
    {
        $this->requireAuth();
        $this->requirePost();
        $id = (int) $id;

        $equipo = $this->model('Equipo');
        if (!is_admin() && !$equipo->isCaptain($id, (int) current_user()['id'])) {
            flash('warn', 'Sólo el capitán o un admin pueden eliminar el equipo.');
            redirect('teams');
            return;
        }
        $blocker = $equipo->deletionBlocker($id);
        if ($blocker) {
            flash('warn', $blocker);
            redirect('teams/show/' . $id);
            return;
        }
        $equipo->delete($id);
        flash('ok', 'Equipo eliminado.');
        redirect('teams');
    }
}

<?php
require_once CORE_PATH . '/Controller.php';
require_once APP_PATH  . '/models/Team.php';

class TeamController extends Controller {

    public function index(): void {
        $city  = htmlspecialchars(trim($_GET['city'] ?? ''), ENT_QUOTES, 'UTF-8');
        $teams = (new Team())->getAll($city);
        $this->render('team/list', compact('teams'));
    }

    public function detail(string $id): void {
        $team = (new Team())->findById((int)$id);
        if (!$team) { $this->redirect('/teams'); }
        $players = (new Team())->getPlayers((int)$id);
        $this->render('team/detail', compact('team', 'players'));
    }

    public function createForm(): void {
        $this->requireCaptain();
        $this->render('team/create');
    }

    public function create(): void {
        $this->requireCaptain();
        $userId = $_SESSION['user_id'];

        if ((new Team())->findOneWhere(['captain_id' => $userId])) {
            $this->flash('error', 'Ya eres capitán de un equipo.');
            $this->redirect('/teams');
        }

        $name = trim($_POST['name'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $desc = trim($_POST['description'] ?? '');

        if (empty($name) || empty($city)) {
            $this->flash('error', 'Nombre y ciudad son obligatorios.');
            $this->redirect('/teams/create');
        }

        $id = (new Team())->insert(['name'=>$name,'city'=>$city,'description'=>$desc,'captain_id'=>$userId]);
        $this->flash('success', '¡Equipo creado! Ya eres capitán de ' . $name);
        $this->redirect('/teams/' . $id);
    }
}

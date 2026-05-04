<?php
require_once CORE_PATH . '/Controller.php';
require_once APP_PATH  . '/models/User.php';
require_once APP_PATH  . '/models/MatchModel.php';
require_once APP_PATH  . '/models/Team.php';

class UserController extends Controller {

    public function dashboard(): void {
        $this->requireLogin();
        $userId    = $_SESSION['user_id'];
        $userModel = new User();
        $stats     = $userModel->getStats($userId);
        $team      = (new Team())->getTeamByPlayer($userId);
        $upcoming  = (new MatchModel())->getUpcoming(3);
        $achievements = $userModel->getAchievements($userId);
        $this->render('user/dashboard', [
            'stats'          => $stats,
            'team'           => $team,
            'upcomingMatches'=> $upcoming,
            'achievements'   => $achievements,
        ]);
    }

    public function profile(): void {
        $this->requireLogin();
        $user = (new User())->findById($_SESSION['user_id']);
        $this->render('user/profile', compact('user'));
    }

    public function update(): void {
        $this->requireLogin();
        $userId = $_SESSION['user_id'];

        $email = trim($_POST['email'] ?? '');
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flash('error', 'Email no válido.');
            $this->redirect('/profile');
        }
        if (!empty($email)) {
            $existing = (new User())->findOneWhere(['email' => $email]);
            if ($existing && (int)$existing['id'] !== $userId) {
                $this->flash('error', 'Ese email ya está registrado por otro usuario.');
                $this->redirect('/profile');
            }
        }

        $data   = [
            'name'     => trim($_POST['name'] ?? ''),
            'phone'    => trim($_POST['phone'] ?? ''),
            'city'     => trim($_POST['city'] ?? ''),
            'position' => $_POST['position'] ?? '',
            'age'      => (int)($_POST['age'] ?? 0) ?: null,
            'height'   => (int)($_POST['height'] ?? 0) ?: null,
        ];

        if (!empty($_FILES['photo']['tmp_name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg','image/png','image/webp'];
            $finfo   = new finfo(FILEINFO_MIME_TYPE);
            $mime    = $finfo->file($_FILES['photo']['tmp_name']);

            if (in_array($mime, $allowed) && $_FILES['photo']['size'] <= UPLOAD_MAX_SIZE) {
                $imgInfo = getimagesize($_FILES['photo']['tmp_name']);
                if ($imgInfo === false) {
                    $this->flash('error', 'El archivo no es una imagen válida.');
                    $this->redirect('/profile');
                }

                $uploadDir = UPLOAD_PATH . '/profiles/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime];
                $filename = bin2hex(random_bytes(16)) . '.' . $ext;

                $user = (new User())->findById($userId);
                if ($user && $user['photo'] !== 'default.png' && file_exists($uploadDir . $user['photo'])) {
                    unlink($uploadDir . $user['photo']);
                }

                move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $filename);
                chmod($uploadDir . $filename, 0644);
                $data['photo'] = $filename;
                $_SESSION['user_photo'] = $filename;
            } else {
                $this->flash('error', 'Archivo no permitido. Usa JPG, PNG o WebP (máx. 5MB).');
                $this->redirect('/profile');
            }
        }

        (new User())->update($userId, $data);
        $_SESSION['user_name'] = $data['name'];
        $this->flash('success', 'Perfil actualizado.');
        $this->redirect('/profile');
    }
}

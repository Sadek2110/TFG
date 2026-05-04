<?php
require_once CORE_PATH . '/Controller.php';
require_once APP_PATH  . '/models/Chat.php';

class ChatController extends Controller {

    public function index(): void {
        $this->requireLogin();
        $rooms = (new Chat())->getRoomsByUser($_SESSION['user_id']);
        $this->render('chat/index', compact('rooms'));
    }

    public function room(string $id): void {
        $this->requireLogin();
        $chat     = new Chat();
        $room     = $chat->findById((int)$id);
        if (!$room) { $this->redirect('/chat'); }
        if (!$chat->isMember((int)$id, $_SESSION['user_id'])) {
            $this->redirect('/chat');
        }
        $messages = $chat->getMessages((int)$id);
        $this->render('chat/room', compact('room', 'messages'));
    }

    public function sendMessage(string $id): void {
        $this->requireLogin();
        $this->requireCsrf();

        $chat = new Chat();
        if (!$chat->isMember((int)$id, $_SESSION['user_id'])) {
            $this->json(['error' => 'No tienes acceso a esta sala'], 403);
        }

        $content = trim($_POST['content'] ?? '');
        if (empty($content) || strlen($content) > 1000) {
            $this->json(['error' => 'Mensaje inválido'], 400);
        }

        $msg = $chat->sendMessage((int)$id, $_SESSION['user_id'], $content);
        if ($msg) {
            $msg['content']     = htmlspecialchars($msg['content'], ENT_QUOTES, 'UTF-8');
            $msg['author_name'] = htmlspecialchars($msg['author_name'], ENT_QUOTES, 'UTF-8');
            $this->json(['success' => true, 'message' => $msg]);
        }
        $this->json(['error' => 'Error al enviar'], 500);
    }

    public function getMessages(string $id): void {
        $this->requireLogin();

        $chat = new Chat();
        if (!$chat->isMember((int)$id, $_SESSION['user_id'])) {
            $this->json(['error' => 'No tienes acceso'], 403);
        }

        $since = $_GET['since'] ?? null;
        $messages = $chat->getMessages((int)$id, 50);

        if ($since) {
            $messages = array_filter($messages, fn($m) => strtotime($m['created_at']) > (int)$since);
        }

        $this->json(['messages' => array_values($messages)]);
    }
}

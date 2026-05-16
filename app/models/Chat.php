<?php
// FastPlay · modelo Chat (salas + mensajes)

class Chat
{
    public function rooms(?int $userId = null, bool $isAdmin = false): array
    {
        $rooms = Database::all(
            "SELECT r.*,
                    (SELECT COUNT(*) FROM chat_messages WHERE room_id = r.id) AS msg_count,
                    (SELECT body FROM chat_messages WHERE room_id = r.id ORDER BY id DESC LIMIT 1) AS last_body,
                    (SELECT created_at FROM chat_messages WHERE room_id = r.id ORDER BY id DESC LIMIT 1) AS last_at
             FROM chat_rooms r ORDER BY r.id ASC"
        );
        if ($userId === null) {
            return $rooms;
        }
        return array_values(array_filter($rooms, function (array $room) use ($userId, $isAdmin) {
            return $this->canAccessRoom($room, $userId, $isAdmin);
        }));
    }

    public function room(int $id): ?array
    {
        return Database::one('SELECT * FROM chat_rooms WHERE id = ?', [$id]);
    }

    public function messages(int $roomId, int $limit = 200): array
    {
        return Database::all(
            "SELECT m.*, u.name AS user_name
             FROM chat_messages m JOIN users u ON u.id = m.user_id
             WHERE m.room_id = ?
             ORDER BY m.id DESC
             LIMIT ?", [$roomId, $limit]
        );
    }

    public function send(int $roomId, int $userId, string $body, bool $isAdmin = false): array
    {
        $body = trim($body);
        if ($body === '')                 return ['error' => 'El mensaje no puede estar vacío.'];
        if (mb_strlen($body) > 800)       return ['error' => 'Mensaje demasiado largo (máx. 800).'];
        $room = $this->room($roomId);
        if (!$room)                       return ['error' => 'Sala no encontrada.'];
        if (!$this->canAccessRoom($room, $userId, $isAdmin)) {
            return ['error' => 'No tienes acceso a esta sala.'];
        }

        Database::run('INSERT INTO chat_messages (room_id,user_id,body) VALUES (?,?,?)', [$roomId, $userId, $body]);
        return ['ok' => true, 'id' => Database::insertId()];
    }

    public function createRoom(string $name, string $type = 'group'): int
    {
        $name = trim($name) ?: 'Sala';
        if (!in_array($type, ['general','group','match_negotiation','team'], true)) $type = 'group';
        Database::run('INSERT INTO chat_rooms (name,type) VALUES (?,?)', [$name, $type]);
        return Database::insertId();
    }

    public function canAccessRoom(array $room, int $userId, bool $isAdmin = false): bool
    {
        if ($isAdmin) {
            return true;
        }
        if (($room['type'] ?? 'group') === 'match_negotiation') {
            return (bool) Database::value('SELECT 1 FROM teams WHERE captain_id=?', [$userId]);
        }
        return true;
    }
}

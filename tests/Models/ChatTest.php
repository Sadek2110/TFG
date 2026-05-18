<?php

use PHPUnit\Framework\TestCase;

class ChatTest extends TestCase
{
    private Chat $chat;

    protected function setUp(): void
    {
        test_reset();
        $this->chat = new Chat();
    }

    // ===== createRoom() =====
    public function test_create_room(): void
    {
        $id = $this->chat->createRoom('Sala Test', 'group');
        $this->assertGreaterThan(0, $id);

        $room = $this->chat->room($id);
        $this->assertSame('Sala Test', $room['name']);
        $this->assertSame('group', $room['type']);
    }

    public function test_create_room_with_default_type(): void
    {
        $id = $this->chat->createRoom('Sala Default');
        $room = $this->chat->room($id);
        $this->assertSame('group', $room['type']);
    }

    public function test_create_room_empty_name(): void
    {
        $id = $this->chat->createRoom('', 'general');
        $room = $this->chat->room($id);
        $this->assertSame('Sala', $room['name']);
    }

    public function test_create_room_invalid_type(): void
    {
        $id = $this->chat->createRoom('Test', 'invalid_type');
        $room = $this->chat->room($id);
        $this->assertSame('group', $room['type']);
    }

    public function test_create_room_valid_types(): void
    {
        foreach (['general', 'group', 'match_negotiation', 'team'] as $type) {
            $id = $this->chat->createRoom("Room $type", $type);
            $room = $this->chat->room($id);
            $this->assertSame($type, $room['type']);
        }
    }

    // ===== room() =====
    public function test_room_found(): void
    {
        $id = $this->chat->createRoom('Find Me', 'general');
        $room = $this->chat->room($id);
        $this->assertNotNull($room);
        $this->assertSame('Find Me', $room['name']);
    }

    public function test_room_not_found(): void
    {
        $this->assertNull($this->chat->room(9999));
    }

    // ===== send() =====
    public function test_send_message(): void
    {
        $u = test_create_user('Chat User', 'chat@test.com');
        $roomId = $this->chat->createRoom('Lobby', 'general');

        $result = $this->chat->send($roomId, (int) $u['id'], 'Hola mundo');
        $this->assertArrayHasKey('ok', $result);
        $this->assertArrayHasKey('id', $result);

        $messages = $this->chat->messages($roomId);
        $this->assertCount(1, $messages);
        $this->assertSame('Hola mundo', $messages[0]['body']);
    }

    public function test_send_empty_message(): void
    {
        $u = test_create_user();
        $roomId = $this->chat->createRoom('Test Room');

        $result = $this->chat->send($roomId, (int) $u['id'], '   ');
        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('vacío', $result['error']);
    }

    public function test_send_message_too_long(): void
    {
        $u = test_create_user();
        $roomId = $this->chat->createRoom('Test Room');

        $result = $this->chat->send($roomId, (int) $u['id'], str_repeat('a', 801));
        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('largo', $result['error']);
    }

    public function test_send_to_nonexistent_room(): void
    {
        $u = test_create_user();
        $result = $this->chat->send(9999, (int) $u['id'], 'Hello');
        $this->assertArrayHasKey('error', $result);
    }

    public function test_send_to_match_negotiation_as_captain(): void
    {
        $u = test_create_user('Captain', 'capt@test.com');
        test_create_team('Cap Team', 'Madrid', (int) $u['id']);
        $roomId = $this->chat->createRoom('Capitanes', 'match_negotiation');

        $result = $this->chat->send($roomId, (int) $u['id'], 'Busco rival');
        $this->assertArrayHasKey('ok', $result);
    }

    public function test_send_to_match_negotiation_as_regular_player(): void
    {
        $u = test_create_user('Player', 'plyr@test.com');
        $roomId = $this->chat->createRoom('Capitanes', 'match_negotiation');

        $result = $this->chat->send($roomId, (int) $u['id'], 'Hola');
        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('No tienes acceso', $result['error']);
    }

    public function test_send_to_match_negotiation_as_admin(): void
    {
        $u = test_create_user('Admin', 'adm@test.com', 'password', 'admin');
        $roomId = $this->chat->createRoom('Cap Admin', 'match_negotiation');

        $result = $this->chat->send($roomId, (int) $u['id'], 'Admin msg', true);
        $this->assertArrayHasKey('ok', $result);
    }

    // ===== messages() =====
    public function test_messages_ordered_by_desc(): void
    {
        $u = test_create_user('Msg User', 'msg@test.com');
        $roomId = $this->chat->createRoom('Msg Room');

        $this->chat->send($roomId, (int) $u['id'], 'First');
        $this->chat->send($roomId, (int) $u['id'], 'Second');

        $messages = $this->chat->messages($roomId);
        $this->assertCount(2, $messages);
        $this->assertSame('Second', $messages[0]['body']);
        $this->assertSame('First', $messages[1]['body']);
    }

    public function test_messages_empty_room(): void
    {
        $roomId = $this->chat->createRoom('Empty Room');
        $messages = $this->chat->messages($roomId);
        $this->assertEmpty($messages);
    }

    public function test_messages_respects_limit(): void
    {
        $u = test_create_user();
        $roomId = $this->chat->createRoom('Limit Room');
        for ($i = 0; $i < 10; $i++) {
            $this->chat->send($roomId, (int) $u['id'], "Msg $i");
        }
        $messages = $this->chat->messages($roomId, 3);
        $this->assertCount(3, $messages);
    }

    // ===== rooms() =====
    public function test_rooms_without_user(): void
    {
        $this->chat->createRoom('Room 1');
        $this->chat->createRoom('Room 2');
        $rooms = $this->chat->rooms();
        $this->assertCount(2, $rooms);
    }

    public function test_rooms_filters_for_user(): void
    {
        $this->chat->createRoom('General', 'general');
        $this->chat->createRoom('Capitanes', 'match_negotiation');

        $u = test_create_user('Player', 'p@t.com');
        $rooms = $this->chat->rooms((int) $u['id'], false);
        // Regular player cannot access match_negotiation room
        $this->assertCount(1, $rooms);
        $this->assertSame('general', $rooms[0]['type']);
    }

    public function test_rooms_admin_sees_all(): void
    {
        $this->chat->createRoom('General', 'general');
        $this->chat->createRoom('Capitanes', 'match_negotiation');

        $u = test_create_user('Admin', 'admin@test.com', 'pwd', 'admin');
        $rooms = $this->chat->rooms((int) $u['id'], true);
        $this->assertCount(2, $rooms);
    }

    public function test_rooms_captain_sees_negotiation(): void
    {
        $this->chat->createRoom('General', 'general');
        $this->chat->createRoom('Capitanes', 'match_negotiation');

        $u = test_create_user('Cap', 'cap@test.com');
        test_create_team('Cap Team', 'Madrid', (int) $u['id']);

        $rooms = $this->chat->rooms((int) $u['id'], false);
        $this->assertCount(2, $rooms);
    }

    // ===== canAccessRoom() =====
    public function test_can_access_admin_always_true(): void
    {
        $this->assertTrue($this->chat->canAccessRoom(
            ['type' => 'match_negotiation'], 1, true
        ));
        $this->assertTrue($this->chat->canAccessRoom(
            ['type' => 'private'], 1, true
        ));
    }

    public function test_can_access_general_room(): void
    {
        $this->assertTrue($this->chat->canAccessRoom(
            ['type' => 'general'], 1, false
        ));
    }

    public function test_can_access_match_negotiation_as_captain(): void
    {
        $u = test_create_user('Cap2', 'cap2@test.com');
        test_create_team('Cap2 Team', 'Madrid', (int) $u['id']);
        $this->assertTrue($this->chat->canAccessRoom(
            ['type' => 'match_negotiation'], (int) $u['id'], false
        ));
    }

    public function test_can_access_match_negotiation_as_regular(): void
    {
        $u = test_create_user('Regular');
        $this->assertFalse($this->chat->canAccessRoom(
            ['type' => 'match_negotiation'], (int) $u['id'], false
        ));
    }
}

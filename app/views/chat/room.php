<main class="fp-fade fp-page fp-chat-container">
    <p class="fp-eyebrow"><?= $room['type'] === 'match_negotiation' ? 'Negociación de partido' : 'Chat de equipo' ?></p>
    <div class="fp-page-head">
        <h1 class="fp-h1"><?= e($room['name']) ?></h1>
        <?php $this->partial('back-button', ['href' => $room['type'] === 'match_negotiation' ? url('match-request/show/' . (int) $room['match_request_id']) : url('teams')]); ?>
    </div>

    <div class="fp-glass fp-panel fp-chat-panel">
        <div id="fp-chat-feed" class="fp-chat-feed">
            <?php if (empty($messages)): ?>
                <?php $this->partial('empty-state', ['icon' => 'bi-chat-dots', 'title' => 'Sin mensajes', 'description' => 'Sé el primero en escribir.']); ?>
            <?php else: ?>
                <?php foreach ($messages as $m): $own = (int) $m['user_id'] === (int) current_user()['id']; ?>
                    <div class="fp-msg <?= $own ? 'own' : '' ?>">
                        <div class="fp-msg-meta"><strong><?= e($m['user_name']) ?></strong><span><?= e(date('d/m H:i', strtotime($m['created_at']))) ?></span></div>
                        <div class="fp-msg-body"><?= e($m['body']) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <form method="post" action="<?= url('chat/send/' . (int) $room['id']) ?>" class="fp-chat-form">
            <?= csrf_field() ?>
            <input name="body" class="fp-input" placeholder="Escribe un mensaje" maxlength="800" required autofocus>
            <button class="fp-btn fp-btn-primary"><i class="bi bi-send"></i><span>Enviar</span></button>
        </form>
    </div>
</main>
<script>
(function () {
    var feed = document.getElementById('fp-chat-feed');
    if (feed) feed.scrollTop = feed.scrollHeight;
})();
</script>

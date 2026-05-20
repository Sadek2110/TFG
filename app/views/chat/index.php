<main class="fp-fade fp-page" style="max-width:880px;">
    <p class="fp-eyebrow">Conecta</p>
    <h1 class="fp-h1">Chat</h1>
    <p style="color:var(--fp-fg-muted);font-size:14px;margin-top:6px;">Coordina partidos, busca rivales y habla con la comunidad.</p>

    <div style="display:flex;flex-direction:column;gap:12px;margin-top:24px;">
        <?php foreach ($rooms as $r): ?>
            <a href="<?= url('chat/room/' . (int) $r['id']) ?>" class="fp-glass fp-card-link" style="border-radius:14px;padding:18px;display:flex;gap:14px;align-items:center;text-decoration:none;color:var(--fp-fg);">
                <span style="width:44px;height:44px;border-radius:9999px;background:rgba(22,163,74,.20);display:inline-flex;align-items:center;justify-content:center;font-size:20px;">
                    <i class="bi <?= $r['type'] === 'match_negotiation' ? 'bi-people' : 'bi-chat-dots' ?>"></i>
                </span>
                <div style="flex:1;min-width:0;">
                    <div style="font-weight:700;font-size:15px;"><?= e($r['name']) ?></div>
                    <div style="font-size:12px;color:var(--fp-fg-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= e($r['last_body'] ?? 'Sin mensajes todavía.') ?></div>
                </div>
                <div style="font-size:11px;color:var(--fp-fg-subtle);text-align:right;">
                    <div><?= (int) ($r['msg_count'] ?? 0) ?> msgs</div>
                    <?php if (!empty($r['last_at'])): ?><div><?= e(date('d/m H:i', strtotime($r['last_at']))) ?></div><?php endif; ?>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</main>

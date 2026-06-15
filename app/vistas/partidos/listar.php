<section>
    <div class="barra-acciones">
        <h1>Partidos</h1>
        <?php if (Sesion::autenticado()): ?>
            <a class="boton boton--principal" href="<?= e(url('/partidos/crear')) ?>">+ Nuevo partido</a>
        <?php endif; ?>
    </div>

    <?php if (empty($partidos)): ?>
        <div class="estado-vacio">Aún no se ha programado ningún partido.</div>
    <?php else: ?>
        <table class="tabla">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Local</th>
                    <th>Visitante</th>
                    <th>Resultado</th>
                    <th>Estado</th>
                    <th>Liga</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($partidos as $p): ?>
                    <tr>
                        <td><?= e(formatear_fecha($p['fecha_partido'])) ?></td>
                        <td><?= e($p['nombre_local']) ?></td>
                        <td><?= e($p['nombre_visitante']) ?></td>
                        <td>
                            <?php if ($p['estado'] === 'finalizado'): ?>
                                <a href="<?= e(url('/partidos/' . $p['id'])) ?>">
                                    <?= (int) $p['goles_local'] ?> - <?= (int) $p['goles_visitante'] ?>
                                </a>
                            <?php else: ?>
                                <a href="<?= e(url('/partidos/' . $p['id'])) ?>">Ver</a>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="etiqueta <?= $p['estado'] === 'finalizado' ? 'etiqueta--exito' : '' ?>">
                                <?= e(ucfirst($p['estado'])) ?>
                            </span>
                        </td>
                        <td><?= e($p['nombre_liga'] ?? '—') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

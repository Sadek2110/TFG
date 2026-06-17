<section>
    <div class="barra-acciones">
        <h1>Partidos</h1>
        <?php if ($puedeSolicitar): ?>
            <a class="boton boton--principal boton--destacado" href="<?= e(url('/partidos/crear')) ?>">Solicitar partido</a>
        <?php endif; ?>
    </div>

    <div class="calendario-partidos">
        <article class="calendario">
            <div class="calendario__cabecera">
                <a class="boton boton--pequeno boton--secundario" href="<?= e(url('/partidos?fecha=' . $calendario['anterior'])) ?>" aria-label="Mes anterior">‹</a>
                <h2><?= e($calendario['titulo']) ?></h2>
                <a class="boton boton--pequeno boton--secundario" href="<?= e(url('/partidos?fecha=' . $calendario['siguiente'])) ?>" aria-label="Mes siguiente">›</a>
            </div>
            <div class="calendario__leyenda">
                <span><i class="calendario__punto calendario__punto--hoy"></i>Hoy</span>
                <span><i class="calendario__punto calendario__punto--partido"></i>Partido</span>
                <span><i class="calendario__punto calendario__punto--jugado"></i>Jugado</span>
            </div>
            <div class="calendario__grid" role="grid" aria-label="Calendario de partidos">
                <?php foreach (['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'] as $diaSemana): ?>
                    <span class="calendario__semana"><?= e($diaSemana) ?></span>
                <?php endforeach; ?>
                <?php foreach ($calendario['semanas'] as $semana): ?>
                    <?php foreach ($semana as $dia): ?>
                        <?php
                        $clases = ['calendario__dia'];
                        if (!$dia['mes_actual']) { $clases[] = 'calendario__dia--fuera'; }
                        if ($dia['hoy']) { $clases[] = 'calendario__dia--hoy'; }
                        if ($dia['seleccionado']) { $clases[] = 'calendario__dia--seleccionado'; }
                        if ($dia['tiene_partido']) { $clases[] = 'calendario__dia--partido'; }
                        if ($dia['jugado']) { $clases[] = 'calendario__dia--jugado'; }
                        ?>
                        <a class="<?= e(implode(' ', $clases)) ?>" href="<?= e(url('/partidos?fecha=' . $dia['fecha'])) ?>">
                            <span class="calendario__numero"><?= e($dia['dia']) ?></span>
                            <?php if ($dia['total'] > 0): ?>
                                <small class="calendario__contador"><?= (int) $dia['total'] ?></small>
                                <span class="calendario__evento">
                                    <strong><?= e($dia['hora']) ?></strong>
                                    <?= e($dia['resumen']) ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        </article>

        <aside class="dia-partidos">
            <span class="etiqueta">Día seleccionado</span>
            <h2><?= e(formatear_fecha($fechaSeleccionada, 'd/m/Y')) ?></h2>
            <?php if (empty($partidosDia)): ?>
                <p class="campo__ayuda">No hay partidos programados para este día.</p>
            <?php else: ?>
                <ul class="dia-partidos__lista">
                    <?php foreach ($partidosDia as $partido): ?>
                        <li>
                            <a href="<?= e(url('/partidos/' . $partido['id'])) ?>">
                                <strong><?= e($partido['nombre_local']) ?> vs <?= e($partido['nombre_visitante']) ?></strong>
                                <span><?= e(formatear_fecha($partido['fecha_partido'], 'H:i')) ?> · <?= e($partido['nombre_campo'] ?? 'Campo pendiente') ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </aside>
    </div>

    <?php if (empty($partidos)): ?>
        <div class="estado-vacio">Aún no se ha programado ningún partido.</div>
    <?php else: ?>
        <div class="tabla-envoltura"><table class="tabla">
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
                    <tr class="fila--clicable" data-tarjeta-url="<?= e(url('/partidos/' . $p['id'])) ?>">
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
        </table></div>
    <?php endif; ?>
</section>

<section>
    <div class="barra-acciones">
        <div>
            <h1><?= e($liga['nombre']) ?></h1>
            <p class="campo__ayuda">Temporada <?= e($liga['temporada']) ?></p>
        </div>
    </div>

    <?php if (!empty($liga['descripcion'])): ?>
        <p><?= nl2br(e($liga['descripcion'])) ?></p>
    <?php endif; ?>

    <h2>Clasificación</h2>
    <?php if (empty($clasificacion)): ?>
        <div class="estado-vacio">Inscribe equipos en esta liga para ver la clasificación.</div>
    <?php else: ?>
        <table class="tabla">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Equipo</th>
                    <th>PJ</th>
                    <th>G</th>
                    <th>E</th>
                    <th>P</th>
                    <th>GF</th>
                    <th>GC</th>
                    <th>DG</th>
                    <th>Pts</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clasificacion as $i => $fila): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><a href="<?= e(url('/equipos/' . $fila['id'])) ?>"><?= e($fila['nombre']) ?></a></td>
                        <td><?= (int) $fila['jugados'] ?></td>
                        <td><?= (int) $fila['ganados'] ?></td>
                        <td><?= (int) $fila['empatados'] ?></td>
                        <td><?= (int) $fila['perdidos'] ?></td>
                        <td><?= (int) $fila['goles_favor'] ?></td>
                        <td><?= (int) $fila['goles_contra'] ?></td>
                        <td><?= ((int) $fila['goles_favor']) - ((int) $fila['goles_contra']) ?></td>
                        <td><strong><?= (int) $fila['puntos'] ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php if ($puedeInscribir && !empty($equiposLibres)): ?>
        <h2>Inscribir un equipo</h2>
        <form method="post" action="<?= e(url('/ligas/' . $liga['id'] . '/inscribir')) ?>" class="formulario">
            <?= Csrf::campo() ?>
            <div class="campo">
                <label for="id_equipo">Equipo</label>
                <select id="id_equipo" name="id_equipo" required>
                    <option value="">-- Selecciona --</option>
                    <?php foreach ($equiposLibres as $eq): ?>
                        <option value="<?= (int) $eq['id'] ?>"><?= e($eq['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="formulario__acciones">
                <button type="submit" class="boton boton--principal">Inscribir</button>
            </div>
        </form>
    <?php endif; ?>

    <h2>Partidos</h2>
    <?php if (empty($partidos)): ?>
        <div class="estado-vacio">Aún no hay partidos en esta liga.</div>
    <?php else: ?>
        <table class="tabla">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Local</th>
                    <th>Visitante</th>
                    <th>Resultado</th>
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
                                    <?= (int) $p['goles_local'] ?>-<?= (int) $p['goles_visitante'] ?>
                                </a>
                            <?php else: ?>
                                <a href="<?= e(url('/partidos/' . $p['id'])) ?>">Programado</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

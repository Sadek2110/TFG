<section>
    <div class="barra-acciones">
        <div>
            <h1><?= e($equipo['nombre']) ?></h1>
            <?php if (!empty($equipo['ciudad'])): ?>
                <p class="campo__ayuda"><?= e($equipo['ciudad']) ?></p>
            <?php endif; ?>
        </div>
        <?php if ($puedeGestionar): ?>
            <div class="formulario__acciones">
                <a class="boton boton--secundario" href="<?= e(url('/equipos/' . $equipo['id'] . '/editar')) ?>">Editar</a>
                <form method="post" action="<?= e(url('/equipos/' . $equipo['id'] . '/eliminar')) ?>"
                      data-confirmar="¿Seguro que quieres eliminar este equipo?">
                    <?= Csrf::campo() ?>
                    <button type="submit" class="boton boton--peligro">Eliminar</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($equipo['descripcion'])): ?>
        <p><?= nl2br(e($equipo['descripcion'])) ?></p>
    <?php endif; ?>

    <p>
        <strong>Capitán:</strong> <?= e($equipo['nombre_capitan']) ?>
    </p>

    <h2>Miembros (<?= count($miembros) ?>)</h2>
    <?php if (empty($miembros)): ?>
        <div class="estado-vacio">Este equipo todavía no tiene miembros.</div>
    <?php else: ?>
        <!-- Filtrado en vivo con delegación de eventos: public/js/detalle-equipo.js -->
        <div class="tabla-miembros" data-tabla-miembros>
        <div class="tabla-miembros__barra">
            <label class="tabla-miembros__buscar">
                <span class="campo__ayuda">Buscar miembro</span>
                <input type="search" data-buscar-miembro placeholder="Nombre del jugador…" autocomplete="off">
            </label>
            <span class="etiqueta" data-contador-visibles><?= count($miembros) ?> miembros</span>
        </div>
        <div class="tabla-envoltura"><table class="tabla">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Dorsal</th>
                    <th>Posición</th>
                    <?php if ($puedeGestionar): ?><th>Acciones</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($miembros as $miembro): ?>
                    <tr data-nombre="<?= e($miembro['nombre']) ?>">
                        <td><?= e($miembro['nombre']) ?></td>
                        <td><?= $miembro['dorsal'] !== null ? (int) $miembro['dorsal'] : '—' ?></td>
                        <td><?= e($miembro['posicion'] ?? '') ?: '—' ?></td>
                        <?php if ($puedeGestionar): ?>
                            <td>
                                <?php if ((int) $miembro['id_usuario'] === (int) $equipo['id_capitan']): ?>
                                    <span class="etiqueta">Capitán</span>
                                <?php else: ?>
                                    <form method="post"
                                          action="<?= e(url('/equipos/' . $equipo['id'] . '/quitar-miembro')) ?>"
                                          data-confirmar="¿Quitar a este miembro del equipo?">
                                        <?= Csrf::campo() ?>
                                        <input type="hidden" name="id_usuario" value="<?= (int) $miembro['id_usuario'] ?>">
                                        <button type="submit" class="boton boton--enlace">Quitar</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table></div>
        </div><!-- /.tabla-miembros -->
    <?php endif; ?>

    <?php if ($puedeGestionar): ?>
        <h2>Añadir miembro</h2>
        <form method="post" action="<?= e(url('/equipos/' . $equipo['id'] . '/anadir-miembro')) ?>" class="formulario formulario--ancho" novalidate data-validar>
            <?= Csrf::campo() ?>
            <div class="campo">
                <label for="email">Correo del jugador</label>
                <input type="email" id="email" name="email" required maxlength="120"
                       aria-invalid="<?= isset($errores['email']) ? 'true' : 'false' ?>">
                <span class="campo__ayuda">El jugador debe tener cuenta en FastPlay.</span>
                <?php if (isset($errores['email'])): ?>
                    <span class="campo__error"><?= e($errores['email']) ?></span>
                <?php endif; ?>
            </div>
            <div class="campo">
                <label for="dorsal">Dorsal (1-99)</label>
                <input type="number" id="dorsal" name="dorsal" min="1" max="99"
                       aria-invalid="<?= isset($errores['dorsal']) ? 'true' : 'false' ?>">
                <?php if (isset($errores['dorsal'])): ?>
                    <span class="campo__error"><?= e($errores['dorsal']) ?></span>
                <?php endif; ?>
            </div>
            <div class="campo">
                <label for="posicion">Posición</label>
                <input type="text" id="posicion" name="posicion" maxlength="40"
                       placeholder="Portero, defensa, medio, delantero...">
            </div>
            <div class="formulario__acciones">
                <button type="submit" class="boton boton--principal">Añadir miembro</button>
            </div>
        </form>
    <?php endif; ?>
</section>

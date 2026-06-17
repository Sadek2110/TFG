<section class="equipo-detalle">
    <div class="barra-acciones">
        <div>
            <h1><?= e($equipo['nombre']) ?></h1>
            <?php if (!empty($equipo['ciudad'])): ?>
                <p class="campo__ayuda"><?= e($equipo['ciudad']) ?></p>
            <?php endif; ?>
        </div>
        <div class="formulario__acciones">
            <?php if ($puedeGestionar): ?>
                <a class="boton boton--secundario" href="<?= e(url('/equipos/' . $equipo['id'] . '/editar')) ?>">Editar equipo</a>
                <form method="post" action="<?= e(url('/equipos/' . $equipo['id'] . '/eliminar')) ?>"
                      data-confirmar="¿Seguro que quieres eliminar este equipo?">
                    <?= Csrf::campo() ?>
                    <button type="submit" class="boton boton--peligro">Eliminar</button>
                </form>
            <?php elseif ($puedeUnirse): ?>
                <form method="post" action="<?= e(url('/equipos/' . $equipo['id'] . '/unirse')) ?>">
                    <?= Csrf::campo() ?>
                    <button type="submit" class="boton boton--principal">Unirme al equipo</button>
                </form>
            <?php elseif (!Sesion::autenticado()): ?>
                <a class="boton boton--principal" href="<?= e(url('/iniciar-sesion')) ?>">Inicia sesión para unirte</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="equipo-resumen">
        <article class="tarjeta">
            <h2>Información</h2>
            <?php if (!empty($equipo['descripcion'])): ?>
                <p><?= nl2br(e($equipo['descripcion'])) ?></p>
            <?php else: ?>
                <p class="campo__ayuda">Este equipo todavía no tiene descripción.</p>
            <?php endif; ?>
            <p><strong>Capitán:</strong> <?= e($equipo['nombre_capitan']) ?></p>
            <?php if (!$puedeUnirse && !$esIntegrante && !empty($equipoUsuario)): ?>
                <p class="mensaje mensaje--aviso">Ya perteneces a <?= e($equipoUsuario['nombre']) ?>, por eso no puedes unirte a otro equipo.</p>
            <?php endif; ?>
        </article>

        <?php if ($puedeGestionar): ?>
            <article class="tarjeta">
                <h2>Invitar jugador</h2>
                <form method="post" action="<?= e(url('/equipos/' . $equipo['id'] . '/invitar')) ?>" class="formulario-compacto" novalidate data-validar>
                    <?= Csrf::campo() ?>
                    <div class="campo">
                        <label for="email">Correo del jugador</label>
                        <input type="email" id="email" name="email" required maxlength="120"
                               aria-invalid="<?= isset($errores['email']) ? 'true' : 'false' ?>">
                        <span class="campo__ayuda">El jugador debe tener cuenta y no pertenecer a otro equipo.</span>
                        <?php if (isset($errores['email'])): ?>
                            <span class="campo__error"><?= e($errores['email']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="equipo-form-grid">
                        <div class="campo">
                            <label for="dorsal">Dorsal</label>
                            <input type="number" id="dorsal" name="dorsal" min="1" max="99"
                                   aria-invalid="<?= isset($errores['dorsal']) ? 'true' : 'false' ?>">
                        </div>
                        <div class="campo">
                            <label for="posicion">Posición</label>
                            <input type="text" id="posicion" name="posicion" maxlength="40"
                                   placeholder="Portero, defensa...">
                        </div>
                    </div>
                    <button type="submit" class="boton boton--principal">Enviar invitación</button>
                </form>
            </article>
        <?php endif; ?>
    </div>

    <h2>Miembros (<?= count($miembros) ?>)</h2>
    <?php if (empty($miembros)): ?>
        <div class="estado-vacio">Este equipo todavía no tiene miembros.</div>
    <?php else: ?>
        <div class="tabla-miembros" data-tabla-miembros>
            <div class="tabla-miembros__barra">
                <label class="tabla-miembros__buscar">
                    <span class="campo__ayuda">Buscar miembro</span>
                    <input type="search" data-buscar-miembro placeholder="Nombre del jugador..." autocomplete="off">
                </label>
                <span class="etiqueta" data-contador-visibles><?= count($miembros) ?> miembros</span>
            </div>
            <div class="tabla-envoltura">
                <table class="tabla tabla--gestion">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Dorsal</th>
                            <th>Posición</th>
                            <th>Titular</th>
                            <?php if ($puedeGestionar): ?><th>Acciones</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($miembros as $miembro): ?>
                            <?php $formId = 'miembro-' . (int) $miembro['id_usuario']; ?>
                            <tr data-nombre="<?= e($miembro['nombre']) ?>">
                                <td>
                                    <strong><?= e($miembro['nombre']) ?></strong>
                                    <?php if ((int) $miembro['id_usuario'] === (int) $equipo['id_capitan']): ?>
                                        <span class="etiqueta">Capitán</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($puedeGestionar): ?>
                                        <input class="tabla-input" form="<?= e($formId) ?>" type="number" name="dorsal" min="1" max="99"
                                               value="<?= $miembro['dorsal'] !== null ? (int) $miembro['dorsal'] : '' ?>">
                                    <?php else: ?>
                                        <?= $miembro['dorsal'] !== null ? (int) $miembro['dorsal'] : '—' ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($puedeGestionar): ?>
                                        <input class="tabla-input tabla-input--texto" form="<?= e($formId) ?>" type="text" name="posicion" maxlength="40"
                                               value="<?= e($miembro['posicion'] ?? '') ?>">
                                    <?php else: ?>
                                        <?= e($miembro['posicion'] ?? '') ?: '—' ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($puedeGestionar): ?>
                                        <label class="checkbox-linea">
                                            <input form="<?= e($formId) ?>" type="checkbox" name="titular" value="1"
                                                   <?= !empty($miembro['titular']) ? 'checked' : '' ?>>
                                            <span>Titular</span>
                                        </label>
                                    <?php else: ?>
                                        <?php if (!empty($miembro['titular'])): ?>
                                            <span class="etiqueta etiqueta--exito">Titular</span>
                                        <?php else: ?>
                                            <span class="campo__ayuda">Suplente</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <?php if ($puedeGestionar): ?>
                                    <td class="acciones-tabla">
                                        <form id="<?= e($formId) ?>" method="post"
                                              action="<?= e(url('/equipos/' . $equipo['id'] . '/actualizar-miembro')) ?>">
                                            <?= Csrf::campo() ?>
                                            <input type="hidden" name="id_usuario" value="<?= (int) $miembro['id_usuario'] ?>">
                                            <button type="submit" class="boton boton--pequeno boton--secundario">Guardar</button>
                                        </form>
                                        <?php if ((int) $miembro['id_usuario'] !== (int) $equipo['id_capitan']): ?>
                                            <form method="post"
                                                  action="<?= e(url('/equipos/' . $equipo['id'] . '/quitar-miembro')) ?>"
                                                  data-confirmar="¿Quitar a este miembro del equipo?">
                                                <?= Csrf::campo() ?>
                                                <input type="hidden" name="id_usuario" value="<?= (int) $miembro['id_usuario'] ?>">
                                                <button type="submit" class="boton boton--pequeno boton--enlace">Echar</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</section>

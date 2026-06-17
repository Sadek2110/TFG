<section>
    <h1>Panel de administración</h1>
    <p>Resumen rápido del estado del sitio y atajos para crear contenido.</p>

    <ul class="lista-tarjetas">
        <li class="tarjeta">
            <h2>Usuarios</h2>
            <p class="tarjeta__cifra"><?= (int) $estadisticas['usuarios'] ?></p>
        </li>
        <li class="tarjeta">
            <h2>Equipos</h2>
            <p class="tarjeta__cifra"><?= (int) $estadisticas['equipos'] ?></p>
            <div class="tarjeta__pie">
                <a class="boton boton--secundario" href="<?= e(url('/equipos')) ?>">Ver</a>
                <a class="boton boton--principal" href="<?= e(url('/equipos/crear')) ?>">+ Nuevo</a>
            </div>
        </li>
        <li class="tarjeta">
            <h2>Partidos</h2>
            <p class="tarjeta__cifra"><?= (int) $estadisticas['partidos'] ?></p>
            <div class="tarjeta__pie">
                <a class="boton boton--secundario" href="<?= e(url('/partidos')) ?>">Ver</a>
                <a class="boton boton--principal" href="<?= e(url('/partidos/crear')) ?>">+ Nuevo</a>
            </div>
        </li>
        <li class="tarjeta">
            <h2>Campos</h2>
            <p class="tarjeta__cifra"><?= (int) $estadisticas['campos'] ?></p>
            <div class="tarjeta__pie">
                <a class="boton boton--secundario" href="<?= e(url('/campos')) ?>">Ver</a>
                <a class="boton boton--principal" href="<?= e(url('/campos/crear')) ?>">+ Nuevo</a>
            </div>
        </li>
        <li class="tarjeta">
            <h2>Ligas</h2>
            <p class="tarjeta__cifra"><?= (int) $estadisticas['ligas'] ?></p>
            <div class="tarjeta__pie">
                <a class="boton boton--secundario" href="<?= e(url('/ligas')) ?>">Ver</a>
                <a class="boton boton--principal" href="<?= e(url('/ligas/crear')) ?>">+ Nueva</a>
            </div>
        </li>
    </ul>

    <h2>Usuarios registrados</h2>
    <div class="tabla-envoltura"><table class="tabla">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Rol</th>
                <th>Alta</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $u): ?>
                <tr>
                    <td><?= e($u['nombre']) ?></td>
                    <td><?= e($u['email']) ?></td>
                    <td>
                        <span class="etiqueta <?= $u['rol'] === 'administrador' ? 'etiqueta--exito' : '' ?>">
                            <?= e($u['rol']) ?>
                        </span>
                    </td>
                    <td><?= e(formatear_fecha($u['fecha_creacion'], 'd/m/Y')) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table></div>
</section>

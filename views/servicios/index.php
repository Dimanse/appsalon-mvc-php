<?php
    @include_once __DIR__ . '/../templates/barra.php';
?>

<h1 class="nombre-pagina">Servicios</h1>
<p class="descripcion-pagina">Administración de Servicios</p>

<div class="servicios">
    <?php foreach($servicios as $servicio) { ?>
        <li>
            <p>Nombre: <span><?php echo $servicio->nombre; ?></span> </p>
            <p>Precio: <span>$<?php echo $servicio->precio; ?></span> </p>

            <div class="accion">
                <a href="/servicios/actualizar?id=<?php echo $servicio->id; ?>" class="boton-actualizar">Actualizar</a>

                <form action="/servicios/eliminar" method="POST">
                    <input type="hidden" name="id" value="<?php echo $servicio->id; ?>">
                    <input type="submit" value="Borrar" class="boton-borrar">
                </form>
            </div>
        </li>

    <?php } ?>
</div>
<?php

namespace Model;

class Cita extends ActiveRecord {
    // Base de datos
    protected static $tabla = 'citas';
    protected static $columnasDB = ['id', 'fecha', 'hora_id', 'usuarioId'];
    
    public $id;
    public $fecha;
    public $hora_id;
    public $usuarioId;

    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->fecha = $args['fecha'] ?? '';
        $this->hora_id = $args['hora_id'] ?? '';
        $this->usuarioId = $args['usuarioId'] ?? '';
    }

}
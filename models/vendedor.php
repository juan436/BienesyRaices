<?php

namespace Model;

class Vendedor extends ActiveRecord
{
    protected static $tabla = 'vendedores';
    protected static $columnasDB = ['id','nombre','apellido','telefono','imagen'];

    public $id;
    public $nombre;
    public $apellido;
    public $telefono;
    public $imagen;

    public function __construct($args = [])
    {
     $this-> id= $args['id'] ?? null;
     $this-> nombre= $args['nombre'] ?? '';
     $this-> apellido= $args['apellido'] ?? '';
     $this-> telefono= $args['telefono'] ?? '';
     $this-> imagen= $args['imagen'] ?? ''; 
    }

    public function validar() {
      
        if(!$this->nombre) {
           self::$errores[] = "Debes añadir un nombre";
       }
  
       if(!$this->apellido) {
           self::$errores[] = 'Debes añadir el apellido';
       }
  
       if(!$this->telefono) {
           self::$errores[] = 'El telefono es obligatorio';
       }

       if(!preg_match('/[0-9]{11}/',$this->telefono)){

        self::$errores[] = "Formato no valido";

       }

       if(!$this->imagen ) {
        self::$errores[] = 'La Imagen del vendedor es obligatoria';
       }
  
        
       return self::$errores;
     }
}

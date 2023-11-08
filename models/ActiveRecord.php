<?php

namespace Model;

class ActiveRecord 
{
    //Base de Datos
   protected static $db;
   protected static $columnasDB = [];
   protected static $tabla = '';
   protected static $errores =[];

 
   //Definir la conexion a la base de datos

   public static function setDB($database){
      self::$db = $database;
   }

   public function guardar(){

         if(!is_null($this->id)){
            //actualizar
            $this->actualizar();

         } else{
            //creando un nuevo registro
            $this->crear();
         }
   }

   public function crear() {

      //Sanitizar los datos
      $atributos = $this->sanitizarAtributos(); //ATRIBUTOS YA SANITIZADOS
    
       $query = " INSERT INTO " . static::$tabla . " (";
       $query .= join(', ', array_keys($atributos) );
       $query .= ") VALUES (' " ;
       $query .= join("', '", array_values($atributos) );
       $query .= " ')";

       
      // debuguear($query);
      
      $resultado = self::$db->query($query);
      
      if($resultado) {
         // Redireccionar al usuario.
         header('Location: /admin?resultado=1');
     }

   }

   public function actualizar(){
      //Sanitizar los datos
      $atributos = $this->sanitizarAtributos(); //ATRIBUTOS YA SANITIZADOS
      
      $valores = [];
      foreach ($atributos as $key => $value) {
         $valores[] = "{$key}='{$value}'";
      }
      $query = "UPDATE " . static::$tabla . " SET ";
      $query .= join(', ', $valores);
      $query .= " WHERE id = '" . self::$db->escape_string($this->id) . "' ";
      $query .= " LIMIT 1";

      $resultado = self::$db->query($query);
      //Insertar en la database

      if($resultado) {
          // Redireccionar al usuario.
          header('Location: /admin?resultado=2');
      }
   }
   //Eliminar un registro

   public function eliminar(){
      $query = "DELETE FROM " . static::$tabla . " WHERE id = " . self::$db->escape_string($this->id) . " LIMIT 1";
      $resultado = self::$db->query($query);

      if($resultado) {
         $this->borrarImagen();
         header('location: /admin?resultado=3');
     }
   }
   //Identificar los atributos de la base de datos
   public function atributos(){
      $atributos =[];

      foreach(static::$columnasDB as $columna){
         if($columna === 'id') continue; // al cumplir la conficion pasa a la siguiente linea de comando
         $atributos[$columna]=$this->$columna;
      }
      return $atributos;
   }

   //Sanitizar
   public function sanitizarAtributos(){
     
      $atributos =$this->atributos();
      $sanitizado = [];

      foreach ($atributos as $key => $value) {
         $sanitizado[$key] = self::$db->escape_string($value);
      }
   
      return $sanitizado;
    
   }
   //Subida de archivos
   public function setImagen($imagen){
      
      if(!is_null($this->id)){
          //Elimina imagen previa
        $this->borrarImagen();
      }
      //Asignar al atributos imagen el nombre de la imagen
      if ($imagen) {
         $this->imagen = $imagen;
      }
   }

   //Elimina el archivo
   public function borrarImagen(){
      
      $existeArchivo = file_exists(CARPETA_IMAGENES . $this->imagen);
      if($existeArchivo){
      //Comprobar si existe archivo
      unlink(CARPETA_IMAGENES . $this->imagen);
      }  
   }

   //Validacion
   public static function getErrores(){
      return static::$errores;
   }

   public function validar() {
      static::$errores = [];

     return static::$errores;
   }

   //Lista todas las propiedades  
   public static function all(){
     
    $query = "SELECT * FROM " . static::$tabla;

     $resultado = self::consultarSQL($query);

     return $resultado;

   }

   // Obtiene determinado número de registro

   public static function get($cantidad) {
      $query = "SELECT * FROM " . static::$tabla . " LIMIT " . $cantidad;

      $resultado = self::consultarSQL($query); 
      
      return $resultado;
   }


   //Busca una propiedad por su ID

   public static function find($id) { 
      $query = "SELECT * FROM " . static::$tabla . " WHERE id = ${id}";

      $resultado = self::consultarSQL($query);  
      return array_shift($resultado);
   }

   public static function consultarSQL($query){

      //consultar la base de datos
      $resultado = self::$db->query($query);
      
      //Iterar los resultados
      
      $array = [];
      while($registro = $resultado->fetch_assoc()){
         $array[] = static::crearObjeto($registro);
      }

      //liberar la memoria
      $resultado->free();
      //retornar los resultados
      return $array;

   }

   public static function crearObjeto($registro){
      
      $objeto = new static;

      foreach ($registro as $key => $value) {
         if(property_exists($objeto , $key)){
            $objeto->$key = $value;

         }
      }
      return $objeto;
   }

   //Sanitizar el objeto en memoria con los cambios realizados por el usuario
   public function sincronizar( $args =[]) {
      
      foreach ($args as $key => $value) {
      
         if(property_exists($this,$key) && !is_null($value)) {
            $this->$key = $value;
         }
      }
   }
}

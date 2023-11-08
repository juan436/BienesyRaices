<?php 

namespace Controllers;

use MVC\Router;
use Model\Vendedor;
use Intervention\Image\ImageManagerStatic as Image;
class VendedorController {

    public static function crear(Router $router)
    {
        $errores = Vendedor::getErrores();

        $vendedor = new Vendedor;

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
    
            $vendedor = new Vendedor($_POST['vendedor']);
    
             /** SUBIDA DE Imagen */
    
            // Generar un nombre único
            $nombreImagen = md5( uniqid( rand(), true ) ) . ".jpg";
    
            //setea la imagen
    
            if($_FILES['vendedor']['tmp_name']['imagen']) {
            //Realiza un resize a la imagen con intervention
            
            $image = Image::make($_FILES['vendedor']['tmp_name']['imagen']) ->fit(800,600);
    
            $vendedor->setImagen($nombreImagen);
    
            }
    
             //Validar
            $errores = $vendedor->validar();
          
            if(empty($errores)) {   // Revisar que el array de errores este vacio
                //Crear la carpeta apra subir imagenes
    
                if (!is_dir(CARPETA_IMAGENESV)) {
                    
                    mkdir(CARPETA_IMAGENESV);
                }
                
                //Guarda la imagen en el servidor
                $image->save(CARPETA_IMAGENESV . $nombreImagen);
    
                $resultado = $vendedor->guardar();
    
            }
        
        }
    

        $router->render('vendedores/crear', [
            'errores' => $errores,
            'vendedor' => $vendedor

        ]);
    }

    public static function actualizar(Router $router)
    {
        $id = validarORedireccionar('/admin');

        $errores = Vendedor::getErrores();

        //Obtener datos del vendedor
        $vendedor = Vendedor::find($id);

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
    
            $args = $_POST['vendedor'];
    
            $vendedor->sincronizar($args);
            
            $errores = $vendedor->validar();
    
        
            //Subida de archivos
    
            // Generar un nombre único       
            $nombreImagen = md5( uniqid( rand(), true ) ) . ".jpg";
            
            if($_FILES['vendedor']['tmp_name']['imagen']) {
                //Realiza un resize a la imagen con intervention
                
                $image = Image::make($_FILES['vendedor']['tmp_name']['imagen']) ->fit(800,600);
    
                $vendedor->setImagen($nombreImagen); 
    
            }
    
            if(empty($errores)) {
                // Almacenar la imagen
                if($_FILES['vendedor']['tmp_name']['imagen']) {
                    
                    $image->save(CARPETA_IMAGENESV . $nombreImagen);
                }
    
                $vendedor->guardar();       
            }
        
        
        }

        
        $router->render('vendedores/actualizar',[
            'errores'=>$errores,
            'vendedor'=>$vendedor
        ]);

        
    }
    public static function eliminar(Router $router){

        if($_SERVER['REQUEST_METHOD'] === 'POST') {

            $id = $_POST['id'];
            $id = filter_var($id, FILTER_VALIDATE_INT);
    
            if($id) {
    
                $tipo = $_POST['tipo'];
                
                if(validarTipoContenido($tipo)) {
                     // Obtener los datos de la propiedad
                     $vendedor = Vendedor::find($id);
                     $vendedor->eliminar();
                 
                } 
    
            }
    
            
        }

    }

} 
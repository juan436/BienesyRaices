<?php

namespace Controllers;
use MVC\Router;
use Model\Propiedad;
use Model\Vendedor;
use Intervention\Image\ImageManagerStatic as Image;

class PropiedadController 
{
    
    public static function index(Router $router)
    {
    
        $propiedades = Propiedad::all();
        $vendedores = Vendedor::all();
        // Muestra mensaje condicional
        $resultado = $_GET['resultado'] ?? null;

        //RENDER ES PASAR A LA VISTA
        $router->render('Propiedades/admin', [
            'propiedades' => $propiedades,
            'resultado' => $resultado,
            'vendedores' =>$vendedores
        ]);
    }
    public static function crear(Router $router){

        $propiedad = new Propiedad();
        $vendedores = Vendedor::all();

        // Arreglo con mensajes de errores
        $errores = Propiedad::getErrores();

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            //Crea una nueva instancia
            $propiedad = new Propiedad($_POST['propiedad']);
        

            /** SUBIDA DE ARCHIVOS */
    
            // Generar un nombre único
            $nombreImagen = md5( uniqid( rand(), true ) ) . ".jpg";
    
            //setea la imagen
    
            if($_FILES['propiedad']['tmp_name']['imagen']) {
            //Realiza un resize a la imagen con intervention
            
            $image = Image::make($_FILES['propiedad']['tmp_name']['imagen']) ->fit(800,600);
    
            $propiedad->setImagen($nombreImagen);
    
            }
    

            //Validar
            $errores = $propiedad->validar(); 
    
        
            if(empty($errores)) {   // Revisar que el array de errores este vacio
    
                //Crear la carpeta apra subir imagenes
    
                if (!is_dir(CARPETA_IMAGENES)) {
                    
                    mkdir(CARPETA_IMAGENES);
                }
                
                //Guarda la imagen en el servidor
                $image->save(CARPETA_IMAGENES . $nombreImagen);
    
                //Guardar en la base de datos
                $resultado = $propiedad->guardar();
            }
        }

        $router->render('Propiedades/crear', [
            'propiedad' => $propiedad,
            'vendedores' => $vendedores,
            'errores' => $errores
        ]); 
    }
    public static function actualizar(Router $router){
        
        $id = validarORedireccionar('/admin');
    
        $propiedad = Propiedad::find($id);
        $vendedores = Vendedor::all();

        $errores = Propiedad::getErrores();

        if($_SERVER['REQUEST_METHOD'] === 'POST') { 

            //aSIGNAR ATRIBUTOS
            $args = $_POST['propiedad'];

            $propiedad->sincronizar($args);

            //Validacion
            $errores = $propiedad->validar();
            
            //Subida de archivos

            // Generar un nombre único       
            $nombreImagen = md5( uniqid( rand(), true ) ) . ".jpg";
            
            if($_FILES['propiedad']['tmp_name']['imagen']) {
                //Realiza un resize a la imagen con intervention
                
                $image = Image::make($_FILES['propiedad']['tmp_name']['imagen']) ->fit(800,600);

                $propiedad->setImagen($nombreImagen); 

            }

            if(empty($errores)) {
                // Almacenar la imagen
                if($_FILES['propiedad']['tmp_name']['imagen']) {
                    
                    $image->save(CARPETA_IMAGENES . $nombreImagen);
                }

                $propiedad->guardar();       
            }

        }

        $router->render('/propiedades/actualizar',[
            'propiedad' => $propiedad,
            'errores' => $errores,
            'vendedores' => $vendedores 
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
                     $propiedad = Propiedad::find($id);
                     $propiedad->eliminar();
                 
                } 
    
            }
    
            
        }

    }
}
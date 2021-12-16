<?php
/*CLIENTE(id: int; 
        nombre: string, 
        dni: string, 
        telefono: string, 
        direccion: string, 
        ejecutivo: boolean)
TARJETA(id: int; 
        fecha_alta: datetime; 
        nro_tarjeta: string, 
        fecha_vencimiento: int, 
        tipo_tarjeta: string, 
        id_cliente: int)
ACTIVIDAD(id: int; 
        kms: int, 
        fecha: datetime, 
        tipo_operación: int, 
        id_cliente: int)

Donde el tipo_operación es 1=canje y 2=suma */

//Dar de alta un cliente nuevo al sistema. 
/*Se debe poder crear un nuevo cliente en el sistema indicando todos los datos necesarios y cumpliendo los siguientes requerimientos. Informar los errores correspondientes en caso de no cumplirlos.
    Controlar posibles errores de carga de datos.
    Verificar que un usuario admin esté logueado. 
    Verificar que no exista un cliente con el mismo dni.
    Cuando se agrega un cliente se le deben depositar automáticamente 200 kms en su cuenta.
    Si el cliente es EJECUTIVO, se le debe asociar automáticamente una tarjeta del tipo ejecutiva empresarial. (Suponga que los datos de la tarjeta se obtienen de una función CardHelper->getBussinesCard())
*/

class ClienteController{
    private $model;
    private $view;
    private $modelActividad;
    private $authHelper;
    private $cardHelper;

    function __construct(){
        $this->model = new ClienteModel();
        $this->view = new ClienteView();
        $this->modelActividad = new ActividadModel();
        $this->authHelper = new AuthHelper();
        $this->cardHelper = new CardHelper();
    }

    function altaCliente(){
        $this->authHelper->checkLoggedIn();
        if(isset($_POST['nombre']) & isset($_POST['dni']) & isset($_POST['telefono']) & isset($_POST['direccion']) & isset($_POST['ejecutivo'])){
            if(!empty($_POST['nombre']) & !empty($_POST['dni']) & !empty($_POST['telefono']) & !empty($_POST['direccion']) & !empty($_POST['ejecutivo'])){
                $nombre = $_POST['nombre'];
                $dni = $_POST['dni'];
                $telefono = $_POST['telefono'];
                $direccion = $_POST['direccion'];
                $ejecutivo = $_POST['ejecutivo'];
                $DNIClientes = $this->model->getDNIClientes();
                if(!in_array($dni, $DNIClientes)){
                    $idCliente = $this->model->crearCliente($nombre, $dni, $telefono, $direccion, $ejecutivo);
                    $kms = "200";
                    $this->modelActividad->agregar200km($kms, $idCliente);
                    $this->view->mostrarMensaje("Se agregaron 200km de regalo");
                    $ejecutivo = $this->model->tipoCliente($idCliente);
                    if($ejecutivo = true){
                        $this->cardHelper->getBussinesCard();
                    }
                }else $this->view->mostrarMensaje("Ya existe un cliente con ese DNI");
            }else $this->view->mostrarMensaje("Alguno de los campos esta vacio");
        }else $this->view->mostrarMensaje("Alguno de los campos no esta establecido");
    }
}

class ClienteModel{
    private $db;

    function __construct(){
        $this->db = new PDO('mysql:host=localhost;dbname=final;charset=utf8','root','');
    }

    function getDNIClientes(){
        $sentence = $this->db->prepare("SELECT dni FROM CLIENTE");
        $sentence->execute();
        $DNIClientes = $sentence->fetchAll(PDO::FETCH_OBJ);
        return $DNIClientes; 
    }

    function crearCliente($nombre, $dni, $telefono, $direccion, $ejecutivo){
        $sentence = $this->db->prepare("INSERT INTO CLIENTE(nombre, dni, telefono, direccion, ejecutivo) VALUES(?,?,?,?,?)");
        $sentence->execute(array($nombre, $dni, $telefono, $direccion, $ejecutivo));
        return $db->lastInsertId();
    }

    function tipoCliente($idCliente){
        $sentence = $this->db->prepare("SELECT ejecutivo FROM CLIENTE WHERE id=?");
        $sentence->execute(array($idCliente));
        $ejecutivo = $sentence->fetch(PDO::FETCH_OBJ);
        return $ejecutivo;
    }
    
}

class ActividadModel{
    private $db;

    function __construct(){
        $this->db = new PDO('mysql:host=localhost;dbname=final;charset=utf8','root','');
    }

    function agregar200km($kms, $idCliente){
        $sentence = $this->db->prepare("INSERT INTO CLIENTE(kms, id_cliente) VALUES (200,?)");
        $sentence->execute(array($kms, $idCliente));
    }
}

class AuthHelper{
    function checkLoggedIn(){
        session_start();
        if(!isset($_SESSION["dni"])){
            header("Location: ".BASE_URL."logIn");
        }
    }
}
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

/*3. TRANSFERENCIA RÁPIDA
La estación de servicio quiere ahora brindar un servicio para que sus clientes realicen transferencias de kms entre ellos de manera rápida. Para esto nos presentan el siguiente caso de uso:
Como usuario quiero poder realizar una transferencia rápida a otro usuario indicando sólo su DNI.
Implemente el requerimiento siguiendo el patrón MVC. No es necesario realizar las vistas, solo controlador(es), modelo(s) y las invocaciones a la vista. 
    Se debe verificar que el cliente esté logueado.
    Se debe verificar que el cliente originario tenga fondos suficientes en su cuenta. 
    Se debe verificar que el cliente destinatario exista.
*/

class ClienteController{
    private $model;
    private $view;
    private $modelActividad;
    private $authHelper;
    private $modelTarjeta;

    function __construct(){
        $this->model = new ClienteModel();
        $this->view = new ClienteView();
        $this->modelActividad = new ActividadModel();
        $this->authHelper = new AuthHelper();
        $this->modelTarjeta = new TarjetaModel();
    }

    function transferenciaKm(){
        $this->authHelper->checkLoggedIn();
        if(isset($_POST['idCliente']) & isset($_POST['kms']) & isset($_POST['DNIOtroCliente']) & isset($_POST['fecha']) & isset($_POST['tipoDeOperacion'])){ //le pasa mediante el DNI del otro usuario los kms
            if(!empty($_POST['idCliente']) & !empty($_POST['kms']) & !empty($_POST['DNIOtroCliente']) & !empty($_POST['fecha']) & !empty($_POST['tipoDeOperacion'])){
                $idCliente = $_POST['idCliente'];
                $kms = $_POST['kms'];
                $DNIOtroCliente = $_POST['DNIOtroCliente'];
                $fecha = $_POST['fecha'];
                $tipoDeOperacion = $_POST['tipoDeOperacion'];
                $kmAcumulados = $this->modelActividad->kmAcumulados($idCliente); //reutilizo la funcion del punto 2
                if($kmAcumulados >= $kms){
                    $DNIClientes = $this->model->getDNIClientes(); //reutilizo la funcion del punto 1
                    if(in_array($DNIOtroCliente, $DNIClientes)){
                        $IdOtroCliente = $this->model->getIDCliente($DNIOtroCliente);
                        $this->modelActividad->transferenciaKms($kms, $fecha, $tipoDeOperacion, $idOtroCliente);
                        $this->view->mostrarMensaje("Se hizo una transferencia de $kms kilometros.");
                }else $this->view->mostrarMensaje("NO existe un cliente con ese DNI");
            }else $this->view->mostrarMensaje("Alguno de los campos esta vacio");
        }else $this->view->mostrarMensaje("Alguno de los campos no esta establecido");
    }
}

class ClienteModel{
    private $db;

    function __construct(){
        $this->db = new PDO('mysql:host=localhost;dbname=final;charset=utf8','root','');
    }

    function getIDCliente($DNIOtroCliente){
    $sentence = $this->db->prepare("SELECT id FROM CLIENTE WHERE dni=?");
    $sentence->execute(array($DNIOtroCliente));
    $Id = $sentence->fetch(PDO::FETCH_OBJ);
    return $Id;
}
    
}


class ActividadModel{
    private $db;

    function __construct(){
        $this->db = new PDO('mysql:host=localhost;dbname=final;charset=utf8','root','');
    }

    function transferenciaKms($kms, $fecha, $tipoDeOperacion, $idOtroCliente){
        $sentence = $this->db->prepare("INSERT INTO ACTIVIDAD(kms, fecha, tipo_operación, id_cliente) VALUES(?,?,?,?)");
        $sentence->execute(array($kms, $fecha, $tipoDeOperacion, $idOtroCliente));
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
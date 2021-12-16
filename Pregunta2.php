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

/* RESUMEN DE CUENTA
Implemente el siguiente requerimiento siguiendo el patrón MVC. No es necesario realizar las vistas, solo controlador(es), modelo(s) y las invocaciones a la vista. 
Generar una tabla resumen de cuenta de un cliente determinado
Informar posibles errores
Se debe mostrar una lista detallada de las operaciones del cliente y el saldo actual de km
Se debe informar la lista de tarjetas asociadas
*/

class ClienteController{
    private $model;
    private $view;
    private $modelActividad;
    private $modelTarjeta;

    function __construct(){
        $this->model = new ClienteModel();
        $this->view = new ClienteView();
        $this->modelActividad = new ActividadModel();
        $this->modelTarjeta = new TarjetaModel();
    }

    function resumenCliente(){
        if(isset($_POST['idCliente'])){
            if(!empty($_POST['idCliente'])){
                $idCliente = $_POST['idCliente'];
                $datosCliente = $this->model->getCliente($idCliente);
                $this->view->mostrarNombre($datosCliente->nombre);
                $this->view->mostrarDNI($datosCliente->dni);
                $kmAcumulados = $this->modelActividad->kmAcumulados($idCliente);
                $this->view->mostrarKmAcumulados("Kilometros acumulados $kmAcumulados");
                $detallesActividad = $this->modelActividad->detallesActividad($idCliente);
                $this->view->mostrarDetallesActividad($detallesActividad);
                $tarjetasAsociadas = $this->modelTarjeta->listaTrajetasAsociadas($idCliente);
                $this->view->mostrarTrajetasAsociadas($tarjetasAsociadas);
            }else $this->view->mostrarMensaje("El campo esta vacio");
        }else $this->view->mostrarMensaje("El campo no esta establecido");
    }
    
}

class ClienteModel{
    private $db;

    function __construct(){
        $this->db = new PDO('mysql:host=localhost;dbname=final;charset=utf8','root','');
    }

    function getCliente($idCliente){
        $sentence = $this->db->prepare("SELECT * FROM CLIENTE WHERE id=?");
        $sentence->execute(array($idCliente));
        $datosCliente = $sentence->fetch(PDO::FETCH_OBJ);
        return $datosCliente;
    }
    
}

class ActividadModel{
    private $db;

    function __construct(){
        $this->db = new PDO('mysql:host=localhost;dbname=final;charset=utf8','root','');
    }

    function kmAcumulados($idCliente){
        $sentence = $this->db->prepare("SELECT SUM(kms) FROM ACTIVIDAD WHERE id_cliente=?");
        $sentence->execute(array($idCliente));
        $kmAcumulados = $sentence->fetch(PDO::FETCH_OBJ);
        return $kmAcumulados;
    }
    function detallesActividad($idCliente){
        $sentence = $this->db->prepare("SELECT * FROM ACTIVIDAD WHERE id_cliente=?");
        $sentence->execute(array($idCliente));
        $detallesActividad = $sentence->fetch(PDO::FETCH_OBJ);
        return $detallesActividad;
    }
}

class TarjetasModel{
    private $db;

    function __construct(){
        $this->db = new PDO('mysql:host=localhost;dbname=final;charset=utf8','root','');
    }

    function listaTrajetasAsociadas($idCliente){
        $sentence = $this->db->prepare("SELECT * FROM TARJETA WHERE id_cliente=?");
        $sentence->execute(array($idCliente));
        $tarjetasAsociadas = $sentence->fetch(PDO::FETCH_OBJ);
        return $tarjetasAsociadas;
    }
}
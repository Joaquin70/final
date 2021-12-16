<?php

/*4)
a)Lo que haría sería seguir con el mismo modelo MVC pero agregando una nueva vista (APIView, donde colocaria todos los codigos de estado HTTP) y controlador(APIController), además de un nuevo route.
b) 
("/cliente/:ID",              "GET",      "ClienteApiController",       "getDatosCliente");
("/cliente/:ID",              "PUT",      "ClienteApiController",       "updateDatosCliente");
("/tarjeta/:ID",              "GET",      "TarjetaApiController",     "getTarjeta");
("/actividad/:ID",            "GET",      "ActividadApiController",   "getActividad");
("/actividad/?intervalofechas=fecha1&fecha2",                  "GET",     "DatosApiController",       "addDatos");
("/tarjeta/:ID",              "DELETE",   "TarjetaApiController",       "deleteTarjeta");*/
class TarjetaController{
    private $model;
    private $view;

    function __construct(){
        $this->model = new TarjetaModel();
        $this->view = new ApiView();
    }

    function getTarjetas(){
        $tarjetas = $this->model->getTarjetas();
        if($tarjetas){
            return $this->view->response($tarjetas, 200);
        }else{
            return $this->view->response("No se encontraron tarjetas disponibles", 404);
        }
    }
    function getTarjeta($params = null){
        $idTarjeta = $params[":ID"];
        $tarjeta = $this->model->getTarjeta($idTarjeta);
        if ($tarjeta){
            return $this->view->response($tarjeta, 200);
        }else {
            return $this->view->response("La tarjeta con el id=$idTarjeta no existe", 404);
        }
    }

    public function deleteTarjeta($params = null) {
        $idTarjeta = $params[':ID'];
        $tarjeta = $this->model->get($idTarjeta);
        if ($tarjeta) {
            $this->model->delete($idTarjeta);
            $this->view->response("La tarjeta fue borrada con exito.", 200);
        } else
            $this->view->response("La tarjeta con el id={$idTarjeta} no existe", 404);
    }

}
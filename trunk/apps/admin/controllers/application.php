<?php

/**
 * Todas las controladores heredan de esta clase en un nivel superior
 * por lo tanto los metodos aqui definidos estan disponibles para
 * cualquier controlador.
 *
 * @category Kumbia
 * @package Controller
 * @access public
 **/
class ControllerBase {

	public function init(){
		if(Router::getActiveApplication()!="admin"){
			Router::redirectToApplication("admin");
		} else {
			Router::routeTo("controller: asadmin");
		}
	}

}


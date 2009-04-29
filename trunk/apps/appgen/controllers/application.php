<?php

/**
 * Louder Application Forms
 *
 * LICENSE
 *
 * This source file is subject to the New BSD License that is bundled
 * with this package in the file docs/LICENSE.txt.
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@loudertechnology.com so we can send you a copy immediately.
 *
 * @category 	Louder
 * @package 	Louder
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright  	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @copyright	Copyright (c) 2008-2009 Oscar Garavito (game013@gmail.com)
 * @license 	New BSD License
 * @version 	$Id$
 */

/**
 * Todas las controladores heredan de esta clase en un nivel superior
 * por lo tanto los metodos aqui definidos estan disponibles para
 * cualquier controlador.
 *
 * @category 	Louder
 * @package 	Controller
 * @access 		public
 **/
class ControllerBase {

	public function init(){
		Core::routeTo("controller: create");
	}

}


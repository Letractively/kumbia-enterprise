<?php

/**
 * Kumbia Enterprise Framework
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
 * @category	Kumbia
 * @package		Filter
 * @subpackage	BaseFilters
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2007-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @copyright	Copyright (c) 2007-2007 Emilio Rafael Silveira Tovar(emilio.rst at gmail.com)
 * @copyright	Copyright (c) 2007-2007 Deivinson Tejeda Brito (deivinsontejeda at gmail.com)
 * @license		New BSD License
 * @version 	$Id$
 */

/**
 * EmailFilter
 *
 * Filtra una cadena para que contenga solo correos electronicos
 *
 * @category	Kumbia
 * @package		Filter
 * @subpackage	BaseFilters
 * @copyright	Copyright (c) 2007-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @copyright	Copyright (c) 2007-2007 Emilio Rafael Silveira Tovar(emilio.rst at gmail.com)
 * @copyright	Copyright (c) 2007-2007 Deivinson Tejeda Brito (deivinsontejeda at gmail.com)
 * @license		New BSD License
 */
class EmailFilter implements FilterInterface {

	/**
 	 * Ejecuta el filtro
 	 *
 	 * @param string $s
 	 * @return int
 	 */
	public function execute($s){
		$patron = '/[a-zA-Z0-9\._\-]+[@+][a-zA-Z0-9\._\-]+/';
		if(preg_match($patron, $s, $regs)){
			return $regs[0];
		} else {
			return "";
		}
	}

}

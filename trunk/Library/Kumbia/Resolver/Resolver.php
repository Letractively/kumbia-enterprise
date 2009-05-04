<?php

/**
 * Kumbia Enteprise Framework
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
 * @category 	Kumbia
 * @package 	Router
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 * @version 	$Id: Router.php 29 2009-05-01 02:19:38Z gutierrezandresfelipe $
 */

/**
 * Resolver
 *
 * Este componente permite resolver los servicios web en el contenedor
 * de servicios ó mediante un naming directory service
 *
 * @category 	Kumbia
 * @package 	Resolver
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @license  	New BSD License
 * @abstract
 */
abstract class Resolver {

	/**
	 * Servicios resueltos
	 *
	 * @var array
	 */
	static $_resolvedServices = array();

	/**
	 * Localiza la ubicación de un servicio web
	 *
	 * @access 	public
	 * @param 	string $serviceName
	 * @return 	WebServiceClient
	 * @static
	 */
	public static function lookUp($serviceName){
		$instancePath = Core::getInstancePath();
		$activeApp = Router::getApplication();
		$serviceURL = 'http://'.$_SERVER['HTTP_HOST'].$instancePath.$activeApp.'/'.$serviceName;
		$webService = new WebServiceClient(array(
			'location' => $serviceURL
		));
		return $webService;
	}

}
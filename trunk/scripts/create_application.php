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
 * to kumbia@kumbia.org so we can send you a copy immediately.
 *
 * @category Kumbia
 * @package Scripts
 * @copyright Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license New BSD License
 */

require 'Library/Kumbia/Core/ClassPath/CoreClassPath.php';
require 'Library/Kumbia/Autoload.php';

class CreateApplication extends Script {

	public function __construct(){
		$parameters = $this->getParameters(array('name'));
		$name = $parameters['name'];
		ComponentBuilder::createApplication($name);
	}

}

try {
	$script = new CreateApplication();
}
catch(CoreException $e){
	print get_class($e).' : '.$e->getMessage().'\n';
}
catch(Exception $e){
	print 'Exception : '.$e->getMessage().'\n';
}


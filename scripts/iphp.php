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

if(isset($_SERVER['SERVER_SOFTWARE'])){
	header('Location: index.php');
	exit;
}

$fp = fopen("php://stdin", "r");
print "Bienvenido a Kumbia Enterprise Console\n";
print "Escriba 'exit' para salir\n\n";
print "iphp> ";
while($_c = fgets($fp)){
	if(rtrim($_c)=="quit"){
		exit;
	}
	if(rtrim($_c)=='entonces que parce'){
		print 'que mas'."\n";
	}
	if(rtrim($_c)=='que hay pa hacer'){
		print 'no se pero yo me apunto!'."\n";
	}
	if(rtrim($_c)=='bueno nos vemos'){
		print 'listo marik'."\n";
	}
	/*try {
		if(trim($_c)){
			$_a = eval("return ".trim($_c).";");
			if($_a===null){
				print "NULL";
			} else {
				if($_a===false){
					print "FALSE";
				} else {
					if($_a===true){
						print "TRUE";
					} else {
						if(!is_object($_a)){
							print_r($_a);
						} else {
							print "Object Instance Of ".get_class($_a);
						}
					}
				}
			}
			print "\niphp> ";
		} else {
			print "iphp> ";
		}
	}
	catch(KumbiaException $e){
		print $e->getMessage()."\n";
		$i = 1;
		foreach($e->getTrace() as $trace){
			if($trace['class']){
				print "#$i {$trace['class']}::{$trace['function']}(".join(",",$trace['args']).") en ".basename($trace['file'])."\n";
			}
			$i++;
		}
	}*/
	print "iphp> ";
}
fclose($fp);

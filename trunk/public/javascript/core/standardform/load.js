
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
 * @package	Core
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (C) 2005-2008 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
 */

function entero(x){ return parseInt(x); }
function integer(x){ return parseInt(x); }

function enable_browse(obj, action){
	window.location = Utils.getKumbiaURL(action+"/browse/");
}

if(document.all){
	onerror = handleErr
}

function handleErr(msg,url,l) {
	var txt = "";
	if(document.all){
		txt="FormError: There was an error on this Application.\n\n"
		txt+="Error: " + msg + "\n"
		txt+="URL: " + url + "\n"
		txt+="Line: " + l + "\n\n"
		txt+="Please inform this error to your Software Provider.\n\n"
		alert(txt)
	};
	return true
}

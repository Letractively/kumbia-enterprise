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
 * @package		Helpers
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @copyright	Copyright (C) 2007-2007 Roger Jose Padilla Camacho(rogerjose81 at gmail.com)
 * @copyright	Copyright (c) 2007-2008 Emilio Rafael Silveira Tovar(emilio.rst at gmail.com)
 * @copyright	Copyright (c) 2007-2008 Deivinson Tejeda Brito (deivinsontejeda at gmail.com)
 * @license  	New BSD License
 * @version 	$Id: Helpers.php 135 2009-04-16 11:06:19Z gutierrezandresfelipe $
 */

/**
 * Helpers
 *
 * Componente que implementa ayudas utiles al desarrollador
 *
 * @category 	Kumbia
 * @package 	Helpers
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @copyright 	Copyright (c) 2007-2007 Roger Jose Padilla Camacho(rogerjose81 at gmail.com)
 * @copyright 	Copyright (c) 2007-2008 Emilio Rafael Silveira Tovar(emilio.rst at gmail.com)
 * @copyright 	Copyright (c) 2007-2008 Deivinson Tejeda Brito (deivinsontejeda at gmail.com)
 * @license  	New BSD License
 * @access 		public
 */
abstract class Helpers {

	/**
	 * Escribe un valor en bytes en forma humana
	 *
	 * @param integer $num
	 * @param integer $decimals
	 * @return string
	 * @static
	 */
	static public function toHuman($num, $decimals=2){
		$num = (int) $num;
		if($num<1024){
			return $num." bytes";
		} else {
			if($num<1048576){
				return round($num/1024, $decimals)." kb";
			} else {
				if($num<1073741824){
					return round($num/1024/1024, $decimals)." mb";
				} else {
					return round($num/1024/1024/1024, $decimals)." gb";
				}
			}
		}
	}

	/**
	 * Genera una frase mediante un timestamp indicando con palabras
	 * hace cuanto ocurrio algo
	 *
	 * @access public
	 * @param integer $time
	 * @static
	 */
	static public function verboseTimeAgo($time){
		$now = time();
		if($time){
			if($time>=($now-60)){
				return "Hace unos segundos";
			} else {
				if($time>=($now-3600)){
					return "Hace unos minutos (".date("H:i:s", $time).")";
				} else {
					if($time>=($now-86400)){
						return "Hace unos horas (".date("H:i:s", $time).")";
					} else {
						return date("Y-m-d H:i:s");
					}
				}
			}
		}
		return "";
	}

	/**
	 * Recibe una cadena como: item1,item2,item3 y retorna una como: "item1","item2","item3".
	 *
	 * @param string $lista
	 * @return string $listaEncomillada
	 */
	static public function encomillarLista($lista){
		$arrItems = split(",", $lista);
		$n = count($arrItems);
		$listaEncomillada = "";
		for ($i=0; $i<$n-1; $i++) {
			$listaEncomillada.= "\"".$arrItems[$i]."\",";
		}
		$listaEncomillada.= "\"".$arrItems[$n-1]."\"";
		return $listaEncomillada;
	}

	/**
	 * Devuelve un string encerrado en comillas
	 *
	 * @param string $word
	 * @return string
	 */
	static public function comillas($word){
		return "'$word'";
	}

	/**
	 * Resalta un Texto en otro Texto
	 *
	 * @param string $sentence
	 * @param string $what
	 * @return string
	 */
	static public function highlight($sentence, $what){
		return str_replace($what, '<strong class="highlight">'.$what.'</strong>', $sentence);
	}

	/**
	 * Escribe un numero usando formato numerico
	 *
	 * @param string $number
	 * @param integer $n
	 * @return string
	 */
	static public function money($number, $n=2){
		$number = my_round($number, $n);
		return "$&nbsp;".number_format($number, $n, ",", ".");
	}

	/**
	 * Redondea un numero
	 *
	 * @param numeric $n
	 * @param integer $d
	 * @return string
	 */
	static public function roundnumber($n, $d = 0) {
		$n = $n - 0;
		if($d===NULL){
			$d = 2;
		}
		$f = pow(10, $d);
		$n += pow(10, - ($d + 1));
		$n = round($n * $f) / $f;
		$n += pow(10, - ($d + 1));
		$n += '';
		if($d==0){
			return substr($n, 0, strpos($n, '.'));
		} else {
			return substr($n, 0, strpos($n, '.') + $d + 1);
		}
	}

	/**
	 * Realiza un redondeo usando la funcion round de la base
	 * de datos.
	 *
	 * @param numeric $number
	 * @param integer $n
	 * @return numeric
	 */
	static public function myRound($number, $n=2){
		$number = (float) $number;
		$n = (int) $n;
		return ActiveRecord::staticSelectOne("round($number, $n)");
	}

	/**
	 * Una version avanzada de trim
	 *
	 * @param string $word
	 * @param integer $number
	 * @return string
	 */
	public static function truncate($word, $number=0){
		if($number){
			return substr($word, 0, $number);
		} else {
			return rtrim($word);
		}
	}

}

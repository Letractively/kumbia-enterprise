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
 * @category 	Kumbia
 * @package 	Report
 * @subpackage 	Adapters
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 * @version 	$Id: Html.php 122 2010-02-11 19:09:18Z gutierrezandresfelipe $
 */

/**
 * TextReport
 *
 * Adaptador que permite generar reportes en Texto Plano
 *
 * @category 	Kumbia
 * @package 	Report
 * @subpackage 	Adapters
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 * @abstract
 */
class TextReport extends ReportAdapter implements ReportInterface {

	/**
	 * Salida HTML
	 *
	 * @var string
	 */
	private $_output;

	/**
	 * Tamaño de texto predeterminado
	 *
	 * @var int
	 * @static
	 */
	private static $_defaultFontSize = 12;


	/**
	 * Fuente de texto predeterminado
	 *
	 * @var int
	 * @static
	 */
	private static $_defaultFontFamily = "Lucida Console";

	/**
	 * Alto de cada fila
	 *
	 * @var int
	 */
	private $_rowHeight = 0;

	/**
	 * Altura del encabezado
	 *
	 * @var int
	 */
	private $_headerHeight = 0;

	/**
	 * Numero total de paginas del reporte
	 *
	 * @var int
	 */
	private $_totalPages = 0;

	/**
	 * Totales de columnas
	 *
	 * @var array
	 */
	protected $_totalizeValues = array();

	/**
	 * Formatos de Columnas
	 *
	 * @var array
	 */
	private $_columnFormats = array();

	/**
	 * Número de columnas del reporte
	 *
	 * @var int
	 */
	private $_numberColumns = null;

	/**
	 * Indica si el reporte debe ser volcado a disco en cuanto se agregan los datos
	 *
	 * @var unknown_type
	 */
	protected $_implicitFlush = false;

	/**
	 * Handler al archivo temporal donde se volca el reporte
	 *
	 * @var handler
	 */
	private $_tempFile;

	/**
	 * Nombre del archivo temporal donde se volca el reporte
	 *
	 * @var string
	 */
	private $_tempFileName;

	/**
	 * Indica si el volcado del reporte ha sido iniciado
	 *
	 * @var boolean
	 */
	private $_started = false;

	/**
	 * Genera la salida del reporte
	 *
	 * @return string
	 */
	public function getOutput(){
		$this->_prepareHead();
		$this->_renderPages();
		$this->_prepareFooter();
		return $this->_output;
	}

	/**
	 * Inicia el reporte
	 *
	 * @param boolean $implicitFlush
	 */
	protected function _start($implicitFlush=false){
		$this->_implicitFlush = $implicitFlush;
		if($this->_implicitFlush==true){
			$this->_tempFileName = 'public/temp/'.uniqid();
			$this->_tempFile = fopen($this->_tempFileName, 'w');
			$this->_prepareHead();
			$this->_renderHeader();
			$this->_appendToOutput("<p><table cellspacing='0' align='center'>\n");
			$this->_renderColumnHeaders();
		}
		$this->_started = true;
	}

	/**
	 * Finalizar el reporte
	 *
	 */
	protected function _finish(){
		if($this->_implicitFlush==true){
			$this->_renderTotals();
			$this->_appendToOutput("</table></p>\n");
			$this->_prepareFooter();
		}
	}

	/**
	 * Genera el encabezado del reporte
	 *
	 */
	private function _prepareHead(){
		if($this->_started==false){
			$output = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
			$output.= "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n";
			$output.= "<html xmlns=\"http://www.w3.org/1999/xhtml\">\n";
			$output.= "<head>\n";
			$output.= "<meta http-equiv='Content-type' content='text/plain; charset=".$this->getEncoding()."' />\n";
			$output.= "<meta http-equiv='Pragma' CONTENT='no-cache' />\n";
	   		$output.= "\t\t<meta http-equiv='Cache-Control' CONTENT='no-cache' />\n";
			$output.= "\t\t<title>".$this->getDocumentTitle()."</title>\n";
			$output.= "\t\t<style type='text/css'>\n";
			$output.= "\t\t\tbody, div, td, th { font-family: \"".self::$_defaultFontFamily."\"; font-size: ".self::$_defaultFontSize."px; }\n";
			$output.= "\t\t\ttable { border: none; }\n";
			$output.= "\t\t\tth, td { border: none; font-weight: normal; padding-left: 10px; }\n";
			$output.= "\t\t\tbody { margin: 0px; padding: 0px; }\n";
			$this->_appendToOutput($output);
			$this->_prepareCellHeaderStyle();
			$this->_prepareColumnStyles();
			$this->_prepareColumnFormats();
			$this->_prepareTotalizedColumns();
			$this->_appendToOutput("\t\t</style>\n");
			$this->_appendToOutput("\t</head>\n");
			$this->_appendToOutput("\t<body>\n");
		}
	}

	protected function _prepareFooter(){
		$this->_appendToOutput("\t</body>\n");
		$this->_appendToOutput("</html>\n");
	}

	/**
	 * Agrega una cadena al reporte
	 *
	 * @param string $output
	 */
	private function _appendToOutput($output){
		if($this->_implicitFlush==true){
			fwrite($this->_tempFile, $output);
			unset($output);
		} else {
			$this->_output.=$output;
		}
	}

	/**
	 * Escribe los estilos de los encabezados del reporte
	 *
	 * @access protected
	 */
	protected  function _prepareCellHeaderStyle(){

	}

	/**
	 * Obtiene los formatos asignados a las columnas del reporte
	 *
	 * @access protected
	 */
	protected function _prepareColumnFormats(){
		$this->_columnFormats = $this->getColumnFormats();
	}

	/**
	 * Obtiene los valores base de totales de las columnas
	 *
	 * @access protected
	 */
	protected function _prepareTotalizedColumns(){
		$this->_totalizeValues = $this->getTotalizeValues();
	}

	/**
	 * Escribe los estilos de las columnas del reporte
	 *
	 * @access protected
	 */
	protected function _prepareColumnStyles(){
		$styles = $this->getColumnStyles();
		if(count($styles)){
			foreach($styles as $numberColumn => $style){
				$columnStyle = $style->getStyles();
				$preparedStyle = $this->_prepareStyle($columnStyle);
				$this->_appendToOutput("\t\t\t.c$numberColumn { ".join(";", $preparedStyle)."; }\n");
			}
		}
	}

	public function _prepareStyle($attributes){
		$style = array();
		foreach($attributes as $attributeName => $value){
			switch($attributeName){
				case 'textAlign':
					$style[] = 'text-align:'.$value;
					break;
				case 'paddingRight':
					$style[] = 'padding-right:'.$value;
					break;
			}
		}
		return $style;
	}

	/**
	 * Renderiza el encabezado del documento
	 *
	 */
	protected function _renderHeader(){
		$header = $this->getHeader();
		if(is_array($header)){
			foreach($header as $item){
				 $this->_renderItem($item);
			}
		} else {
			$this->_renderItem($item);
		}
	}

	/**
	 * Renderiza un item
	 *
	 * @param mixed $item
	 * @return array
	 */
	protected function _renderItem($item){
		if(is_string($item)){
			$this->_appendToOutput($item);
			return;
		}
		if(is_object($item)==true){
			if(get_class($item)=="ReportText"){
				$html = "<div ";
				$itemStyle = $item->getAttributes();
				$style = $this->_prepareStyle($itemStyle);
				if(count($style)){
					$html.="style='".join(";", $style)."'";
				}
				$html.=">".$this->_prepareText($item->getText())."</div>\n";
				$this->_appendToOutput($html);
				return $itemStyle;
			}
		}
	}

	/**
	 * Escribe los encabezados del reporte
	 *
	 */
	private function _renderColumnHeaders(){
		$output = "<thead>\n";
		foreach($this->getColumnHeaders() as $header){
			$output.="<th>$header</th>\n";
		}
		$output.="</thead>\n";
		$this->_appendToOutput($output);
	}

	/**
	 * Crea un thumbnail
	 *
	 * @param	int $pageNumber
	 * @return	string
	 */
	private function _getPageThumbnail($pageNumber){
		$numColumns = count($this->getColumnHeaders());
		if($numColumns==0||$numColumns>6){
			$numColumns = 4;
		}
		$code = "<div class='thumbnail' align='center'>
		<a href='#$pageNumber'>
		<table cellspacing='0' cellpadding='0' width='50'><tr>";
		for($i=0;$i<$numColumns;++$i){
			$code.="<th></th>";
		}
		$code.="</tr>";
		for($j=0;$j<9;++$j){
			$code.="<tr>";
			for($i=0;$i<$numColumns;++$i){
				$code.="<td></td>";
			}
			$code.="</tr>";
		}
		$code.="</table>$pageNumber</a></div>";
		return $code;
	}

	/**
	 * Escribe las páginas del reporte
	 *
	 * @param array $rows
	 */
	private function _renderRows($rows){
		foreach($rows as $row){
			$this->_renderRow($row);
		}
	}

	/**
	 * Agrega una fila al reporte en implicit flush
	 *
	 * @param array $row
	 */
	protected function _addRow($row){
		$this->_renderRow($row);
	}

	/**
	 * Escribe una fila del reporte
	 *
	 * @param array $row
	 */
	private function _renderRow($row){
		$output = "<tr>\n";
		if($row['_type']=='normal'){
			unset($row['_type']);
			if($this->_numberColumns===null){
				$this->_numberColumns = count($row);
			}
			foreach($row as $numberColumn => $value){
				if(isset($this->_totalizeColumns[$numberColumn])){
					if(!isset($this->_totalizeValues[$numberColumn])){
						$this->_totalizeValues[$numberColumn] = 0;
					}
					$this->_totalizeValues[$numberColumn]+=$value;
				}
				if(isset($this->_columnFormats[$numberColumn])){
					$value = $this->_columnFormats[$numberColumn]->apply($value);
				}
				$output.="<td class='c$numberColumn'>$value</td>\n";
			}
		} else {
			if($row['_type']=='raw'){
				unset($row['_type']);
				foreach($row as $numberColumn => $rawColumn){
					$output.="<td colspan='".$rawColumn->getSpan()."'";
					$styles = $rawColumn->getStyle();
					if($styles){
						$style = $this->_prepareStyle($styles);
						$output.=" style='".join(';', $style)."'	";
					}
					$output.=">".$rawColumn->getValue()."</td>\n";
					unset($rawColumn);
				}
			}
		}
		$output.="</tr>\n";
		$this->_appendToOutput($output);
	}

	/**
	 * Escribe las páginas del reporte
	 *
	 */
	private function _renderPages(){
		$output = '';
		$data = $this->getRows();
		if($this->getPagination()==true){
			$calculatedOffset = $this->getRowsPerPage();
			$renderRows = 0;
			$numberRows = count($data);
			if($calculatedOffset!=0){
				$rowsToRender = $calculatedOffset;
			} else {
				$calculatedOffset = -1;
				$rowsToRender = 0;
			}
			$pageNumber = 1;
			if($numberRows>0){
				while($renderRows<$numberRows){
					$this->_appendToOutput("<div align='center'><div class='page'><a name='$pageNumber'>\n");
					$this->_renderHeader();
					if($calculatedOffset==-1){
						$calculatedOffset = ceil(($this->_rowHeight*$numberRows+$this->_headerHeight+20)/700);
						$rowsToRender = floor($numberRows/$calculatedOffset);
					}
					$this->_appendToOutput("<p><table cellspacing='0'>\n");
					$this->_renderColumnHeaders();
					$this->_renderRows(array_slice($data, $renderRows, $rowsToRender));
					$this->_renderTotals();
					$this->_appendToOutput("</table></p>\n");
					$this->_appendToOutput("</div></div>\n");
					$renderRows+=$rowsToRender;
					++$pageNumber;
					$this->_setPageNumber($pageNumber);
				}
			} else {
				$this->_appendToOutput("<div align='center'><div class='page'><a name='$pageNumber'>\n");
				$this->_renderHeader();
				$this->_appendToOutput("<p><table cellspacing='0'>\n");
				$this->_renderColumnHeaders();
				$this->_appendToOutput("</table></p>\n");
				$this->_appendToOutput("</div></div>\n");
				++$pageNumber;
				$this->_setPageNumber($pageNumber);
			}
			$this->_totalPages = $pageNumber;
		} else {
			$this->_renderHeader();
			$this->_appendToOutput("<p><table cellspacing='0' align='center'>\n");
			$this->_renderColumnHeaders();
			$this->_renderRows($data);
			$this->_renderTotals();
			$this->_appendToOutput("</table></p>\n");
		}
	}

	/**
	 * Renombra el archivo temporal del volcado al nombre dado por el usuario
	 *
	 * @param	string $path
	 * @return	boolean
	 */
	protected function _moveOutputTo($path){
		if(rename($this->_tempFileName, $path)){
			return basename('/'.$path);
		}
	}

	/**
	 * Visualiza los totales del reporte
	 *
	 */
	private function _renderTotals(){
		if(count($this->_totalizeValues)>0){
			$output = '<tr>';
			for($i=0;$i<$this->_numberColumns;++$i){
				if(isset($this->_totalizeValues[$i])){
					if(isset($this->_columnFormats[$i])){
						$this->_totalizeValues[$i] = $this->_columnFormats[$i]->apply($this->_totalizeValues[$i]);
					}
					$output.='<td class="c'.$i.'">'.$this->_totalizeValues[$i].'</td>';
				} else {
					$output.='<td class="c'.$i.'"></td>';
				}
			}
			$output.='</tr>';
			$this->_appendToOutput($output);
		}
	}

	/**
	 * Devuelve la extension del archivo recomendada
	 *
	 * @return string
	 */
	protected function getFileExtension(){
		return 'html';
	}

}

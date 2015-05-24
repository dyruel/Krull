<?php
/*******************************************************************************
*  ==========================================================================
*									 Krull
*  ==========================================================================
*
*								krullHtml.class.php
*  --------------------------------------------------------------------------
*
*	   Site Web :		
*	   Fait par :		
*	   Commencé le :	
*	   Modifié le :		
*
*  --------------------------------------------------------------------------
*	Ce programme est libre, vous pouvez le redistribuer et/ou le modifier
*	selon les termes de la Licence Publique Générale GNU publiée par la Free
*	Software Foundation (version 2). Reportez-vous à la Licence Publique
*	Générale GNU pour plus de détails. Vous devez avoir reçu une copie de
*	la Licence Publique Générale GNU en même temps que ce programme ; si ce
*	n'est pas le cas, écrivez à la Free Software Foundation, Inc., 59 Temple
*	Place, Suite 330, Boston, MA 02111-1307, États-Unis.
*  --------------------------------------------------------------------------
*
*******************************************************************************/

// Color Box
define('KRULL_COLORBOX_BLUE',0); // (1,0,0)
define('KRULL_COLORBOX_RED',1);
define('KRULL_COLORBOX_GREEN',2);
define('KRULL_COLORBOX_YELLOW',3); // (1,1,0)
define('KRULL_COLORBOX_PURPLE',4); // (1,0,1)
define('KRULL_COLORBOX_LBLUE',5); // (0,1,1)
define('KRULL_COLORBOX_ORANGE',6); // (1,0.5,0)


class krullHtml extends krullSingleton
{
	//
	var $_mainBuffer;

	//
	var $_settings;

	//
	var $_includes;
	
	//
	var $_messages;

	// 
	var $_htmlEntities;

	/*

	*/
	function __construct()
	{
		parent::__construct();

		$this->_mainBuffer = '';
		$this->_htmlEntities = '';
		$this->messages = array();
		$this->_settings = array(
			'charSet' => 'UTF-8',
			'title' => 'krullProject'
		);
	}

	/*

	*/
    function &getInstance()
	{
		return parent::__getInstanceImp('krullHtml');
    }

	/*

	*/
	function configure($name, $value)
	{
		$this->_settings[$name] = $value;
	}

	/*

	*/
	function configure_many($options)
	{
		foreach ($options as $name => $value)
		{
			$this->configure($name, $value);
		}
	}

	/*
		Function: addFile
		
		$type :
			'CSS' -> fichier css
			'JS'  -> script javascript
	*/
	function addFile($type,$file)
	{
		if(is_file($file))
		{
			$this->_includes[] = array('type' => $type, 'name' => $file);
		}
		else
		{
			trigger_error('Le fichier '.$file.' est introuvable. La page risque de ne pas s\'afficher correctement.',E_USER_WARNING);
		}
	}

	/*

	*/
	function addMsgBox($color, $title, $msg)
	{
		$div = '';
		$style = '';
		$bgColor = '';
		$border = '';
		$js = '';
		$k = 0;

		if(!isset($title) || !isset($msg) || !is_string($title) || !is_string($msg))
		{
			return false;
		}

		if($color == KRULL_COLORBOX_BLUE)
		{
			$bgColor = '#CCCCFF';
			$border = '#333399';
		}
		else if($color == KRULL_COLORBOX_RED)
		{
			$bgColor = '#FFCCCC';
			$border = '#993333';
		}
		else if($color == KRULL_COLORBOX_GREEN)
		{
			$bgColor = '#CCFFCC';
			$border = '#339933';
		}
		else if($color == KRULL_COLORBOX_YELLOW)
		{
			$bgColor = '#FFFFCC';
			$border = '#999933';
		}
		else if($color == KRULL_COLORBOX_PURPLE)
		{
			$bgColor = '#FFCCFF';
			$border = '#993399';
		}
		else if($color == KRULL_COLORBOX_LBLUE)
		{
			$bgColor = '#CCFFFF';
			$border = '#339999';
		}
		else if($color == KRULL_COLORBOX_ORANGE)
		{
			$bgColor = '#FFEECC';
			$border = '#996633';
		}
		else
		{
			$bgColor = '#CCCCCC';
			$border = '#333333';		
		}

		$k = count($this->_messages);

		$div = "<div id='system_out_".$k."' style ='display:block; position:absolute; width:16em; margin-left:-8em; left:50%; height:8em; margin-top:-4em; top:50%; background-color:".$bgColor."; border: 1px solid ".$border."; padding: 5px 5px 5px 5px'>\n";
		$div .= "<div style='width:95%; font-size: medium; position:relative; float:left; text-align:center'>".$title."</div>\n";
		$div .= "<div style='position:relative; clear:left; text-align:center; padding-top:10px; border-top: 1px solid ".$border.";'>".$msg."</div>\n";
		$div .= '<div style=\'text-align:center; padding-top:10px;\'><a href="javascript:void(0)" onClick="document.getElementById(\'system_out_'.$k.'\').style.display = \'none\'">ok</a></div>';
		$div .= "\n</div>\n\n";

		// Ajout du message
		$this->_messages[] = $div;

		return true;
	}

	/*

	*/
	function addHtmlEntities($html)
	{
		$this->_htmlEntities .= $html."\n";
	}

	/*

	*/
	function getMainBuffer()
	{
		if(empty($this->_mainBuffer))
		{
			return '';
		}
		
		return $this->_mainBuffer;
	}

	/*

	*/
	function clean($cleanMainBuffer = false)
	{
		$this->_includes = array();
		$this->_htmlEntities = '';

		if($cleanMainBuffer)
		{
			$this->_mainBuffer = '';
		}
	}
	
	/*

	*/
	function buildHtmlDocument()
	{
		$html = '';
		$head = '';
		$body = '';

		$html = '<!-- Generated with krull -->'."\n";
		$html .= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'."\n";
		$html .= '<html xmlns="http://www.w3.org/1999/xhtml">'."\n";

		// Head
		$head = '<head>'."\n";
		$head .= '<meta http-equiv="Content-Type" content="text/html;" charset="'.$this->_settings['charSet'].'" />'."\n";
		$head .= '<title>'.$this->_settings['title'].'</title>'."\n";

		// Pour chaque fichiers ajoutés
		if(!empty($this->_includes))
		{
			foreach ($this->_includes as $include)
			{
				if($include['type'] == 'css' || $include['type'] == 'CSS')
				{
					$head .= '<link href=\''.$include['name'].'\' rel=\'stylesheet\' type=\'text/css\' />'."\n";
				}
				else if($include['type'] == 'js' || $include['type'] == 'JS')
				{
					$head .= '<script src=\''.$include['name'].'\' type=\'text/javascript\' /></script>'."\n";
				}
				else
				{
					trigger_error('Le type du fichier '.basename($include['name']).' est inconnu. La page risque de ne pas s\'afficher correctement' ,E_USER_WARNING);
				}
			}
		}

		$head .= '</head>'."\n";

		// Body
		$body = '<body>'."\n";

		if(!empty($this->_messages))
		{
			foreach($this->_messages as $message)
			{
				$body .= $message;
			}
		}
		
		$body .= $this->_htmlEntities;

		$body .= '</body>'."\n";

		// Formation du document
		$html .= $head.$body;

		$html .= '</html>';

		$this->_mainBuffer = $html;
	}

}

?>
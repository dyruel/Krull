<?php
/*******************************************************************************
*  ==========================================================================
*									 Krull
*  ==========================================================================
*
*							krullProcessor.class.php
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

if(!defined('IN_KRULL'))
{
	die('<[INTERDIT]>');
}

/*
	Chef d'orchestre de Krull
*/
class krullProcessor extends krullSingleton
{

	/*

	*/
	function __construct()
	{
		global $dbType, $dbUser, $dbHost, $dbPwd, $dbName;

		parent::__construct();

		$this->summonClass('Cache');
		$this->summonClass('DataBase');
		$this->summonClass('Template');
		$this->summonClass('Xml');
		$this->summonClass('ModulesMng');
		$this->summonClass('Session');
		$this->summonClass('Html');

		// Selection et connexion à la base de données
		$impl = null;

		if($dbType == 'mysql')	
		{
			$impl =& MySQL::getInstance();
		}
		else
		{
			trigger_error('Base de données non gérée.', E_USER_ERROR);
			return false;
		}

		$db =& krullDataBase::getInstance();
		$db->setImplementor($impl);

		if(!$db->connect($dbHost,$dbUser,$dbPwd,$dbName))
		{
			trigger_error('Impossible de se connecter à la base de données.', E_USER_ERROR);
			return false;
		}
	}

	/*

	*/
    function &getInstance()
	{
		return parent::__getInstanceImp('krullProcessor');
    }


	/*

	*/
    function getVersion()
	{
		return 'krull alpha';
    }

	/*
		Invoque la classe $sNom.
	*/
	function summonClass($sNom)
	{
		if(is_string($sNom))
		{
			require_once(ROOT . 'krull_core/class/krull' . $sNom . '.class.php');
		}
	}
	
	/*
		Invoque la function $sNom.
	*/
	function summonFunctions($sNom)
	{
		if(is_string($sNom))
		{
			require_once(ROOT . 'krull_core/functions/krull_' . $sNom . '.func.php');
		}
	}

	/*

	*/
	function process()
	{
		$module = 0;

		$oKrullModsMng =& krullModulesMng::getInstance();

		if(isset($_GET['m']) && preg_match("/^([0-9]+)$/",$_GET['m']))
		{
			$module = (int) $_GET['m'];
	
			$oKrullModsMng->selectIndex($module);
			$oKrullModsMng->main();
		}
		else
		{
			trigger_error('Erreur URL',E_USER_ERROR);
		}
	}

	/*
		Load modules
	*/
	function loadModules()
	{
		$modules = null;
		$nbModule = 0;
		$db =& krullDataBase::getInstance();
		$oKrullModsMng =& krullModulesMng::getInstance();

		$modules = $db->query('SELECT nom, main FROM krull_modules WHERE is_active = 1');
		$nbModule = $db->numRows($modules);

		if($nbModule > 0)
		{
			while($module = $db->fetchArray($modules))
			{
				$moduleMainFile = ROOT . 'krull_modules/'.$module['nom'].'/'.$module['main'];

				if(is_file($moduleMainFile))
				{
					require_once($moduleMainFile);
					$oKrullModsMng->load($module['nom']);
				}
				else
				{
					trigger_error('Le module '.$module['nom'].' ne s\'est par chargé correctement.', E_USER_ERROR);
					return false;
				}
			}
		}
	}



	/*
		Archive le fichier $nom dans $target
	*/
/*
	function archive($nom, $target, $options = FALSE)
	{
		$this->summon_class('Archive');

		if(!preg_match('/\.(t?bz2|t?gz|zip|tar)$/', $target, $out))
		{
			//$this->core_error(1003, $out[1]);
		}
		
		if($out[1] == 'tar')
		{
			$type = 'tar';
		}
		else if($out[1] == 'tgz' || $out[1] == 'gz')
		{
			$type = 'gzip';
		}
		else if($out[1] == 'tbz2' || $out[1] == 'bz2')
		{
			$type = 'bzip';
		}
		else
		{
			//$this->core_error(1004);
			$type = 'zip';			
		}

		$class = $type . '_file';
		$archive = new $class($target);

		if($options != FALSE)
		{
			$archive->set_options($options);
		}

		$archive->add_files($nom);

		return $archive->create_archive();
	}
*/
	/*
		Désarchive $nom
	*/
/*
	function unarchive($nom)
	{
		$this->summon_class('Archive');
		
		if(!preg_match('/\.(t?bz2|t?gz|zip|tar)$/', $target, $out))
		{
			//$this->core_error(1003, $out[1]);
		}
		
		if($out[1] == 'tar')
		{
			$type = 'tar';
		}
		else if($out[1] == 'tgz' || $out[1] == 'gz')
		{
			$type = 'gzip';
		}
		else if($out[1] == 'tbz2' || $out[1] == 'bz2')
		{
			$type = 'bzip';
		}
		else
		{
			//$this->core_error(1004);
			$type = 'zip';			
		}


		$class = $type . '_file';
		$archive = new $class($nom);

		return $archive->extract_files();
	}
*/
}

/*
	The error handler
*/
function errorHandler($errno, $errstr, $errfile, $errline)
{
	if (error_reporting() == 0)
	{
		return;
	}

	$errorType = array (
            E_ERROR          => 'ERROR',
            E_WARNING        => 'WARNING',
            E_PARSE          => 'PARSING ERROR',
            E_NOTICE         => 'NOTICE',
            E_CORE_ERROR     => 'CORE ERROR',
            E_CORE_WARNING   => 'CORE WARNING',
            E_COMPILE_ERROR  => 'COMPILE ERROR',
            E_COMPILE_WARNING => 'COMPILE WARNING',
            E_USER_ERROR     => 'USER ERROR',
            E_USER_WARNING   => 'USER WARNING',
            E_USER_NOTICE    => 'USER NOTICE',
            E_STRICT         => 'STRICT NOTICE',
            E_RECOVERABLE_ERROR  => 'RECOVERABLE ERROR'
    );

	$fatalArray = array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_RECOVERABLE_ERROR);

	$oKrullHtml =& krullHtml::getInstance();

	if(!in_array($errno,$fatalArray))
	{
		if($errno == E_USER_ERROR)
		{
			$oKrullHtml->clean(true);
			$oKrullHtml->addMsgBox(KRULL_COLORBOX_RED, $errorType[$errno].' : '.basename($errfile).' ,'.$errline, $errstr);
			$oKrullHtml->buildHtmlDocument();
			echo $oKrullHtml->getMainBuffer();
			exit;
		}
		else
		{
			$oKrullHtml->addMsgBox(KRULL_COLORBOX_ORANGE, $errorType[$errno].' : '.basename($errfile).' ,'.$errline, $errstr);
		}
	}
	else
	{
		echo $errorType[$errno].'::'.$errstr.'; A la ligne'.$errline.' dans le fichier'.$errfile.'.'."\n";
	}
}

// 
set_error_handler('errorHandler');

?>
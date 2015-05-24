<?php
/*******************************************************************************
*  ==========================================================================
*									 Krull
*  ==========================================================================
*
*							krullModulesMng.class.php
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


class krullModulesMng extends krullSingleton
{
	//
	var $_modules;
	
	//
	var $_index;

	function __construct()
	{
		parent::__construct();

		$this->_modules = array();
		$this->_index = 0;
	}

	/*

	*/
    function &getInstance()
	{
		return parent::__getInstanceImp('krullModulesMng');
    }

	/*

	*/
	function load($nom)
	{
		if(class_exists($nom))
		{
			if(get_parent_class($nom) != 'krullmodule')
			{
				trigger_error('Module chargement : module non conforme '.$nom, E_USER_ERROR);
				return false;				
			}

			$this->_index = count($this->_modules);
			eval('$this->_modules[] = new '.$nom.'();');
			return $this->_index;
		}

		trigger_error('Module chargement : classe indéfinie '.$nom, E_USER_ERROR);
		return false;
	}

	/*

	*/
	function unload($index)
	{
		$module_size = count($this->_modules);

		if($index < $module_size && $index >= 0)
		{
			unset($this->_modules[$index]);
			$i = $index + 1;

			while( $i > 0 && $i < $module_size )
			{
				$this->_modules[$i-1] =& $this->_modules[$i];
				unset($this->_modules[$i]);
				$i++;
			}

			if($this->_index > $index)
			{
				$this->_index--;
			}
			else if($this->_index == $index)
			{
				trigger_error('Suppression d\'un module en cours d\'utilisation',E_USER_WARNING);

				$this->_index > 0 ? $this->_index-- : 0;
			}

			return true;
		}

		trigger_error('Module déchargement : hors limite', E_USER_ERROR);
		return false;
	}

	/*

	*/
	function selectIndex($index)
	{
		if($index < count($this->_modules))
		{
			$this->_index = $index;
			return true;
		}

		trigger_error('Module selection : hors limite', E_USER_ERROR);
		return false;
	}

	/*
		
	*/
	function main()
	{
		$args = func_get_args();

		if(count($this->_modules) > 0)
		{
			return call_user_func_array(array($this->_modules[$this->_index], 'main'), $args);
		}

		trigger_error('Il n\'y a aucun modules', E_USER_ERROR);
		return false;
	}

}

// Interface pour les modules
class krullModule
{
	function main()
	{
		return false;
	}
}

?>
<?php
/*******************************************************************************
*  ==========================================================================
*									 Krull
*  ==========================================================================
*
*								krullCommon.inc.php
*  --------------------------------------------------------------------------
*
*	   Site Web :		
*	   Fait par :		
*	   Commenc� le :	
*	   Modifi� le :		
*
*  --------------------------------------------------------------------------
*	Ce programme est libre, vous pouvez le redistribuer et/ou le modifier
*	selon les termes de la Licence Publique G�n�rale GNU publi�e par la Free
*	Software Foundation (version 2). Reportez-vous � la Licence Publique
*	G�n�rale GNU pour plus de d�tails. Vous devez avoir re�u une copie de
*	la Licence Publique G�n�rale GNU en m�me temps que ce programme ; si ce
*	n'est pas le cas, �crivez � la Free Software Foundation, Inc., 59 Temple
*	Place, Suite 330, Boston, MA 02111-1307, �tats-Unis.
*  --------------------------------------------------------------------------
*
*******************************************************************************/

if(!defined('IN_KRULL'))
{
	die('<[INTERDIT]>');
}


// Constants
define('KRULL_MSGBOX_INFO',0);
define('KRULL_MSGBOX_ERR',1);
define('KRULL_MSGBOX_FATAL',2);

// SQL codes
define('BEGIN_TRANSACTION', 1);
define('END_TRANSACTION', 2);


// Includes
require_once(ROOT . 'krull_ghost/krullConfig.inc.php');
require_once(ROOT . 'krull_core/class/patterns/krullObject.class.php');
require_once(ROOT . 'krull_core/class/patterns/krullSingleton.class.php');
require_once(ROOT . 'krull_core/krullProcessor.class.php');

?>
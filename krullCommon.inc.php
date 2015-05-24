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
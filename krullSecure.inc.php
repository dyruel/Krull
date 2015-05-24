<?php
/*******************************************************************************
*  ==========================================================================
*									 Krull
*  ==========================================================================
*
*								krullSecure.inc.php
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

if ( !defined("IN_KRULL") )
{
    die('-[interdit]-');
}

//
//error_reporting  (E_ERROR | E_WARNING | E_PARSE); // This will NOT report uninitialized variables
set_magic_quotes_runtime(0); // Disable magic_quotes_runtime


//
// Optimisation du fichier php.ini tiré de l'article
// http://developpeur.journaldunet.com/tutoriel/php/040330-php-nexen-optimiser3.shtml
// (Merci à Dr DLP pour la "mise en code" de cet article).
//
@ini_set('register_globals', 0);
@ini_set('variables_order', 'GPC');
@ini_set('register_argc_argv', 0);
@ini_set('expose_php', 0);
@ini_set('default_socket_timeout', 10);
@ini_set('allow_url_fopen', 0);

// Protect against GLOBALS tricks
if (isset($HTTP_POST_VARS['GLOBALS']) || isset($HTTP_POST_FILES['GLOBALS']) || isset($HTTP_GET_VARS['GLOBALS']) || isset($HTTP_COOKIE_VARS['GLOBALS']))
{
	die("Hacking attempt");
}

// Protect against HTTP_SESSION_VARS tricks
if (isset($HTTP_SESSION_VARS) && !is_array($HTTP_SESSION_VARS))
{
	die("Hacking attempt");
}

if (@ini_get('register_globals') == '1' || strtolower(@ini_get('register_globals')) == 'on')
{
	// PHP4+ path
	$not_unset = array('HTTP_GET_VARS', 'HTTP_POST_VARS', 'HTTP_COOKIE_VARS', 'HTTP_SERVER_VARS', 'HTTP_SESSION_VARS', 'HTTP_ENV_VARS', 'HTTP_POST_FILES', 'phpEx', 'phpbb_root_path');

	// Not only will array_merge give a warning if a parameter
	// is not an array, it will actually fail. So we check if
	// HTTP_SESSION_VARS has been initialised.
	if (!isset($HTTP_SESSION_VARS) || !is_array($HTTP_SESSION_VARS))
	{
		$HTTP_SESSION_VARS = array();
	}

	// Merge all into one extremely huge array; unset
	// this later
	$input = array_merge($HTTP_GET_VARS, $HTTP_POST_VARS, $HTTP_COOKIE_VARS, $HTTP_SERVER_VARS, $HTTP_SESSION_VARS, $HTTP_ENV_VARS, $HTTP_POST_FILES);

	unset($input['input']);
	unset($input['not_unset']);

	while (list($var,) = @each($input))
	//foreach($input as $var)
	{
		if (in_array($var, $not_unset))
		{
			die('Hacking attempt !');
		}
		unset($$var);
	}

	unset($input);
}

// Parse COOKIE
/*
if(!$magic_quote)
{
	if( is_array($HTTP_COOKIE_VARS) )
	{
		foreach($HTTP_COOKIE_VARS[$k] as $k => $v)
		{
			if( is_array($HTTP_COOKIE_VARS[$k]) )
			{
				foreach($HTTP_COOKIE_VARS[$k] as $k2 => $v2)
				{
					$HTTP_COOKIE_VARS[$k][$k2] = addslashes($v2);
				}
				@reset($HTTP_COOKIE_VARS[$k]);
			}
			else
			{
				$HTTP_COOKIE_VARS[$k] = addslashes($v);
			}
		}
		@reset($HTTP_COOKIE_VARS);
	}
}
*/
if(isset($_GET) && is_array($_GET))
{
    foreach($_GET as $key => $value)
	{
        if(ini_get(register_globals))
		{
			$value = stripslashes($value);
		}

        $value = htmlentities($value,ENT_QUOTES);
        $_GET[$key] = $value;
        ${$key} = $value;
    }
}

if(isset($_POST) && is_array($_POST))
{
    foreach($_POST as $key => $value)
	{
        if(ini_get(register_globals))
		{
			$value = stripslashes($value);
		}

        $value = htmlentities($value,ENT_QUOTES);
        $_POST[$key] = $value;
        ${$key} = $value;
    }
}

// Inclusions
//require_once(ROOT . 'krull_core/class/krullDb.class.php');
//require_once(ROOT . 'krull_core/class/krullCache.class.php');



?>
<?php
/*******************************************************************************
*  ==========================================================================
*									 Krull
*  ==========================================================================
*
*								krullMain.exe.php
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

if(!defined('ROOT'))
{
	define('ROOT','./');
}

define('IN_KRULL',true);


/*

	INCLUSIONS

*/
require_once(ROOT . 'krullSecure.inc.php');
require_once(ROOT . 'krullCommon.inc.php');


/*

	INITIALISATION DU MOTEUR

*/
$oKrullProc =& krullProcessor::getInstance();


/*

	CHARGEMENT DES PLUGINS

*/
$oKrullProc->loadModules();

$oKrullProc->process();

//$oKrullHtml->addMsgBox(KRULL_COLORBOX_PURPLE,'test','test');

$oKrullHtml =& krullHtml::getInstance();

$oKrullHtml->buildHtmlDocument();
echo $oKrullHtml->getMainBuffer();

/*
$oKrullDb =& krullDataBase::getInstance();

$oKrullDb->query('SELECT * FROM krull_plugins');
$array = $oKrullDb->fetchArray();

//echo $array['nom'];

$oKrullXml =& krullSimpleXml::getInstance();
$oKrullTag = $oKrullXml->parseFile('krull_mods/admin/admin.xml');

//echo $oKrullTag->name();

$oKrullTpl =& krullTemplate::getInstance();
//$oKrullTpl->parseAndPrint('empty');

$oKrullSess =& krullSession::getInstance();
$oKrullSess->isLegalKey('');
*/

/*

	APPEL AU MODULE

*/
/*

$oKrullProc->process();
*/

//$oKrullProc->build($oKrullModsMng->main());




?>
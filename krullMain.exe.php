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
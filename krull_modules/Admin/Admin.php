<?php
	
class Admin extends krullModule
{

	function Admin()
	{
		
	}

	/*

	*/
    function &getInstance()
	{
		return parent::__getInstanceImp('Admin');
    }

	function main()
	{
		$oKrullTpl =& krullTemplate::getInstance();
		$oKrullHtml =& krullHtml::getInstance();

		$root = './krull_modules/Admin/';

		$oKrullHtml->addFile('css',$root.'styles/index.css');
		
		$oKrullTpl->setFilenames(array('index' => $root.'templates/index.tpl'));

		$oKrullHtml->addHtmlEntities($oKrullTpl->parseAndBuff('index'));
	}
}


?>
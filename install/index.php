<?php
/**
 * Created by PhpStorm.
 * User: busev
 * Date: 25.10.2017
 * Time: 16:54
 */

use Bitrix\Main\Type\DateTime;

IncludeModuleLangFile(__FILE__);

Class alurate extends CModule
{
	var $MODULE_ID = "alurate";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "Y";

	function alurate()
	{
		$arModuleVersion = array();

		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path."/version.php");

		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		$this->PARTNER_NAME = GetMessage("EXCH_RATE_PARTNER_NAME");
		$this->PARTNER_URI = GetMessage("EXCH_RATE_PARTNER_URI");

		$this->MODULE_NAME = GetMessage("EXCH_RATE_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("EXCH_RATE_MODULE_DESCRIPTION");
	}

	function InstallDB()
	{
		global $DB, $DBType, $APPLICATION;

		if (!$DB->Query("SELECT 'x' FROM b_alutech_exchange_rates", true))
		{
			$errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/alurate/install/".$DBType."/install.sql");
		}

		if (!empty($errors))
		{
			$APPLICATION->ThrowException(implode("", $errors));
			return false;
		}

		$rate = array(
			"'1','EUR','Êóðñ EURO ÍÁÐÁ','2.3027','Y','2017-11-02 17:00:00'",
			"'2','rateAlutec','Êóðñ Àëþòåõ','2.3027','','2017-11-02 17:00:00'"
		);
		foreach ($rate as $inser)
		{
			$strSql = "INSERT INTO b_alutech_exchange_rates(ID, CHARCODE, NAME, RATE, ACTIVE, DATE_UPDATE) VALUES(".$inser.")";
			$DB->Query($strSql, False, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		RegisterModule("alurate");

		$checkDate = DateTime::createFromTimestamp(strtotime('tomorrow 04:01:00'));

		CAgent::AddAgent('exchangeRates::alurateAgent();', 'alurate', 'Y', 86400, '', 'Y', $checkDate->toString(), 100, false, true);

		RegisterModuleDependences("main","OnEndBufferContent","alurate","exchangeRates","changePriceContent");

		CModule::IncludeModule("alurate");

		return true;
	}

	function UnInstallDB()
	{
		global $DB, $DBType, $APPLICATION;

		$errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/alurate/install/".$DBType."/uninstall.sql");
		if (!empty($errors))
		{
			$APPLICATION->ThrowException(implode("", $errors));
			return false;
		}

		CAgent::RemoveModuleAgents('alurate');

		UnRegisterModuleDependences("main","OnEndBufferContent","alurate","exchangeRates","changePriceContent");

		UnRegisterModule("alurate");
		return true;
	}

	function InstallEvents()
	{
		return true;
	}

	function UnInstallEvents()
	{
		return true;
	}

	function InstallFiles($arParams = array())
	{
		CopyDirFiles(
			$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/alurate/install/admin",
			$_SERVER["DOCUMENT_ROOT"]."/bitrix/admin",
			true, true
		);
		CopyDirFiles(
			$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/alurate/install/js",
			$_SERVER["DOCUMENT_ROOT"]."/bitrix/js",
			true, true
		);
		return true;
	}

	function UnInstallFiles()
	{
		DeleteDirFiles(
			$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/alurate/install/admin/",
			$_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
		DeleteDirFilesEx("/bitrix/js/alurate");//js

		return true;
	}

	function DoInstall()
	{
		global $APPLICATION, $DOCUMENT_ROOT;

		if (!IsModuleInstalled("alurate"))
		{
			$this->InstallDB();
			$this->InstallEvents();
			$this->InstallFiles();
			$APPLICATION->IncludeAdminFile(GetMessage("EXCH_RATE_INSTALL_TITLE"), $DOCUMENT_ROOT . "/bitrix/modules/alurate/install/step.php");
		}
	}

	function DoUninstall()
	{
		global $DOCUMENT_ROOT, $APPLICATION;
		$this->UnInstallDB();
		$this->UnInstallEvents();
		$this->UnInstallFiles();
		$APPLICATION->IncludeAdminFile(GetMessage("EXCH_RATE_UNINSTALL_TITLE"), $DOCUMENT_ROOT . "/bitrix/modules/alurate/install/unstep.php");
	}
}
?>
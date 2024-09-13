<?
IncludeModuleLangFile(__FILE__);

Class iggle_stat extends CModule
{
	const MODULE_ID = 'iggle.stat';
	var $MODULE_ID = 'iggle.stat'; 
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $strError = '';

	function __construct()
	{
		$arModuleVersion = array();
		include(dirname(__FILE__)."/version.php");
		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		$this->MODULE_NAME = GetMessage("IGGLE_STAT_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("IGGLE_STAT_MODULE_DESC");

		$this->PARTNER_NAME = GetMessage("IGGLE_STAT_PARTNER_NAME");
		$this->PARTNER_URI = GetMessage("IGGLE_STAT_PARTNER_URI");
	}

	function InstallDB($arParams = array())
	{
		global $DB;

		if (file_exists($f = dirname(__FILE__).'/db/install.sql'))
		{
			foreach($DB->ParseSQLBatch(file_get_contents($f)) as $sql)
				$DB->Query($sql);
		}
		return true;
	}

	function UnInstallDB($arParams = array())
	{
		global $DB;

		if (file_exists($f = dirname(__FILE__).'/db/uninstall.sql'))
		{
			foreach($DB->ParseSQLBatch(file_get_contents($f)) as $sql)
				$DB->Query($sql);
		}
		return true;
	}

	function InstallFiles()
	{
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/local/modules/".self::MODULE_ID."/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/local/modules/".self::MODULE_ID."/install/themes/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes", true, true);
		return true;
	}

	function UnInstallFiles()
	{
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/local/modules/".self::MODULE_ID."/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/local/modules/".self::MODULE_ID."/install/themes/.default", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/.default");
		DeleteDirFilesEx("/local/themes/.default/icons/".self::MODULE_ID);
		return true;
	}

	function InstallAgents()
	{
		CAgent::AddAgent("\\Iggle\\Stat\\Agent::cleanUp();", self::MODULE_ID, "Y", 86400, "", "Y", ConvertTimeStamp(strtotime(date("Y-m-d 01:00:00", time() + 86400)), "FULL"));
		return true;
	}

	function UnInstallAgents()
	{
		CAgent::RemoveAgent("\\Iggle\\Stat\\Agent::cleanUp();", self::MODULE_ID);
		return true;
	}

	function SetDefaultOptions()
	{
		require($_SERVER["DOCUMENT_ROOT"]."/local/modules/".self::MODULE_ID."/install/default_options.php");
		return true;
	}

	function InstallData()
	{
		require($_SERVER["DOCUMENT_ROOT"]."/local/modules/".self::MODULE_ID."/install/default_data.php");
		return true;
	}

	function DoInstall()
	{
		global $APPLICATION;
		$this->InstallDB();
		$this->InstallFiles();
		RegisterModule(self::MODULE_ID);
		$this->SetDefaultOptions();
		$this->InstallAgents();
		$this->InstallData();
		RegisterModuleDependences('main', 'OnEpilog', self::MODULE_ID);
	}

	function DoUninstall()
	{
		global $APPLICATION;
		UnRegisterModuleDependences('main', 'OnEpilog', self::MODULE_ID);
		$this->UnInstallAgents();
		UnRegisterModule(self::MODULE_ID);
		$this->UnInstallDB();
		$this->UnInstallFiles();
	}
}
?>
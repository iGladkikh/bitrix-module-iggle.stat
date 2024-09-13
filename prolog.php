<?
IncludeModuleLangFile(__FILE__);
define("ADMIN_MODULE_NAME", "iggle.stat");

function moduleCheck()
{
	$res = \Bitrix\Main\Loader::includeSharewareModule("iggle.stat");
	switch($res)
	{
		case 0:
		ShowError('Module "iggle.stat" is not installed :(');
		//die();
			break;
		case 1:
			$result = true;
			break;
		case 2:
			$result = 2;
			break;
		case 3:
		echo ShowError('The trial period of "iggle.stat" module is expired :(');
			$result = false;
			break;
	}
	return $result;
}

moduleCheck();
//Bitrix\Main\Loader::includeModule("iggle.stat");
?>
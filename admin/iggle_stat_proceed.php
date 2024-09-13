<?
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
	define("SET_USER_ONLINE", true);
	define("NO_KEEP_STATISTIC", true);
	define("NO_AGENT_CHECK", true);
	define("NOT_CHECK_PERMISSIONS", true);
	define("NO_AGENT_STATISTIC", "Y");
	define("DisableEventsCheck", true);
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
}
else
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
}
?>
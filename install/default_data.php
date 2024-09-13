<?
if (!Bitrix\Main\Loader::includeModule("iggle.stat"))
	return false;

$arBots = array(
	array("NAME" => "Google", "MASK" => "Google"),
	array("NAME" => "Yandex", "MASK" => "Yandex"),
	array("NAME" => "Mail", "MASK" => "Mail.Ru"),
	array("NAME" => "Wget", "MASK" => "Wget"),
	array("NAME" => "MJ12", "MASK" => "MJ12bot"),
	array("NAME" => "Java", "MASK" => "Java"),
	array("NAME" => "Bot", "MASK" => "Bot"),
	array("NAME" => "Bitrix", "MASK" => "Bitrix"),
	array("NAME" => "MSN", "MASK" => "MSNBot"),
	array("NAME" => "Baiduspider", "MASK" => "Baiduspider"),
	array("NAME" => "bot", "MASK" => "bot"),
	array("NAME" => "NET CLR", "MASK" => "NET CLR"),
	array("NAME" => "Bing", "MASK" => "bingbot"),
	array("NAME" => "Ezooms", "MASK" => "Ezooms"),
);

$dateTime = new \Bitrix\Main\Type\DateTime();

foreach($arBots as $arFields)
{
	$arFields["CREATE_DATE"] = $dateTime;
	$arFields["ACTIVE"] = "Y";
	\Iggle\Stat\BotTable::add($arFields);
}
?>
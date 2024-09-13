<?
if (defined("STAT_OFF") || (defined("NO_KEEP_STATISTIC") && !defined("SET_USER_ONLINE")))
{ 
	return false;
}

global $APPLICATION;

if (!defined("ADMIN_SECTION") && $_SESSION["STAT"]["IS_BANNED"] == "Y")
{
	$connection = \Bitrix\Main\Application::getConnection();
	$connection->disconnect();
	$APPLICATION->RestartBuffer();
	die($_SESSION["STAT"]["MESSAGE"]);
}

\Bitrix\Main\Loader::registerAutoLoadClasses(
	"iggle.stat",
	array(
		"\\Iggle\\Stat\\SessTable" => "lib/sess.php",
		"\\Iggle\\Stat\\BlacklistTable" => "lib/blacklist.php",
	)
);
/****************************************************/

$sess = new \Iggle\Stat\SessTable;

if (defined("SET_USER_ONLINE"))
{
	return $sess->setUserOnline();
}

if (!defined("ADMIN_SECTION"))
{
	$sessId = intval(intval($_SESSION["STAT"]["GUEST_SESS_ID"]) > 0 ? $_SESSION["STAT"]["GUEST_SESS_ID"] : $sess->init());

	if ($_SESSION["STAT"]["USER_ONLINE_PERIOD"] > 0)
	{
		$uploadTime = ($_SESSION["STAT"]["USER_ONLINE_PERIOD"] > 3) ? $_SESSION["STAT"]["USER_ONLINE_PERIOD"]*1000 : 3000;
	}

	if ($sessId > 0)
	{
		$sess->setHit($sessId);
		if ($_SESSION["STAT"]["USER_ONLINE_PERIOD"] > 0)
		{
			CJSCore::Init(array("jquery"));
			$str = '<script type="text/javascript">
			function setOnlineStatus()
			{
				$.post(
					"/bitrix/admin/iggle_stat_proceed.php",
					{"ajax_call":"Y"}
				);
				setTimeout(function() {setOnlineStatus()}, '.$uploadTime.');
			}
			setTimeout(function() {setOnlineStatus()}, '.$uploadTime.');
			</script>';
			$APPLICATION->AddHeadString($str);
		}
	}
}
/****************************************************/

function convertText($text) 
{ 
	return (defined("BX_UTF")) ? $GLOBALS["APPLICATION"]->ConvertCharset($text, "windows-1251", "UTF-8") : $text; 
}

function getPageTitle()
{
	global $APPLICATION;
	//$APPLICATION->AddBufferContent("getPageTitle");
	if ($APPLICATION->GetPageProperty("title"))
		return $APPLICATION->GetPageProperty("title");
	else
		return $APPLICATION->GetTitle();
}

function getPageUri()
{
	$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
	$hostScheme = $request->isHttps() ? "https://" : "http://";
	$host = $request->getHttpHost();
	$query = $request->getRequestUri();
	$result = $hostScheme. $host. $query;
	return $result;
}

function getLink($uri, $title = false, $target = true)
{
	if ($title === false || strlen(trim($title)) <= 0)
		$title = $uri;

	return '<a '.($target === true ? 'target="_blank"' : '').'href="'.$uri.'">'.$title.'</a>';
}

function cutString($string, $maxlen = 50, $etc = "...")
{
	if ($maxlen == 0 || strlen(trim($string)) <= 0)
		return false;

	$result = implode(array_slice(explode('<br>', wordwrap($string, $maxlen, '<br>', false)), 0, 1));
	if ($result != $string)
		$result .= $etc;

	return $result;
}

function cutLink($string, $maxlen = 60, $etc = "...")
{
	if ($maxlen == 0 || strlen(trim($string)) <= 0)
		return false;

	$result = substr($string, 0, $maxlen);
	if (strlen($result) != strlen($string))
		$result .= $etc;

	return $result;
}

?>
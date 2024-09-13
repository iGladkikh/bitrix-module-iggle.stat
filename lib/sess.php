<?
namespace Iggle\Stat;

use Bitrix\Main\Entity;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class SessTable extends Entity\DataManager
{

	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return "i_stat_sess";
	}

	public static function getMap()
	{
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		return array(
			"ID" => array(
				"data_type" => "integer",
				"primary" => true,
				"autocomplete" => true,
				"title" => Loc::getMessage("IGGLE_STAT_SESS_ID"),
			),
			"SITE_ID" => array(
				"data_type" => "string",
				"required" => true,
				"title" => Loc::getMessage("IGGLE_STAT_SESS_SITE_ID"),
			),
			"GUEST_ID" => array(
				"data_type" => "integer",
				"required" => true,
				"title" => Loc::getMessage("IGGLE_STAT_SESS_GUEST_ID"),
			),
			"USER_ID" => array(
				"data_type" => "integer",
				"title" => Loc::getMessage("IGGLE_STAT_SESS_USER_ID"),
			),
			"FIRST_IP" => array(
				"data_type" => "string",
				"title" => Loc::getMessage("IGGLE_STAT_SESS_FIRST_IP"),
			),
			"LAST_IP" => array(
				"data_type" => "string",
				"title" => Loc::getMessage("IGGLE_STAT_SESS_LAST_IP"),
			),
			"USER_AGENT" => array(
				"data_type" => "string",
				"required" => true,
				"title" => Loc::getMessage("IGGLE_STAT_SESS_USER_AGENT"),
			),
			"REFERER" => array(
				"data_type" => "string",
				"title" => Loc::getMessage("IGGLE_STAT_SESS_REFERER"),
			),
			"FIRST_URI" => array(
				"data_type" => "string",
				"title" => Loc::getMessage("IGGLE_STAT_SESS_FIRST_URI"),
			),
			"LAST_URI" => array(
				"data_type" => "string",
				"title" => Loc::getMessage("IGGLE_STAT_SESS_LAST_URI"),
			),
			"FIRST_PAGE_TITLE" => array(
				"data_type" => "string",
				"title" => Loc::getMessage("IGGLE_STAT_SESS_FIRST_PAGE_TITLE"),
			),
			"LAST_PAGE_TITLE" => array(
				"data_type" => "string",
				"title" => Loc::getMessage("IGGLE_STAT_SESS_LAST_PAGE_TITLE"),
			),
			"CREATE_DATE" => array(
				"data_type" => "datetime",
				"title" => Loc::getMessage("IGGLE_STAT_SESS_CREATE_DATE"),
			),
			"LAST_ACTIVITY" => array(
				"data_type" => "datetime",
				"title" => Loc::getMessage("IGGLE_STAT_SESS_LAST_ACTIVITY"),
			),
			"SESSION_TIME" => array(
				"data_type" => "integer",
				"title" => Loc::getMessage("IGGLE_STAT_SESS_SESSION_TIME"),
			),
			"HITS" => array(
				"data_type" => "integer",
				"title" => Loc::getMessage("IGGLE_STAT_SESS_HITS"),
			),
			"IS_BANNED" => array(
				"data_type" => "boolean",
				"title" => Loc::getMessage("IGGLE_STAT_SESS_IS_BANNED"),
				"values" => array("", "Y"),
			),
			"LAST_VISIT" => array(
				"data_type" => "datetime",
				"title" => Loc::getMessage("IGGLE_STAT_SESS_LAST_VISIT"),
			),
			"FUSER_ID" => array(
				"data_type" => "integer",
				"title" => Loc::getMessage("IGGLE_STAT_SESS_BASKET"),
			),
			"IS_ONLINE" => array(
				"data_type" => "boolean",
				"title" => Loc::getMessage("IGGLE_STAT_SESS_IS_ONLINE"),
				"values" => array("N", "Y"),
				"expression" => array(
					"CASE WHEN %s > ".$helper->addSecondsToDateTime("(-%%USER_IS_ONLINE_INTERVAL%%)")." THEN \"Y\" ELSE \"N\" END",
					"LAST_ACTIVITY",
				),
				"options" => array(
					"USER_IS_ONLINE_INTERVAL" => 30 // sec
				)
			),
		);
	}

	public static function init()
	{
		if (!$_SESSION["STAT"]["GUEST_ID"])
		{
			$uri = getPageUri();
			$dateTime = new DateTime();
			$IP = self::getRealIP();
			$title = htmlspecialchars(getPageTitle());
			$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
			$cookieGuestId = intval($request->getCookie("STAT_GUEST_ID"));
			$cookieLastVisit = $request->getCookie("STAT_LAST_VISIT");
			$arBot = self::isBot();
			$connection = \Bitrix\Main\Application::getConnection();
			$helper = $connection->getSqlHelper();
			$tableName = self::getTableName();

			if (is_array($arBot))
			{
				$botStatEnable = \Bitrix\Main\Config\Option::get("iggle.stat", "BOT_STAT_ENABLE");
				if ($arBot["ACTIVE"] == "Y" && $botStatEnable == "Y")
				{
					$arFields = array(
						"SITE_ID" => SITE_ID,
						"BOT_NAME" => $arBot["NAME"],
						"IP" => $IP,
						"PAGE_TITLE" => $title,
						"URI" => $uri,
						"IS_404" => ERROR_404 == "Y" ? "Y" : "",
						"CREATE_DATE" => $dateTime,
					);
					$connection->add("i_stat_bot_hit", $arFields);
				}

				$update = $helper->prepareUpdate("i_stat_bot", array("LAST_VISIT" => $dateTime));
				$sql =
					"UPDATE i_stat_bot".
					" SET ".$update[0].
					" WHERE ID = ".$arBot["ID"]
				;
				$connection->queryExecute($sql);
				//$connection->queryExecute("UPDATE i_stat_bot SET LAST_VISIT = ".$helper->getDateTimeToDbFunction($dateTime, 0)." WHERE ID = ".$arBot["ID"]);

				return false;
			}

			if (defined("ERROR_404") && ERROR_404 == "Y")
				return false;

			$arFields = array(
				"GUEST_ID" => $cookieGuestId,
				"SITE_ID" => SITE_ID,
				"FIRST_IP" => $IP,
				"LAST_IP" => $IP,
				"FIRST_URI" => $uri,
				"LAST_URI" => $uri,
				"FIRST_PAGE_TITLE" => $title,
				"LAST_PAGE_TITLE" => $title,
				"REFERER" => (strpos($_SERVER["HTTP_REFERER"], $_SERVER['HTTP_HOST']) !== false) ? "" : $_SERVER["HTTP_REFERER"],
				"USER_AGENT" => $_SERVER["HTTP_USER_AGENT"],
				"CREATE_DATE" => $dateTime,
				"LAST_ACTIVITY" => $dateTime,
				"SESSION_TIME" => 0,
				"HITS" => 1,
				"LAST_VISIT" => $cookieLastVisit ? new DateTime($cookieLastVisit) : null,
			);

			$sessId = intval($connection->add($tableName, $arFields));

			if ($sessId > 0)
			{
				$GUEST_ID = ($cookieGuestId > 0) ? $cookieGuestId : $sessId;

				$_SESSION["STAT"] = array(
					"GUEST_ID" => $GUEST_ID,
					"GUEST_SESS_ID" => $sessId,
					"RUN_TIME" => MakeTimeStamp($dateTime->toString()),
					"HITS" => 1,
					"USER_STAT_ENABLE" => \Bitrix\Main\Config\Option::get("iggle.stat", "USER_STAT_ENABLE"),
					"USER_ONLINE_PERIOD" => \Bitrix\Main\Config\Option::get("iggle.stat", "ONLINE_PERIOD"),
				);

				$GLOBALS["APPLICATION"]->set_cookie("STAT_LAST_VISIT", $dateTime->toString());

				if ($cookieGuestId <= 0)
				{
					$connection->queryExecute("UPDATE i_stat_sess SET GUEST_ID = ID WHERE ID = ".$sessId);
					$GLOBALS["APPLICATION"]->set_cookie("STAT_GUEST_ID", $GUEST_ID);
				}

				return $sessId;
			}
			return false;
		}
		return false;
	}

	public static function setHit($sessId = 0)
	{
		if (intval($sessId) <= 0)
			return false;

		$dateTime = new DateTime();
		$tableName = self::getTableName();
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();
		$arBan = self::isBanned();

		if (is_array($arBan))
		{
			global $APPLICATION;
			$arFields["IS_BANNED"] = "Y";
			$update = $helper->prepareUpdate($tableName, $arFields);
			$sql =
				"UPDATE ".$tableName.
				" SET ".$update[0].
				" WHERE ID = ".$sessId
			;	
			$connection->queryExecute($sql);

			$update = $helper->prepareUpdate("i_stat_blacklist", array("LAST_VISIT" => $dateTime));
			$sql =
				"UPDATE i_stat_blacklist".
				" SET ".$update[0].
				" WHERE ID = ".$arBan["ID"]
			;
			$connection->queryExecute($sql);

			//$connection->queryExecute("UPDATE i_stat_blacklist SET LAST_VISIT = ".$helper->getDateTimeToDbFunction($dateTime, 0)." WHERE ID = ".$arBan["ID"]);
			$connection->disconnect();
			$_SESSION["STAT"]["IS_BANNED"] = "Y";
			$_SESSION["STAT"]["MESSAGE"] = $arBan["MESSAGE"];
			$APPLICATION->RestartBuffer();
			die($arBan["MESSAGE"]);
		}

		$uri = getPageUri();
		$title = htmlspecialchars(getPageTitle());
		$IP = self::getRealIP();

		if ($_SESSION["STAT"]["USER_STAT_ENABLE"] == "Y")
		{
			$arHitFields = array(
				"SESSION_ID" => $sessId,
				"SITE_ID" => SITE_ID,
				"IP" => $IP,
				"PAGE_TITLE" => $title,
				"URI" => $uri,
				"IS_404" => (defined("ERROR_404") && ERROR_404 == "Y") ? "Y" : "",
				"CREATE_DATE" => $dateTime,
			);
	
			$connection->add("i_stat_hit", $arHitFields);
		}

		if ($_SESSION["STAT"]["HITS"] > 1)
		{
			$arSessFields = array(
				"LAST_IP" => $IP,
				"LAST_PAGE_TITLE" => $title,
				"LAST_URI" => $uri,
				"HITS" => $_SESSION["STAT"]["HITS"],
				"SESSION_TIME" => MakeTimeStamp($dateTime->toString()) - $_SESSION["STAT"]["RUN_TIME"],
				"LAST_ACTIVITY" => $dateTime,
			);
	
			global $USER;
			if ($USER->IsAuthorized())
			{
				$arSessFields["USER_ID"] = $USER->GetID();
			}
			if ($_SESSION["SALE_USER_ID"] > 0)
			{
				$arSessFields["FUSER_ID"] = $_SESSION["SALE_USER_ID"];
			}

			$update = $helper->prepareUpdate($tableName, $arSessFields);
			$sql =
				"UPDATE ".$tableName.
				" SET ".$update[0].
				" WHERE ID = ".$sessId
			;	
			$connection->queryExecute($sql);
		}
		$_SESSION["STAT"]["HITS"] += 1;
	}

	public static function setUserOnline()
	{
		$sessId = intval($_SESSION["STAT"]["GUEST_SESS_ID"]);

		if ($sessId > 0)
		{
			$dateTime = new DateTime();
			$sessTime = MakeTimeStamp($dateTime->toString()) - $_SESSION["STAT"]["RUN_TIME"];
			$tableName = self::getTableName();
			$connection = \Bitrix\Main\Application::getConnection();
			$helper = $connection->getSqlHelper();

			$arFields = array(
				"LAST_ACTIVITY" => $dateTime,
				"SESSION_TIME" => $sessTime,
			);

			$update = $helper->prepareUpdate($tableName, $arFields);
			$sql =
				"UPDATE ".$tableName.
				" SET ".$update[0].
				" WHERE ID = ".$sessId
			;	
			$connection->queryExecute($sql);
		}
	}

	public static function getUserBrowser($agent = false) 
	{
		$agent = $agent ? $agent : $_SERVER["HTTP_USER_AGENT"];
		preg_match("/(Trident|MSIE|Firefox|Chrome|Opera|YaBrowser|Version|Netscape|Konqueror|SeaMonkey|Camino|Minefield|Iceweasel|K-Meleon|Maxthon)(?:\/| )([0-9.]+)/", $agent, $browser_info);		
		list(,$browser,$version) = $browser_info;

		if ($browser == "MSIE")
		{ 
			preg_match("/(Maxthon|Avant Browser|MyIE2)/i", $agent, $ie); 
			if ($ie)
			{
				return $ie[1]." based on IE ".$version;
			}
			else 
			{
				return "Internet Explorer ".$version;
			}
		}
		if ($browser == "Firefox")
		{ 
			preg_match("/(Flock|Navigator|Epiphany)\/([0-9.]+)/", $agent, $ff);
			if ($ff)
			{
				return $ff[1]." ".$ff[2];
			}
        }
		if ($browser == "Opera")
		{
			if (preg_match("/Opera Mini\/([0-9.]+)/i", $agent, $opera))
			{
				return "Opera Mini ".$opera[1];
			}
			if ($version == "9.80")
			{
				return "Opera ".substr($agent, -5);
			}
			if (preg_match("/Opera ([0-9.]+)/i", $agent, $opera))
			{
				return "Opera ".$opera[1];
			}
		}
        if ($browser == "Version")
		{
			return "Safari ".$version;
		}
		if ($browser == "Trident") 
		{
			if (preg_match("/(rv:)(?:\/|)([0-9.]+)/", $agent, $ie))
			{
				return "Internet Explorer ".$ie[2];
			}
			else 
			{
				return "Internet Explorer";
			}
		}
		if ($browser == "YaBrowser") 
		{
			return "Yandex.Browser"." ".$version;
		}
		if (!$browser && strpos($agent, "Gecko"))
		{
			return "Browser based on Gecko";
		} 
		else
		{
			return $browser." ".$version;
		}
	}

	function getUserOS($agent = false)
	{
		$agent = $agent ? $agent : $_SERVER["HTTP_USER_AGENT"];
		$arOS = array (
			"Android" => "/Android/",
			"iOS&nbsp;<sup class=\"iggle_stat_info\">iPhone</sup>" => "/iPhone/",
			"iOS&nbsp;<sup class=\"iggle_stat_info\">iPad</sup>" => "/iPad/",
			"Mac OS X" => "/Mac OS X/",
			"Macintosh" => "/(Mac_PowerPC)|(Macintosh)/",
			"Linux" => "/(Linux)|(X11)/",
			"Windows&nbsp;8.1" => "/Windows NT 6.3/",
			"Windows&nbsp;8" => "/Windows NT 6.2/",
			"Windows Phone" => "/Windows Phone/",
			"Windows&nbsp;7" => "/Windows NT 6.1/",
			"Windows Vista" => "/Windows NT 6.0/",
			"Windows Server 2003" => "/(Windows NT 5.2)/",
			"Windows&nbsp;XP" => "/(Windows NT 5.1)|(Windows XP)|(Win32)/",
			"Windows 2000" => "/(Windows NT 5.0)|(Windows 2000)/",
			"Windows 98" => "/(Windows 98)|(Win98)/",
			"Windows 95" => "/(Windows 95)|(Win95)|(Windows_95)/",
			"Windows 3.11" => "/Win16/",
			"Windows NT 4.0" => "/(Windows NT 4.0)|(WinNT4.0)|(WinNT)|(Windows NT)/",
			"Windows ME" => "/Windows ME|Win 9x 4.90/",
			"Windows CE" => "/Windows CE/",
			"Windows Mobile 2003" => "/Windows CE 4.21/",
			"Symbian" => "/Symbian/",
			"Open BSD" => "/OpenBSD/",
			"Sun OS" => "/SunOS/",
			"QNX" => "/QNX/",
			"BeOS" => "/BeOS/",
			"OS/2" => "/OS\/2/",
		);
	
		foreach($arOS as $os => $pattern)
		{
			if (preg_match($pattern, $agent))
			{
				return $os;
			}
		}
		return false;
	}

	public static function isBot($agent = false) 
	{
		$agent = $agent ? $agent : $_SERVER["HTTP_USER_AGENT"];
		$dbRes = BotTable::getList();
		while($arBot = $dbRes->fetch())
		{
			if (strpos($agent, $arBot["MASK"]) !== false)
				return $arBot;
		}
	}

	public static function isBanned($guestId = false, $IP = false) 
	{
		$guestId = $guestId ? $guestId : $_SESSION["STAT"]["GUEST_ID"];
		$IP = $IP ? $IP : self::getRealIP();
		$dateTime = new DateTime();
		$arFilter = array(
			"ACTIVE" => "Y",
			array(
				"LOGIC" => "OR",
				array(">=ACTIVE_TO" => $dateTime->toString()),
				array("ACTIVE_TO" => null),
			),
			array(
				"LOGIC" => "OR",
				array("GUEST_ID" => $guestId), 
				array("IP" => $IP),
			),
		);
		$dbRes = BlacklistTable::getList(array("filter" => $arFilter, "limit" => 1));
		if ($arRes = $dbRes->fetch())
			return $arRes;
		else
			return false;
	}

	public static function getRealIP()
	{
		if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"]))
		{
			$ips = explode (", ", $_SERVER["HTTP_X_FORWARDED_FOR"]);
			for ($i = 0; $i < count($ips); $i++)
			{
				if (!preg_match("/^(10|172\\.16|192\\.168)\\./", $ips[$i]))
				{
					$ip = $ips[$i];
					break;
				}
			}
		}
		return ($ip ? $ip : $_SERVER["REMOTE_ADDR"]);
	}

	public static function getSearchQuery($referer)
	{
		if (strlen($referer) <= 0)
			return false;

		$name = false;
		$query = false;

		$arBots = array(
			"Yandex" =>  array("yandex.", "text"),
			"Google" => array("google.", "q"),
			"Mail.ru" => array("mail.", "q"),
			"Rambler" => array("rambler.", "query"),
			"Yahoo" => array("yahoo.", "p"),
			"Bing" => array("bing.", "q"),
		);
	
		$arUri = parse_url($referer);

		foreach ($arBots as $name => $data)
		{
			if (strpos($arUri["host"], $data[0]) !== false)
			{
				if (strlen($arUri["query"]) > 0)
				{
					parse_str($arUri["query"], $arQuery);
					$query = htmlspecialchars(urldecode($arQuery[$data[1]]));
				}
				return array("BOT" => $name, "QUERY" => $query);
			}
		}
		return false;
	}

	public static function getRefererDomain($referer)
	{
		if (strlen($referer) <= 0)
			return false;

		$arUri = parse_url($referer);
		$domain = $arUri["host"];
		if (strpos($domain, "xn--") == 0 && is_callable(idn_to_utf8))
		{
			if (!defined("BX_UTF"))
			{
				$domain = $GLOBALS["APPLICATION"]->ConvertCharset($domain, "windows-1251", "UTF-8");
				$domain = idn_to_utf8($domain);
				$domain = $GLOBALS["APPLICATION"]->ConvertCharset($domain, "UTF-8", "windows-1251");
			}
			else
			{
				$domain = idn_to_utf8($domain);
			}
		}
		return $domain;
	}
}
?>
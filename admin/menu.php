<?
IncludeModuleLangFile(__FILE__);
$MOD_RIGHT = $APPLICATION->GetGroupRight("iggle.stat");
if($MOD_RIGHT >= "R")
{
	$aMenu = array(
		"parent_menu" => "global_menu_services",
		"section" => "iggle_stat",
		"sort" => 10000,
		"text" => GetMessage("IGGLE_STAT_MENU_STAT"),
		"title" => GetMessage("IGGLE_STAT_MENU_STAT_ALT"),
		"icon" => "iggle_stat_menu_icon",
		"page_icon" => "iggle_stat_page_icon",
		"items_id" => "iggle_stat",
		"items" => array(
			array(
				"text" => GetMessage("IGGLE_STAT_MENU_SESS"),
				"url" => "iggle_stat_sess.php?lang=".LANG,
				"title" => GetMessage("IGGLE_STAT_MENU_SESS_ALT"),
			),
			array(
				"text" => GetMessage("IGGLE_STAT_MENU_HIT"),
				"url" => "iggle_stat_hit.php?lang=".LANG,
				"title" => GetMessage("IGGLE_STAT_MENU_HIT_ALT"),
			),
			array(
				"text" => GetMessage("IGGLE_STAT_MENU_BLACKLIST"),
				"url" => "iggle_stat_blacklist.php?lang=".LANG,
				"title" => GetMessage("IGGLE_STAT_MENU_BLACKLIST_ALT"),
				"more_url" => array("iggle_stat_blacklist_edit.php"),
			),
			array(
				"text" => GetMessage("IGGLE_STAT_MENU_BOT"),
				"title" => GetMessage("IGGLE_STAT_MENU_BOT_ALT"),
				"items_id" => "iggle_stat_bot",
				"items" => array(
					array(
						"text" => GetMessage("IGGLE_STAT_MENU_BOT_HIT"),
						"url" => "iggle_stat_bot_hit.php?lang=".LANG,
						"title" => GetMessage("IGGLE_STAT_MENU_BOT_HIT_ALT"),
					),
					array(
						"text" => GetMessage("IGGLE_STAT_MENU_BOT_DETECT"),
						"url" => "iggle_stat_bot.php?lang=".LANG,
						"title" => GetMessage("IGGLE_STAT_MENU_BOT_DETECT_ALT"),
						"more_url" => array("iggle_stat_bot_edit.php"),
					),
				)
			)
		)
	);
	return $aMenu;
}
return false;
?>
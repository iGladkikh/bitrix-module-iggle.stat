<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/local/modules/iggle.stat/prolog.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/local/modules/iggle.stat/admin/menu.php");
IncludeModuleLangFile(__FILE__);

$sModulePermissions = $APPLICATION->GetGroupRight("iggle.stat");
if ($sModulePermissions == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$class = new \Iggle\Stat\SessTable;
$arTableFields = $class->getMap();
$id = intval($ID);
$arSess = $class->getById($id)->fetch();

$sessTime = MakeTimeStamp($arSess["LAST_ACTIVITY"]) - MakeTimeStamp($arSess["CREATE_DATE"]);

$geoBase = new \Iggle\Stat\IPGeoBase();
$geoData = $geoBase->getRecord($arSess["LAST_IP"]);

if ($geoData["REGION"] == $geoData["CITY"])
	unset($geoData["REGION"]);

if (empty($geoData["COUNTRY"]))
	$geoData["COUNTRY"] = GetMessage("IGGLE_STAT_NONЕ");

if (empty($arSess["LAST_VISIT"]))
	$arSess["LAST_VISIT"] = GetMessage("IGGLE_STAT_FIRST_VISIT");

$arSess["GUEST_ID"] = $arSess["GUEST_ID"]." [".getLink("iggle_stat_blacklist_edit.php?GUEST_ID=".$arSess["GUEST_ID"]."&lang=".LANG, GetMessage("IGGLE_STAT_ACTION_BAN"))."]";

$arSess["LAST_IP"] = ($arSess["LAST_IP"] != $arSess["FIRST_IP"]) ? 
	GetWhoisLink($arSess["LAST_IP"])." [".getLink("iggle_stat_blacklist_edit.php?IP=".$arSess["LAST_IP"]."&lang=".LANG, GetMessage("IGGLE_STAT_ACTION_BAN"))."]" : 
	GetMessage("IGGLE_STAT_SAME_IP")
;

$arSess["FIRST_IP"] = GetWhoisLink($arSess["FIRST_IP"])." [".getLink("iggle_stat_blacklist_edit.php?IP=".$arSess["FIRST_IP"]."&lang=".LANG, GetMessage("IGGLE_STAT_ACTION_BAN"))."]";
$arSess["FIRST_URI"] = getLink($arSess["FIRST_URI"], cutLink($arSess["FIRST_URI"]));
//$arSess["PAGE_TITLE"] = getLink($arSess["LAST_URI"], $arSess["PAGE_TITLE"]);
$arSess["LAST_URI"] = getLink($arSess["LAST_URI"], cutLink($arSess["LAST_URI"]));

if (!empty($arSess["USER_ID"]))
{
	$arUser = \Bitrix\Main\UserTable::getById($arSess["USER_ID"])->fetch();
	$arSess["USER_ID"] = "[".getLink("user_edit.php?lang=".LANG."&ID=".$arSess["USER_ID"], $arSess["USER_ID"])."] ".$arUser["LOGIN"];
}
else
{
	$arSess["USER_ID"] = GetMessage("IGGLE_STAT_NOT_AUTORIZE");
}

$arSess["SESSION_TIME"] = date("H:i:s", mktime(0, 0, $arSess["SESSION_TIME"]));
$arSess["IS_BANNED"] = ($arSess["IS_BANNED"] === "Y") ? GetMessage("IGGLE_STAT_MESS_Y") : GetMessage("IGGLE_STAT_MESS_N");

unset($arSess["ID"]);
$APPLICATION->SetTitle(GetMessage("IGGLE_STAT_TITLE").$id);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_popup_admin.php");
?>
<h2><?=GetMessage("IGGLE_STAT_TITLE")?><?=$id?></h2>
<table class="edit-table" cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td>
			<table cellspacing="0" cellpadding="0" border="0" class="internal">
			<?foreach($arSess as $key => $val):?>
				<tr>
					<td valign="top" nowrap><?=$arTableFields[$key]["title"]?>:</td>
					<td><?=$val?></td>
				</tr>
				<?if ($key == "LAST_IP"):?>
					<?foreach($geoData as $k => $v):?>
						<?if (!empty($v) && !in_array($k, array("RANGE", "LAT", "LONG"))):?>
						<tr>
							<td valign="top" nowrap><?=GetMessage("IGGLE_STAT_".$k)?>:</td>
							<td><?=$v?> <?if ($k == "CITY") echo "[".getLink("http://maps.yandex.ru/?ll=".$geoData["LONG"].",".$geoData["LAT"]."&z=11", GetMessage("IGGLE_STAT_SEE_MAP"))."]"?></td>
						</tr>
						<?endif?>
					<?endforeach?>
				<?endif?>
				<?if ($key == "USER_AGENT"):?>
					<tr>
						<td valign="top" nowrap><?=GetMessage("IGGLE_STAT_BROWSER")?>:</td>
						<td><?=Iggle\Stat\SessTable::getUserBrowser($val)?></td>
					</tr>
					<?$arSearch = \Iggle\Stat\SessTable::getSearchQuery($arSess["REFERER"]);?>
					<tr>
						<td valign="top" nowrap><?=GetMessage("IGGLE_STAT_BOT")?>:</td>
						<td><?=$arSearch["BOT"]?></td>
					</tr>
					<tr>
						<td valign="top" nowrap><?=GetMessage("IGGLE_STAT_QUERY")?>:</td>
						<td><?=$arSearch["QUERY"]?></td>
					</tr>
				<?endif?>
			<?endforeach?>
			</table>
		</td>
	</tr>
</table>
<p>
	<input type="button" onClick="window.close()" value="<?=GetMessage("IGGLE_STAT_ACTION_CLOSE")?>">
</p>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_popup_admin.php")?>
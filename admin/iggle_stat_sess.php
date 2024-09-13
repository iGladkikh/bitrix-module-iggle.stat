<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/local/modules/iggle.stat/prolog.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/local/modules/iggle.stat/admin/menu.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/local/modules/iggle.stat/admin/iggle_stat_sess_detail.php");

if ($saleModule = \Bitrix\Main\Loader::includeModule("sale"))
{
	$serverName = array();
	$dbSite = CSite::GetList(($b = "sort"), ($o = "asc"), array());
	while ($arSite = $dbSite->Fetch())
	{
		$serverName[$arSite["LID"]] = $arSite["SERVER_NAME"];
		if (strlen($serverName[$arSite["LID"]]) <= 0)
		{
			if (defined("SITE_SERVER_NAME") && strlen(SITE_SERVER_NAME) > 0)
				$serverName[$arSite["LID"]] = SITE_SERVER_NAME;
			else
				$serverName[$arSite["LID"]] = COption::GetOptionString("main", "server_name", "");
		}
	}
}

$sModulePermissions = $APPLICATION->GetGroupRight("iggle.stat");
if ($sModulePermissions == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$class = new \Iggle\Stat\SessTable;
$arTableFields = $class->getMap();
$tableId = $class->getTableName();

$by = $by ? $by : "ID";
$order = $order ? $order : "desc";
$oSort = new CAdminSorting($tableId, $by, $order);
$lAdmin = new CAdminList($tableId, $oSort);

$arFilterFields = array(
	"find_ID",
	"find_SITE_ID",
	"find_GUEST_ID",
	"find_CREATE_DATE_1",
	"find_CREATE_DATE_2",
	"find_IS_ONLINE",
);


$lAdmin->InitFilter($arFilterFields);

$arFilter = array(
	"ID" => $find_ID,
	"SITE_ID" => $find_SITE_ID,
	"GUEST_ID" => $find_GUEST_ID,
);


if ($find_IS_ONLINE === "Y")
{
	$arFilter["IS_ONLINE"] = "Y";
}


if(CheckDateTime($find_CREATE_DATE_1))
	$arFilter[">=CREATE_DATE"] = $find_CREATE_DATE_1." 00:00:00";
if(CheckDateTime($find_CREATE_DATE_2))
	$arFilter["<=CREATE_DATE"] = $find_CREATE_DATE_2." 23:59:59";

$rsData = $class->getList(array("order" => array($by => $order), "filter" => array_filter($arFilter)));
$rsData = new CAdminResult($rsData, $tableId);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("IGGLE_STAT_NAV")));

$arHeaders = array(
	array(
		"id" => "ID", 
		"content" => $arTableFields["ID"]["title"], 
		"sort" => "ID", 
		"align" => "right", 
		"default" => true
	),
	array(
		"id" => "SITE_ID", 
		"content" => $arTableFields["SITE_ID"]["title"], 
		"sort" => "SITE_ID", 
		"default" => true,
		"title" => "",
	),
	array(
		"id" => "GUEST_ID", 
		"content" => $arTableFields["GUEST_ID"]["title"], 
		"sort" => "GUEST_ID", 
		"default" => true
	),
	array(
		"id" => "LAST_IP", 
		"content" => "IP", 
		"default" => true
	),
	array(
		"id" => "COUNTRY", 
		"content" => GetMessage("IGGLE_STAT_COUNTRY"), 
		"default" => true
	),
	array(
		"id" => "CITY", 
		"content" => GetMessage("IGGLE_STAT_CITY"), 
		"default" => true
	),
	array(
		"id" => "OS", 
		"content" => GetMessage("IGGLE_STAT_OS"), 
		"default" => true
	),
	array(
		"id" => "BROWSER", 
		"content" => GetMessage("IGGLE_STAT_BROWSER"), 
		"default" => true
	),
	array(
		"id" => "LAST_PAGE_TITLE", 
		"content" => $arTableFields["LAST_PAGE_TITLE"]["title"], 
		"default" => true
	),
	array(
		"id" => "HITS", 
		"content" => $arTableFields["HITS"]["title"],
		"sort" => "HITS",
		"align" => "right", 
		"default" => true
	),
	array(
		"id" => "CREATE_DATE", 
		"content" => $arTableFields["CREATE_DATE"]["title"], 
		"sort" => "SESSION_START", 
		"default" => true
	),
	array(
		"id" => "LAST_ACTIVITY", 
		"content" => $arTableFields["LAST_ACTIVITY"]["title"], 
		"sort" => "LAST_ACTIVITY", 
		"default" => true
	),
	array(
		"id" => "SESSION_TIME",
		"content" => $arTableFields["SESSION_TIME"]["title"],
		"sort" => "SESSION_TIME", 
		"default" => true
	),
	array(
		"id" => "REFERER", 
		"content" => $arTableFields["REFERER"]["title"], 
		"default" => true
	),
	array(
		"id" => "QUERY", 
		"content" => GetMessage("IGGLE_STAT_QUERY"), 
		"default" => true
	)
);

if ($saleModule)
{
	$arHeaders[] = array(
		"id" => "FUSER_ID", 
		"content" => $arTableFields["FUSER_ID"]["title"], 
		"default" => true
	);
}

$lAdmin->AddHeaders($arHeaders);

//$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

$geoBase = new \Iggle\Stat\IPGeoBase();

while($arRes = $rsData->NavNext(true, "f_"))
{

	$arGeo = $geoBase->getRecord($f_LAST_IP);
	$arSearch = $class->getSearchQuery($f_REFERER);

	$row = $lAdmin->AddRow($f_ID, $arRes);
	if (!empty($f_USER_ID))
	{
		$f_GUEST_ID = $f_GUEST_ID."<nobr> [".getLink("user_edit.php?lang=".LANG."&ID=".$f_USER_ID, $f_USER_ID)."]</nobr>";
	}
	$row->AddViewField("GUEST_ID", empty($f_LAST_VISIT) ? $f_GUEST_ID.'&nbsp;<sup class="iggle_stat_info"><em>'.GetMessage("IGGLE_STAT_NEW_GUEST").'</em></sup>' : $f_GUEST_ID.'&nbsp;<sup class="iggle_stat_info"><acronym title="'.$arTableFields["LAST_VISIT"]["title"].': '.$f_LAST_VISIT.'">'.$f_LAST_VISIT->format('d.m.y').'</acronym></sup>');
	$row->AddViewField("LAST_IP", GetWhoisLink($f_LAST_IP));
	$row->AddViewField("COUNTRY", $arGeo["COUNTRY"] ? $arGeo["COUNTRY"] : ('<span class="iggle_stat_no_data">'.GetMessage("IGGLE_STAT_NONЕ").'</span>'));
	$row->AddViewField("CITY", $arGeo["CITY"] ? $arGeo["CITY"] : '<span class="iggle_stat_info">'.GetMessage("IGGLE_STAT_NONE").'</span>');
	$row->AddViewField("LAST_PAGE_TITLE", getLink($f_LAST_URI, $f_LAST_PAGE_TITLE));
	$row->AddViewField("HITS", getLink("iggle_stat_hit.php?set_filter=Y&find_SESSION_ID=".$f_ID."&lang=".LANG, $f_HITS, false));
	$row->AddViewField("SESSION_TIME", date("H:i:s", mktime(0, 0, intval($f_SESSION_TIME))));
	if ($arSearch["BOT"])
	{
		$row->AddViewField("REFERER", getLink($f_REFERER, $arSearch["BOT"]));
	}
	else
	{
		$domain = $class->getRefererDomain($f_REFERER);
		$row->AddViewField("REFERER", getLink($f_REFERER,  $domain));
	}
	$row->AddViewField("QUERY", ($arSearch["BOT"] && !$arSearch["QUERY"]) ? ('<span class="iggle_stat_no_data">'.GetMessage("IGGLE_STAT_NONЕ").'</span>') : $arSearch["QUERY"]);
	$row->AddViewField("OS", ($OS = $class->getUserOS($f_USER_AGENT)) ? $OS : ('<span class="iggle_stat_no_data">'.GetMessage("IGGLE_STAT_NONЕ").'</span>'));
	$row->AddViewField("BROWSER", (trim($BROWSER = $class->getUserBrowser($f_USER_AGENT))) ? $BROWSER : ('<span class="iggle_stat_no_data">'.GetMessage("IGGLE_STAT_NONЕ").'</span>'));

	$arItems = array();
	$arStrItems = array();
	$i = 1;
	if ($saleModule && $f_FUSER_ID > 0)
	{
		$dbBasketItems = CSaleBasket::GetList(
				array(),
				array( 
					"FUSER_ID" => $f_FUSER_ID,
					"ORDER_ID" => "NULL"
				), 
				array("ID", "PRODUCT_ID", "NAME", "QUANTITY", "PRICE", "CURRENCY", "DETAIL_PAGE_URL", "LID")
			);
		while ($arItem = $dbBasketItems->Fetch())
		{
			if (strlen($arItem["DETAIL_PAGE_URL"]) > 0)
			{
				if (strpos($arItem["DETAIL_PAGE_URL"], "htt") !== 0)
					$arItem["DETAIL_PAGE_URL"] = "http://".$serverName[$arItem["LID"]].$arItem["DETAIL_PAGE_URL"];
			}
			$arItems[] = $arItem;
		}

		$arItems = getMeasures($arItems);
		foreach ($arItems as $arItem)
		{
			$measure = (isset($arItem["MEASURE_TEXT"])) ? $arItem["MEASURE_TEXT"] : GetMessage("IGGLE_STAT_SHT");
			$arStrItems[] = "<nobr>".$i++.". ".getLink($arItem["DETAIL_PAGE_URL"], $arItem["NAME"])."</nobr> <nobr>(".$arItem["QUANTITY"]." ".$measure.") - ".SaleFormatCurrency($arItem["PRICE"], $arItem["CURRENCY"])."</nobr>";
		}
		$row->AddViewField("FUSER_ID", implode(",<br/>", $arStrItems));
	}

	$arActions = array();
	$arActions[] = array(
		"ICON"=>"list",
		"TEXT"=>GetMessage("IGGLE_STAT_ACTION_DETAIL"),
		"ACTION"=>"javascript:jsUtils.OpenWindow('iggle_stat_sess_detail.php?lang=".LANG."&ID=".$f_ID."', '700', '550');",
		"DEFAULT" => "Y",
	);
	$row->AddActions($arActions);
}

$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
		array("title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "counter"=>true, "value"=>"0"),
	)
);

$lAdmin->AddAdminContextMenu();
$lAdmin->CheckListMode();
$APPLICATION->SetTitle(GetMessage("IGGLE_STAT_MENU_SESS_ALT"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<form name="filter_form" method="GET" action="<?echo $APPLICATION->GetCurPageParam()?>">
<?
$oFilter = new CAdminFilter(
		$tableId."_filter",
		array(
			$arTableFields["ID"]["title"],
			$arTableFields["SITE_ID"]["title"],
			$arTableFields["GUEST_ID"]["title"],
			$arTableFields["CREATE_DATE"]["title"],
			$arTableFields["IS_ONLINE"]["title"],
	)
);
$oFilter->Begin();
?>
		<tr>
			<td><?=$arTableFields["ID"]["title"]?>:</td>
			<td>
				<input type="text" name="find_ID" value="<?echo htmlspecialchars($find_ID)?>" size="5">
			</td>
		</tr>
		<tr>
			<td><?=$arTableFields["SITE_ID"]["title"]?>:</td>
			<td>
					<?
					$rsData = \Bitrix\Main\SiteTable::getList();
					while($arRes = $rsData->Fetch())
					{
						$arSite[$arRes["LID"]] = $arRes["LID"];
					}
					$arr = array("reference" => $arSite, "reference_id" => $arSite);
					echo SelectBoxFromArray("find_SITE_ID", $arr, htmlspecialcharsex($find_SITE_ID), GetMessage('MAIN_ALL'));
					?>
			</td>
		</tr>
		<tr>
			<td><?=$arTableFields["GUEST_ID"]["title"]?>:</td>
			<td><input type="text" name="find_GUEST_ID" value="<?echo htmlspecialcharsex($find_GUEST_ID)?>"></td>
		</tr>
		<tr>
			<td><?=$arTableFields["CREATE_DATE"]["title"]?>:</td>
			<td><?echo CalendarPeriod("find_CREATE_DATE_1", $find_CREATE_DATE_1, "find_CREATE_DATE_2", $find_CREATE_DATE_2, "filter_form", "Y")?></td>
		</tr>
		<tr>
			<td><?=$arTableFields["IS_ONLINE"]["title"]?>:</td>
			<td>
				<input type="checkbox" name="find_IS_ONLINE" id="find_IS_ONLINE" value="Y" <?if ($find_IS_ONLINE == "Y") echo "checked"?>>
			</td>
		</tr>
<?
$oFilter->Buttons(array("table_id"=>$tableId, "url"=>$APPLICATION->GetCurPage(), "form"=>"filter_form"));
$oFilter->End();
?>
</form>
<?
$lAdmin->DisplayList();
//if ($arFilter["IS_ONLINE"] == "Y" && !$del_filter):
?>
<script type="text/javascript">
BX.ready(function(){
	okButton = document.getElementById("i_stat_sess_filterset_filter");
	delButton = document.getElementById("i_stat_sess_filterdel_filter");
	target = document.getElementById("find_IS_ONLINE");
	function setFilter()
	{
		if (target.checked && document.getElementById("adm-filter-tab-wrap-i_stat_sess_filter").className.match(/\adm-current-filter\b/))
		{
			//alert(target.value);
			okButton.click();
		}	
		setTimeout(function() {setFilter()}, 10000);
	}
	target.onchange = function()
	{
		okButton.click();
	}

	setTimeout(function() {setFilter()}, 10000);
});
</script>
<?//endif?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/local/modules/iggle.stat/prolog.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/local/modules/iggle.stat/admin/menu.php");

$sModulePermissions = $APPLICATION->GetGroupRight("iggle.stat");
if ($sModulePermissions == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$class = new \Iggle\Stat\IndexTable;
$arTableFields = $class->getMap();
$tableId = $class->getTableName();

$by = $_REQUEST["by"] ? $_REQUEST["by"] : "ID";
$order = $_REQUEST["order"] ? $_REQUEST["order"]  : "desc";
$oSort = new CAdminSorting($tableId, $by, $order);
$lAdmin = new CAdminList($tableId, $oSort);

$arFilterFields = array(
	"find_ID_1",
	"find_ID_2",
	"find_SITE_ID",
	"find_BOT_NAME",
	"find_URI",
	"find_IS_404",
	"find_CREATE_DATE_1",
	"find_CREATE_DATE_2",
);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array(
	">=ID" => $find_ID_1,
	"<=ID" => $find_ID_2,
	"SITE_ID" => $find_SITE_ID,
	"%BOT_NAME" => $find_BOT_NAME,
	"%URI" => $find_URI,
	"IS_404" => $find_IS_404
);

if ($find_IS_404 == "N")
{
	unset($arFilter["IS_404"]);
	$arFilter["!IS_404"] = "Y";
}

if(CheckDateTime($find_CREATE_DATE_1))
	$arFilter[">=CREATE_DATE"] = $find_CREATE_DATE_1." 00:00:00";
if(CheckDateTime($find_CREATE_DATE_2))
	$arFilter["<=CREATE_DATE"] = $find_CREATE_DATE_2." 23:59:59";

$rsData = $class->getList(array("order" => array($by => $order), "filter" => array_filter($arFilter)));
$rsData = new CAdminResult($rsData, $tableId);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("IGGLE_STAT_NAV")));
$arHeaders = array();
foreach($arTableFields as $key => $val)
{
	$arHeaders[] = array(
		"id" => $key, 
		"content" => $val["title"] ? $val["title"] : $key, 
		"sort" => ($val["data_type"] != "text") ? $key : false, 
		"align" => ($val["data_type"] == "integer") ? "right" : ($val["data_type"] == "boolean" ? "center" : false), 
		"default" => true
	);
}

$lAdmin->AddHeaders($arHeaders);

while($arRes = $rsData->NavNext(true, "f_"))
{
	$row = $lAdmin->AddRow($f_ID, $arRes);

	$row->AddViewField("IP", GetWhoisLink($f_IP));
	if ($f_IS_404 != "Y")
		$row->AddViewField("PAGE_TITLE", '<a target="_blank" href="'.$f_URI.'">'.$f_PAGE_TITLE.'</a>');
}

$lAdmin->AddAdminContextMenu();
$lAdmin->CheckListMode();
$APPLICATION->SetTitle(GetMessage("IGGLE_STAT_MENU_BOT_HIT"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<form name="filter_form" method="GET" action="<?echo $APPLICATION->GetCurPageParam()?>">
<?
$oFilter = new CAdminFilter(
		$tableId."_filter",
		array(
			$arTableFields["ID"]["title"],
			$arTableFields["SITE_ID"]["title"],
			$arTableFields["BOT_NAME"]["title"],
			$arTableFields["URI"]["title"],
			$arTableFields["IS_404"]["title"],
			$arTableFields["CREATE_DATE"]["title"],
	)
);
$oFilter->Begin();
?>
		<tr>
			<td><?=$arTableFields["ID"]["title"]?>:</td>
			<td>
				<input type="text" name="find_ID_1" value="<?echo htmlspecialchars($find_ID_1)?>" size="5"> &mdash; 
				<input type="text" name="find_ID_2" value="<?echo htmlspecialchars($find_ID_2)?>" size="5">
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
			<td><?=$arTableFields["BOT_NAME"]["title"]?>:</td>
			<td><input type="text" name="find_BOT_NAME" value="<?echo htmlspecialchars($find_BOT_NAME)?>"></td>
		</tr>
		<tr>
			<td><?=$arTableFields["URI"]["title"]?>:</td>
			<td><input type="text" name="find_URI" value="<?echo htmlspecialchars($find_URI)?>"></td>
		</tr>
		<tr>
			<td><?=$arTableFields["IS_404"]["title"]?>:</td>
			<td>
					<?
					$arr = array("reference_id" => array("Y", "N"), "reference" => array(GetMessage('IGGLE_STAT_MESS_Y'), GetMessage('IGGLE_STAT_MESS_N')));
					echo SelectBoxFromArray("find_IS_404", $arr, htmlspecialcharsex($find_IS_404), GetMessage('MAIN_ALL'));
					?>
			</td>
		</tr>
		<tr>
			<td><?=$arTableFields["CREATE_DATE"]["title"]?>:</td>
			<td><?echo CalendarPeriod("find_CREATE_DATE_1", $find_CREATE_DATE_1, "find_CREATE_DATE_2", $find_CREATE_DATE_2, "filter_form", "Y")?></td>
		</tr>


<?
$oFilter->Buttons(array("table_id"=>$tableId, "url"=>$APPLICATION->GetCurPageParam(""), "form"=>"filter_form"));
$oFilter->End();
?>
</form>
<?
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
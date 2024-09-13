<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/local/modules/iggle.stat/prolog.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/local/modules/iggle.stat/admin/menu.php");

$sModulePermissions = $APPLICATION->GetGroupRight("iggle.stat");
if ($sModulePermissions == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$class = new \Iggle\Stat\BotTable;
$arTableFields = $class->getMap();
$tableId = $class->getTableName();

$by = $_REQUEST["by"] ? $_REQUEST["by"] : "ID";
$order = $_REQUEST["order"] ? $_REQUEST["order"]  : "asc";
$oSort = new CAdminSorting($tableId, $by, $order);
$lAdmin = new CAdminList($tableId, $oSort);

$arFilter = array();

if ($lAdmin->EditAction())
{
	foreach ($FIELDS as $ID => $arFields)
	{
		$ID = intval($ID);

		if (!$lAdmin->IsUpdated($ID))
			continue;
		$arFields["ACTIVE"] = $arFields["ACTIVE"] == "Y" ? "Y" : "N";
		$class->update($ID, $arFields);

	}
}
if ($arID = $lAdmin->GroupAction())
{
	if ($_REQUEST["action_target"] == "selected")
	{
		$rsData = $class->getList(array("filter" => array_filter($arFilter)));
		while($arRes = $rsData->Fetch())
			$arID[] = $arRes["ID"];
	}

	foreach($arID as $ID)
	{
		if (intval($ID) > 0)
		{
			switch($_REQUEST["action"])
			{
				case "delete":
					$class->delete($ID);
				break;

				case "activate":
				case "deactivate":
					$arFields = array('ACTIVE' => $_REQUEST['action'] == 'activate' ? 'Y' : 'N');
					$class->update($ID, $arFields);
				break;
			}
		}
	}
}

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
	$row = & $lAdmin->AddRow($f_ID, $arRes);

	$row->AddViewField("ID", getLink("iggle_stat_bot_edit.php?ID=".$f_ID."&lang=".LANG, $f_ID, false));
	$row->AddViewField("NAME", getLink("iggle_stat_bot_edit.php?ID=".$f_ID."&lang=".LANG, $f_NAME, false));
    $row->AddInputField("NAME", array("size" => "35"));
    $row->AddInputField("MASK", array("size" => "35"));
	$row->AddCheckField("ACTIVE");

	$arActions = array();
	$arActions[] = array(
		"ICON" => "edit", 
		"TEXT" => GetMessage("MAIN_ADMIN_MENU_EDIT"), 
		"ACTION" => $lAdmin->ActionRedirect("iggle_stat_bot_edit.php?ID=".$f_ID."&lang=".LANG),
		"DEFAULT" => true,
	);
	$arActions[] = array(
		"ICON" => "delete", 
		"TEXT" => GetMessage("MAIN_ADMIN_MENU_DELETE"), 
		"ACTION" => "if (confirm('".GetMessage("IGGLE_STAT_ACTION_DELETE_CONFIRM")."')) ".$lAdmin->ActionDoGroup($f_ID, "delete", ""),
	);
	$row->AddActions($arActions);
}


$lAdmin->AddFooter(
	array(
		array(
			"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"), 
			"value" => $rsData->SelectedRowsCount()
		),
		array(
			"title" => GetMessage("MAIN_ADMIN_LIST_CHECKED"), 
			"counter" => true, 
			"value" => "0"
		),
	)
);

$lAdmin->AddGroupActionTable(
	array(
		"activate" => GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
		"deactivate" => GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
		"delete" => GetMessage("MAIN_ADMIN_LIST_DELETE"),
	)
);

$aContext = array();
$aContext[]= array(
	"TEXT" => GetMessage("IGGLE_STAT_ACTION_NEW"),
	"LINK" => "iggle_stat_bot_edit.php?lang=".LANG,
	"ICON" => "btn_new",
);

$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();
$APPLICATION->SetTitle(GetMessage("IGGLE_STAT_MENU_BOT_DETECT_ALT"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
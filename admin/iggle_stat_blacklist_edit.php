<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/local/modules/iggle.stat/prolog.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/local/modules/iggle.stat/admin/menu.php");

$sModulePermissions = $APPLICATION->GetGroupRight("iggle.stat");
if ($sModulePermissions == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$class = new \Iggle\Stat\BlackListTable;
$arTableFields = $class->getMap();

$ID = intVal($ID);
$arError =  array();
$arFields = array();
$bInitVars = false;
$APPLICATION->SetTitle(
	GetMessage("IGGLE_STAT_MENU_BLACKLIST")." - ".
	($ID > 0 ? (GetMessage("IGGLE_STAT_EDIT_RECORD_TITLE")." #".$ID) : GetMessage("IGGLE_STAT_NEW_RECORD_TITLE"))
);

if ($REQUEST_METHOD == "POST" && ($save || $apply))
{

	if (empty($arError))
	{
		$GLOBALS["APPLICATION"]->ResetException();
		
		foreach ($arTableFields as $name => $desc)
		{
			if (isset($_REQUEST[$name]))
			{
				$arFields[$name] = !empty($_REQUEST[$name]) ? htmlspecialcharsbx(trim($_REQUEST[$name])) : null;
			}
		}

		$arFields["ACTIVE_TO"] = CheckDateTime($_REQUEST["ACTIVE_TO"]) ? \Bitrix\Main\Type\DateTime::createFromUserTime($_REQUEST["ACTIVE_TO"]) : null;
		$arFields["ACTIVE"] = $_REQUEST["ACTIVE"] == "Y" ? "Y" : "N";

		if ($ID > 0)
		{
			$result = $class->update($ID, $arFields);
		}
		else
		{
			$arFields["CREATE_DATE"] = new \Bitrix\Main\Type\DateTime();
			$result = $class->add($arFields);
			$ID = $result->getId();
		}

		if (!$result->isSuccess())
		{
			foreach ($result->getErrorMessages() as $id => $text)
			{
				$arError[] = array("id" => $id, "text" => $text);
			}
		}
		else
		{
			LocalRedirect(
				strlen($save) > 0 ? 
					"iggle_stat_blacklist.php?lang=".LANG :
					"iggle_stat_blacklist_edit.php?lang=".LANG."&ID=".$ID
			);
		}
	}
	$e = new CAdminException($arError);
	$message = new CAdminMessage(($ID > 0 ? GetMessage("IGGLE_STAT_EDIT_RECORD_ERROR") : GetMessage("IGGLE_STAT_NEW_RECORD_ERROR")), $e);
	$bInitVars = true;
}

if ($bInitVars && !empty($arFields))
{
	$arElementSet = $arFields;
}
elseif ($ID > 0)
{
	$arElementSet = $class->getById($ID)->fetch();
	if (!empty($arElementSet["ACTIVE_TO"]))
		$arElementSet["ACTIVE_TO"] = $arElementSet["ACTIVE_TO"]->toString();
}
else
{
	$arElementSet["ACTIVE"] = "Y";
	$arElementSet["SORT"] = "500";
	$arElementSet["GUEST_ID"] = htmlspecialcharsbx(trim($_REQUEST["GUEST_ID"]));
	$arElementSet["IP"] = htmlspecialcharsbx(trim($_REQUEST["IP"]));
}

$aMenu = array(
	array(
		"TEXT" => GetMessage("IGGLE_STAT_ACTION_BACK"),
		"LINK" => "iggle_stat_blacklist.php?lang=".LANG,
		"ICON" => "btn_list",
	)
);

if ($ID > 0)
{
	$aMenu[] = array("SEPARATOR" => "Y");

	$aMenu[] = array(
		"TEXT" => GetMessage("IGGLE_STAT_ACTION_NEW"),
		"LINK" => "iggle_stat_blacklist_edit.php?lang=".LANG,
		"ICON" => "btn_new",
	);

	$aMenu[] = array(
		"TEXT" => GetMessage("IGGLE_STAT_ACTION_DELETE"),
		"LINK" => "javascript:if(confirm('".GetMessage("IGGLE_STAT_ACTION_DELETE_CONFIRM")."')) window.location='iggle_stat_blacklist.php?action=delete&ID[]=".$ID."&lang=".LANG."&".bitrix_sessid_get()."';",
		"ICON" => "btn_delete",
	);
}
$context = new CAdminContextMenu($aMenu);

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("IGGLE_STAT_MAIN_TAB"), "TITLE" => GetMessage("IGGLE_STAT_MAIN_TAB_TITLE"))
);

$tabControl = new CAdminForm("edit", $aTabs);
$tabControl->Begin(array(
	"FORM_ACTION" => $APPLICATION->GetCurPage()."?ID=".intval($ID)."&lang=".LANG,
));
$tabControl->BeginNextFormTab();
?>
<?
if ($ID > 0):
	$tabControl->BeginCustomField("ID", "ID");
?>
	<tr>
		<td width="40%">ID:</td>
		<td width="60%"><?=$ID?></td>
	</tr>
<?
	$tabControl->EndCustomField("ID");
endif
?>
<?
$tabControl->AddCheckBoxField("ACTIVE", $arTableFields["ACTIVE"]["title"].":", false, "Y", $arElementSet["ACTIVE"] === "Y");
$tabControl->BeginCustomField("ACTIVE_TO", $arTableFields["ACTIVE_TO"]["title"], $arIBlock["FIELDS"]["ACTIVE_TO"]["IS_REQUIRED"] === "Y");
?>
	<tr id="tr_ACTIVE_TO">
		<td width="40%"><?=$tabControl->GetCustomLabelHTML()?>:</td>
		<td width="60%"><?=CAdminCalendar::CalendarDate("ACTIVE_TO", $arElementSet["ACTIVE_TO"], 10, false)?></td>
	</tr>

<?
$tabControl->EndCustomField("ACTIVE_TO");
$tabControl->AddEditField("GUEST_ID", $arTableFields["GUEST_ID"]["title"].":", false, array("size" => 30, "maxlength" => 18), $arElementSet["GUEST_ID"]);
$tabControl->AddEditField("IP", $arTableFields["IP"]["title"].":", false, array("size" => 30, "maxlength" => 25), $arElementSet["IP"]);
$tabControl->AddTextField("MESSAGE", $arTableFields["MESSAGE"]["title"].":", $arElementSet["MESSAGE"], array("rows" => 5, "cols" => 55));
$tabControl->AddTextField("COMMENT", $arTableFields["COMMENT"]["title"].":", $arElementSet["COMMENT"], array("rows" => 5, "cols" => 55));
$tabControl->Buttons(
	array(
		"btnSave" => true,
		"btnCancel" => true,
		"back_url" => "iggle_stat_blacklist.php?lang=".LANG,
	)
);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<?
$context->Show();
if (isset($message))
	echo $message->Show();

$tabControl->Show();
$tabControl->ShowWarnings($tabControl->GetName(), $message);
?>
<?require($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php");?>
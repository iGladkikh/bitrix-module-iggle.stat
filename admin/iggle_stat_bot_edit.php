<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/local/modules/iggle.stat/prolog.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/local/modules/iggle.stat/admin/menu.php");

$sModulePermissions = $APPLICATION->GetGroupRight("iggle.stat");
if ($sModulePermissions == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$class = new \Iggle\Stat\BotTable;
$arTableFields = $class->getMap();

$ID = intVal($ID);
$arError =  array();
$arFields = array();
$bInitVars = false;
$APPLICATION->SetTitle(
	GetMessage("IGGLE_STAT_MENU_BOT_DETECT")." - ".
	($ID > 0 ? (GetMessage("IGGLE_STAT_EDIT_RECORD_TITLE")." #".$ID) : GetMessage("IGGLE_STAT_NEW_RECORD_TITLE"))
);

if ($REQUEST_METHOD == "POST" && ($save || $apply))
{

	if (!check_bitrix_sessid())
	{
		$arError[] = array(
			"id" => "bad_sessid",
			"text" => GetMessage("ERROR_BAD_SESSID"));
	}

	if (empty($arError))
	{
		$GLOBALS["APPLICATION"]->ResetException();
		
		foreach ($arTableFields as $name => $desc)
		{
			if ($_REQUEST[$name]) 
				$arFields[$name] = htmlspecialcharsbx(trim($_REQUEST[$name]));
		}
		$arFields["ACTIVE"] = $ACTIVE == "Y" ? "Y" : "N";

		if ($ID > 0)
		{
			$result = $class->update($ID, $arFields);
		}
		else
		{
			$arFields["CREATE_DATE"] = new Bitrix\Main\Type\DateTime;
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
					"iggle_stat_bot.php?lang=".LANG :
					"iggle_stat_bot_edit.php?lang=".LANG."&ID=".$ID
			);
		}
	}
	$e = new CAdminException($arError);
	$message = new CAdminMessage(($ID > 0 ? GetMessage("ERROR_EDIT_SMILE") : GetMessage("ERROR_ADD_SMILE")), $e);
	$bInitVars = true;
}

if ($bInitVars && !empty($arFields))
{
	$arElementSet = $arFields;
}
elseif ($ID > 0)
{
	$arElementSet = $class->getById($ID)->fetch();
}
else
	$arElementSet["ACTIVE"] = "Y";


require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		"TEXT" => GetMessage("IGGLE_STAT_ACTION_BACK"),
		"LINK" => "iggle_stat_bot.php?lang=".LANG,
		"ICON" => "btn_list",
	)
);

if ($ID > 0)
{
	$aMenu[] = array("SEPARATOR" => "Y");

	$aMenu[] = array(
		"TEXT" => GetMessage("IGGLE_STAT_ACTION_NEW"),
		"LINK" => "iggle_stat_bot_edit.php?lang=".LANG,
		"ICON" => "btn_new",
	);

	$aMenu[] = array(
		"TEXT" => GetMessage("IGGLE_STAT_ACTION_DELETE"),
		"LINK" => "javascript:if(confirm('".GetMessage("IGGLE_STAT_ACTION_DELETE_CONFIRM")."')) window.location='iggle_stat_bot.php?action=delete&ID[]=".$ID."&lang=".LANG."&".bitrix_sessid_get()."';",
		"ICON" => "btn_delete",
	);
}

$context = new CAdminContextMenu($aMenu);
$context->Show();
if (isset($message) && $message)
	echo $message->Show();

?>
	<form method="POST" action="<?=$APPLICATION->GetCurPageParam()?>" name="post_form">
	<input type="hidden" name="ID" value="<?=$ID?>" />
	<?=bitrix_sessid_post()?>
<?
$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("IGGLE_STAT_MAIN_TAB"), "TITLE" => GetMessage("IGGLE_STAT_MAIN_TAB_TITLE"))
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
$tabControl->BeginNextTab();
?>
<?if($ID > 0):?>
	<tr>
		<td width="40%">ID:</td>
		<td width="60%"><?=$ID?></td>
	</tr>
<?endif?>
	<tr>
		<td width="40%"><?=$arTableFields["ACTIVE"]["title"]?>:</td>
		<td width="60%">
			<input type="checkbox" name="ACTIVE" value="Y" <?echo $arElementSet["ACTIVE"] == "Y" ? "checked" : "" ?>/>
		</td>
	</tr>
	<tr>
		<td width="40%"><?=$arTableFields["NAME"]["title"]?>:</td>
		<td width="60%">
			<input type="text" name="NAME" value="<?=$arElementSet["NAME"]?>" size="40" />
		</td>
	</tr>
	<tr>
		<td width="40%"><?=$arTableFields["MASK"]["title"]?>:</td>
		<td width="60%">
			<input type="text" name="MASK" value="<?=$arElementSet["MASK"]?>" size="40" />
		</td>
	</tr>
<?
$tabControl->EndTab();

$tabControl->Buttons(
	array(
		"back_url" => "iggle_stat_bot.php?lang=".LANG,
	)
);
?>
</form>
<?
$tabControl->End();
$tabControl->ShowWarnings("post_form", $message);
?>
<?require($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php");?>
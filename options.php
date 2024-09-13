<?
if(!$USER->IsAdmin())
	return;

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/options.php");
IncludeModuleLangFile(__FILE__);

$arAllOptions = array(
	array("USER_STAT_ENABLE", GetMessage("IGGLE_STAT_USER_ENABLE"), "Y", array("checkbox")),
	array("BOT_STAT_ENABLE", GetMessage("IGGLE_STAT_BOT_ENABLE"), "Y", array("checkbox")),
	array("CLEAN_PERIOD", GetMessage("IGGLE_STAT_SAVE"), "30", array("text", "4", GetMessage("IGGLE_STAT_CLEAN_PERIOD"))),
	array("ONLINE_PERIOD", GetMessage("IGGLE_STAT_ONLINE_PERIOD"), "7", array("text", "4", GetMessage("IGGLE_STAT_PERIOD_SEC"))),
);

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("MAIN_TAB_SET"), "ICON" => "ib_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_SET")),
);

$tabControl = new CAdminTabControl("tabControl", $aTabs);

if($REQUEST_METHOD == "POST" && strlen($Update.$Apply.$RestoreDefaults)>0 && check_bitrix_sessid())
{
	if (strlen($RestoreDefaults)>0)
	{
		//\Bitrix\Main\Config\Option::delete("iggle.stat");
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iggle.stat/install/default_options.php");
	}
	else
	{
		foreach($arAllOptions as $arOption)
		{
			$name = $arOption[0];
			$val = $_REQUEST[$name];
			if ($arOption[2][0] == "checkbox" && $val != "Y")
				$val = "N";
			\Bitrix\Main\Config\Option::set("iggle.stat", $name, $val);
		}
	}
	if (strlen($Update) > 0 && strlen($_REQUEST["back_url_settings"]) > 0)
		LocalRedirect($_REQUEST["back_url_settings"]);
	else
		LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($mid)."&lang=".urlencode(LANGUAGE_ID)."&back_url_settings=".urlencode($_REQUEST["back_url_settings"])."&".$tabControl->ActiveTabParam());
}
$tabControl->Begin();
?>
<form method="post" action="<?=$APPLICATION->GetCurPage()?>?mid=<?=urlencode($mid)?>&amp;lang=<?=LANGUAGE_ID?>">
<?$tabControl->BeginNextTab();?>
<?
foreach($arAllOptions as $arOption):
	$val = \Bitrix\Main\Config\Option::get("iggle.stat", $arOption[0], $arOption[2]);
	$type = $arOption[3];
?>
<?if ($arOption[0] == "ONLINE_PERIOD"):?>
	<tr class="heading">
		<td colspan="2"><?=GetMessage("IGGLE_STAT_ONLINE")?></td>
	</tr>
<?endif?>
	<tr>
		<td width="50%"><?
			if($type[0] == "checkbox")
				echo "<label for=\"".htmlspecialchars($arOption[0])."\">".$arOption[1]."</label>";
			else
				echo $arOption[1];?>:</td>
		<td width="50%">
			<?if ($type[0]=="checkbox"):?>
				<input type="checkbox" id="<?=htmlspecialchars($arOption[0])?>" name="<?=htmlspecialchars($arOption[0])?>" value="Y"<?if($val=="Y")echo" checked";?>>
			<?elseif ($type[0]=="text"):?>
				<input type="text" size="<?=$type[1]?>" maxlength="255" value="<?=htmlspecialchars($val)?>" name="<?=htmlspecialchars($arOption[0])?>"><span style="position:relative;top:2px;left:5px"><?=$type[2]?></span>
			<?elseif ($type[0]=="textarea"):?>
				<textarea rows="<?=$type[1]?>" cols="<?=$type[2]?>" name="<?=htmlspecialchars($arOption[0])?>"><?=htmlspecialchars($val)?></textarea>
			<?endif?>
		</td>
	</tr>
<?endforeach?>
<?$tabControl->Buttons();?>
	<input type="submit" name="Update" value="<?=GetMessage("MAIN_SAVE")?>" title="<?=GetMessage("MAIN_OPT_SAVE_TITLE")?>">
	<input type="submit" name="Apply" value="<?=GetMessage("MAIN_OPT_APPLY")?>" title="<?=GetMessage("MAIN_OPT_APPLY_TITLE")?>">
	<?if(strlen($_REQUEST["back_url_settings"])>0):?>
		<input type="button" name="Cancel" value="<?=GetMessage("MAIN_OPT_CANCEL")?>" title="<?=GetMessage("MAIN_OPT_CANCEL_TITLE")?>" onclick="window.location='<?echo htmlspecialchars(CUtil::addslashes($_REQUEST["back_url_settings"]))?>'">
		<input type="hidden" name="back_url_settings" value="<?=htmlspecialchars($_REQUEST["back_url_settings"])?>">
	<?endif?>
	<input type="submit" name="RestoreDefaults" title="<?echo GetMessage("MAIN_HINT_RESTORE_DEFAULTS")?>" OnClick="return confirm('<?echo AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>')" value="<?echo GetMessage("MAIN_RESTORE_DEFAULTS")?>">
	<?=bitrix_sessid_post();?>
<?$tabControl->End();?>
</form>
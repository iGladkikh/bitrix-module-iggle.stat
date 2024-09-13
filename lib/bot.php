<?
namespace Iggle\Stat;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class BotTable extends Entity\DataManager
{

	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return "i_stat_bot";
	}

	public static function getMap()
	{
		return array(
			"ID" => array(
				"data_type" => "integer",
				"primary" => true,
				"autocomplete" => true,
				"title" => Loc::getMessage("IGGLE_STAT_BOT_ID"),
			),
			"NAME" => array(
				"data_type" => "string",
				"required" => true,
				"title" => Loc::getMessage("IGGLE_STAT_BOT_NAME"),
			),
			"ACTIVE" => array(
				"data_type" => "boolean",
				"values" => array("Y", "N"),
				"required" => true,
				"title" => Loc::getMessage("IGGLE_STAT_BOT_ACTIVE"),
			),
			"CREATE_DATE" => array(
				"data_type" => "datetime",
				"required" => true,
				"title" => Loc::getMessage("IGGLE_STAT_BOT_CREATE_DATE"),
			),
			"MASK" => array(
				"data_type" => "string",
				"required" => true,
				"title" => Loc::getMessage("IGGLE_STAT_BOT_MASK"),
			),
			"LAST_VISIT" => array(
				"data_type" => "datetime",
				"title" => Loc::getMessage("IGGLE_STAT_BOT_LAST_VISIT"),
			),
		);
	}
}
?>
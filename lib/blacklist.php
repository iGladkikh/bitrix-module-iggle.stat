<?
namespace Iggle\Stat;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class BlacklistTable extends Entity\DataManager
{

	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return "i_stat_blacklist";
	}

	public static function getMap()
	{
		return array(
			"ID" => array(
				"data_type" => "integer",
				"primary" => true,
				"autocomplete" => true,
				"title" => Loc::getMessage("IGGLE_STAT_BLACKLIST_ID"),
			),
			"ACTIVE" => array(
				"data_type" => "boolean",
				"values" => array("Y", "N"),
				"required" => true,
				"title" => Loc::getMessage("IGGLE_STAT_BLACKLIST_ACTIVE"),
			),
			"CREATE_DATE" => array(
				"data_type" => "datetime",
				"required" => true,
				"title" => Loc::getMessage("IGGLE_STAT_BLACKLIST_CREATE_DATE"),
			),
			"ACTIVE_TO" => array(
				"data_type" => "datetime",
				"title" => Loc::getMessage("IGGLE_STAT_BLACKLIST_ACTIVE_TO"),
			),
			"GUEST_ID" => array(
				"data_type" => "integer",
				"title" => Loc::getMessage("IGGLE_STAT_BLACKLIST_GUEST_ID"),
			),
			"IP" => array(
				"data_type" => "string",
				"title" => Loc::getMessage("IGGLE_STAT_BLACKLIST_IP"),
			),
			"MESSAGE" => array(
				"data_type" => "string",
				"title" => Loc::getMessage("IGGLE_STAT_BLACKLIST_MESSAGE"),
			),
			"COMMENT" => array(
				"data_type" => "string",
				"title" => Loc::getMessage("IGGLE_STAT_BLACKLIST_COMMENT"),
			),
			"LAST_VISIT" => array(
				"data_type" => "datetime",
				"title" => Loc::getMessage("IGGLE_STAT_BLACKLIST_LAST_VISIT"),
			),
		);
	}
}
?>
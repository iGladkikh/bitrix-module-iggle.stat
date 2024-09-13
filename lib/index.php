<?
namespace Iggle\Stat;

use Bitrix\Main\Entity;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class IndexTable extends Entity\DataManager
{

	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return "i_stat_bot_hit";
	}

	public static function getMap()
	{
		return array(
			"ID" => array(
				"data_type" => "integer",
				"primary" => true,
				"autocomplete" => true,
				"title" => Loc::getMessage("IGGLE_STAT_INDEX_ID"),
			),
			"SITE_ID" => array(
				"data_type" => "string",
				"required" => true,
				"title" => Loc::getMessage("IGGLE_STAT_INDEX_SITE_ID"),
			),
			"BOT_NAME" => array(
				"data_type" => "string",
				"required" => true,
				"title" => Loc::getMessage("IGGLE_STAT_INDEX_BOT_NAME"),
			),
			"IP" => array(
				"data_type" => "string",
				"title" => Loc::getMessage("IGGLE_STAT_INDEX_IP"),
			),
			"URI" => array(
				"data_type" => "string",
				"title" => Loc::getMessage("IGGLE_STAT_INDEX_URI"),
			),
			"PAGE_TITLE" => array(
				"data_type" => "string",
				"title" => Loc::getMessage("IGGLE_STAT_INDEX_PAGE_TITLE"),
			),
			"CREATE_DATE" => array(
				"data_type" => "datetime",
				"title" => Loc::getMessage("IGGLE_STAT_INDEX_CREATE_DATE"),
			),
			"IS_404" => array(
				"data_type" => "boolean",
				"values" => array("", "Y"),
				"title" => Loc::getMessage("IGGLE_STAT_INDEX_IS_404"),
			),
		);
	}
}
?>
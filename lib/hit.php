<?
namespace Iggle\Stat;

use Bitrix\Main\Entity;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class HitTable extends Entity\DataManager
{

	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return "i_stat_hit";
	}

	public static function getMap()
	{
		return array(
			"ID" => array(
				"data_type" => "integer",
				"primary" => true,
				"autocomplete" => true,
				"title" => Loc::getMessage("IGGLE_STAT_HIT_ID"),
			),
			"SITE_ID" => array(
				"data_type" => "string",
				"required" => true,
				"title" => Loc::getMessage("IGGLE_STAT_HIT_SITE_ID"),
			),
			"SESSION_ID" => array(
				"data_type" => "integer",
				"required" => true,
				"title" => Loc::getMessage("IGGLE_STAT_HIT_SESSION_ID"),
			),
			"IP" => array(
				"data_type" => "string",
				"title" => Loc::getMessage("IGGLE_STAT_HIT_IP"),
			),
			"URI" => array(
				"data_type" => "string",
				"title" => Loc::getMessage("IGGLE_STAT_HIT_URI"),
			),
			"PAGE_TITLE" => array(
				"data_type" => "string",
				"title" => Loc::getMessage("IGGLE_STAT_HIT_PAGE_TITLE"),
			),
			"CREATE_DATE" => array(
				"data_type" => "datetime",
				"title" => Loc::getMessage("IGGLE_STAT_HIT_CREATE_DATE"),
			),
			"IS_404" => array(
				"data_type" => "boolean",
				"values" => array("", "Y"),
				"title" => Loc::getMessage("IGGLE_STAT_HIT_IS_404"),
			),
		);
	}

}
?>
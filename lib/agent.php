<?
namespace Iggle\Stat;

class Agent
{
	public static function cleanUp()
	{
		set_time_limit(0);
		$days = intval(\Bitrix\Main\Config\Option::get("iggle.stat", "CLEAN_PERIOD"));
		if ($days > 0)
		{
			$connection = \Bitrix\Main\Application::getConnection();
			$connection->queryExecute("DELETE FROM i_stat_sess WHERE LAST_ACTIVITY <= DATE_SUB(CURDATE(),INTERVAL ".$days." DAY)");
			$connection->queryExecute("DELETE FROM i_stat_hit WHERE CREATE_DATE <= DATE_SUB(CURDATE(),INTERVAL ".$days." DAY)");
			$connection->queryExecute("DELETE FROM i_stat_bot_hit WHERE CREATE_DATE <= DATE_SUB(CURDATE(),INTERVAL ".$days." DAY)");
		}
		return "\\Iggle\\Stat\\Agent::cleanUp();";
	}
}
?>
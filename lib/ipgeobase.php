<?
namespace Iggle\Stat;

class IPGeoBase 
{
	private $fhandleCIDR, $fhandleCities, $fSizeCIDR, $fsizeCities;

	public function __construct($CIDRFile = false, $CitiesFile = false)
	{
		if(!$CIDRFile)
		{
			$CIDRFile = dirname(__FILE__).'/ipgeobase/cidr_optim.txt';
		}
		if(!$CitiesFile)
		{
			$CitiesFile = dirname(__FILE__).'/ipgeobase/cities.txt';
		}
		$this->fhandleCIDR = fopen($CIDRFile, 'r') or die("Cannot open $CIDRFile");
		$this->fhandleCities = fopen($CitiesFile, 'r') or die("Cannot open $CitiesFile");
		$this->fSizeCIDR = filesize($CIDRFile);
		$this->fsizeCities = filesize($CitiesFile);
	}

	private function getCityByIdx($idx)
	{
		rewind($this->fhandleCities);
		while(!feof($this->fhandleCities))
		{
			$str = fgets($this->fhandleCities);
			$arRecord = explode("\t", trim($str));
			if($arRecord[0] == $idx)
			{
				return array(
					'DISTRICT' => convertText($arRecord[3]),
					'REGION' => convertText($arRecord[2]),
					'CITY' => convertText($arRecord[1]),
					'LAT' => $arRecord[4],
					'LONG' => $arRecord[5]
				);
			}
		}
		return false;
	}

	public function getRecord($ip)
	{
		require($_SERVER["DOCUMENT_ROOT"].'/local/modules/iggle.stat/lang/ru/lib/ipgeobase/geoipregionvars.ru.php'); 

		$ip = sprintf('%u', ip2long($ip));
		
		rewind($this->fhandleCIDR);
		$rad = floor($this->fSizeCIDR / 2);
		$pos = $rad;
		while(fseek($this->fhandleCIDR, $pos, SEEK_SET) != -1)
		{
			if($rad) 
			{
				$str = fgets($this->fhandleCIDR);
			}
			else
			{
				rewind($this->fhandleCIDR);
			}
			
			$str = fgets($this->fhandleCIDR);
			
			if(!$str)
			{
				return false;
			}
			
			$arRecord = explode("\t", trim($str));

			$rad = floor($rad / 2);
			if(!$rad && ($ip < $arRecord[0] || $ip > $arRecord[1]))
			{
				return false;
			}
			
			if($ip < $arRecord[0])
			{
				$pos -= $rad;
			}
			elseif($ip > $arRecord[1])
			{
				$pos += $rad;
			}
			else
			{
				$result = array(
					'RANGE' => $arRecord[2], 
					'COUNTRY' => $arRecord[3]
				);

				if($arRecord[4] != '-' && $cityResult = $this->getCityByIdx($arRecord[4]))
				{
					$result += $cityResult;
				}

				if($result['COUNTRY'] && $arRuGeoCodes[$result['COUNTRY']])
				{
					$result['COUNTRY'] = $arRuGeoCodes[$result['COUNTRY']];
				}

				return $result;
			}
		}
		return false;
	}
}
?>
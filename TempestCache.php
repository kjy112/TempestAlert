<?php
class TempestCache{
	private static $instance;
	private static $tempest = 'tempest.json';
	private static $cache;
	
	protected function __construct(){}

	static function tempestChanged($incoming) {
      if (NULL == self::$instance) {
		self::$instance = new TempestCache();
		if(!file_exists(self::$tempest)){
			self::setCache($incoming);
			return true;
		}else{
			$fp = fopen(self::$tempest, 'r');
			self::$cache = json_decode(fgets($fp));
			fclose($fp);
		}
      }
	  
	  //Write cache and return true when current cache is empty or timestamp are different hour or incoming data set is different than cache
	  if(empty(self::$cache) || !self::checkTimeStamp($incoming) || !self::checkDataSet($incoming)){
			self::setCache($incoming);
			return true;
	  }		  
	  
	  return false;
    }
	
	static protected function setCache($incoming){
		//echo 'writing json' . '<br/><br/>';
		$fp = fopen(self::$tempest, 'w+');
		fwrite($fp, json_encode($incoming));
		fclose($fp);
	}
	
	static protected function checkTimeStamp($incomingTime){
	   /* Timestamp returns true if it is within the hour 
		* else false. Tempest resets every hour.         
		*/
		//echo gmdate("G", self::$cache[count(self::$cache)-1]) . ' ' . gmdate('G', $incomingTime[count($incomingTime)-1]) . '<br/><br/>';
		if(gmdate("G", self::$cache[count(self::$cache)-1]) == gmdate('G', $incomingTime[count($incomingTime)-1]))
			return true;
		else	
			return false;
	}
	
	static protected function checkDataSet($incomingData){
	   /* Incoming dataset is compared to the cached. 
		* Return true if same else false             
		*/
		//var_dump(array_diff($incomingData[0], self::$cache[0]));
		//echo count(array_diff($incomingData[0], self::$cache[0]));
		if(count(array_diff($incomingData[0], self::$cache[0])) == 0)
			return true;
		else	
			return false;
	}
}
?>
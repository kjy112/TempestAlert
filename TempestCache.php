<?php
/**
 * TempestCache stores a set of data as JSON and reads and writes the data into and from tempest.json file.
 * 
 * TempestCache class uses singleton to load tempest.json's data and use it as a cache to compare current data 
 * finding with the stored cache. If the stored cache is equivalent to the current data finding, then do nothing, 
 * else replace the cache with the current dataset as JSON into tempest.json. The cache is used to make sure users do not receive multiple 
 * alerts of the same data set. Thus, reduce redundancy.
 * 
 * @author K.J Ye me@kjye.name
 * @version TempestCache Version 0, 9-15-2015
 * 
 */

class TempestCache{
	/**
	 * @var TempestCache $instance	 singleton object instance
	 * @var String $tempest 	JSON file path that stores dataset/cache
	 * @var Array $cache	JSON decoded dataset read from JSON file
	 */
	private static $instance;
	private static $tempest = 'tempest.json';
	private static $cache;
	
	protected function __construct(){}
	
	/**
	 * TempestCache::tempestChanged($incoming) to check if incoming data array matches cache
	 *
	 * @param array[] $incoming Array structure of current dataset that needs to be compared to the cache.
	 *
	 * @return boolean True if datasets are different or timestamp is not within in the hour. Cache resets every hour.
	 * 				   returns false if incoming data set is the same as cache.
	 */
	
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
	
    /**
     * Sets cache by writing json data into tempest.json file
     * 
     * @param array[] $incoming
     */
	static protected function setCache($incoming){
		//echo 'writing json' . '<br/><br/>';
		$fp = fopen(self::$tempest, 'w+');
		fwrite($fp, json_encode($incoming));
		fclose($fp);
	}
	
	/**
	 * Checks to see if timestamp is within the hour.
	 * 
	 * @param String $incomingTime Timestamp that is to be check to see if its within the hour.
	 * 
	 * @return Boolean True if timestamp is within the hour else false. Tempest resets every hour.
	 */
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
	
	/**
	 *  Compares the current dataset with cache.
	 *
	 * @param array[] $incomingData	Current dataset that is to be compared to cache.
	 * @return Boolean True if incoming dataset is same as cache else false.
	 */
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

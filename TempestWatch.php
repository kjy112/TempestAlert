<?php 
/**
 * TempestWatch access poetempest.com's API or direct crawl and checks if maps contain a set of preffix and/or suffix.
 *
 * TempestWatch class access poetempest.com first by checking the web API and retrives the JSON dataset from it. If 
 * for whatever reason the API fails, it uses CRUL to direct crawl poetempest.com. The retrived JSON or DOM is then 
 * traversed to check for a set of preffix and suffix with a set of conditions. If preffix and/or suffix exists, then the map that it's associates 
 * gets append to an array $final. $final is then returned. If no preffix or suffix exists then an empty $final is returned. 
 * -1 is returned if both API and CRUL methods failed.
 *
 * @author K.J Ye me@kjye.name
 * @version TempestWatch Version 0, 9-15-2015
 *
 */

class TempestWatch{
	/**
	 * 
	 * @var String $APIGetURL	poetempest.com API URL
	 * @var String $url			Website URL to CRUL: poetempest.com
	 * @var String $id 			Default to 'maps.' This is the DOM element to look for when traversing through DOM
	 * @var Int $votesRequred 	Vote condition to check for and is set through instantiation. Default to 1.
	 * 
	 */
	protected $APIGetURL ;
	protected $url;
	protected $id = 'maps';
	protected $votesRequired;
	/**
	 * Instantiation class that requires at least $url param. $votes is the minimum required condition 
	 * for a map default to 1 if none passed. $api is the URL for the API.
	 * 
	 * @param String $url	URL of the website to CRUL
	 * @param Int $votes	Minimum votes required condition
	 * @param String $api	URL for the API. If no param passed then its set to null and by pass API method
	 * 
	 * @example new TempestWatch('poetempest.com'), new TempestWatch('poetempest.com', 3), or new TempestWatch('poetempest.com', 3, 'poetempest.com/rest')
	 */
	public function __construct($url, $votes=1, $api=NULL){
		$this->url = $url;
		$this->APIGetURL = $api;
		$this->votesRequired = $votes;
	}
	
	/**
	 * Entry method to retrive either API's JSON or CRUL's DOM. If $this->APIGetURL is null we skip to CRUL method, else use API URL 
	 * to retrive JSON. We first check to see if API returns JSON, if it fails we use CRUL to retrive $this->url's DOM. If both methods
	 * fail, we return -1. If success, an array of result is returned containing the maps we are looking for.
	 * 
	 * @param array[] $filters	An array of strings with preffix and suffix to check for.
	 * 
	 */
	public function execute($filters){
		/*
		 * If no API url passed through instantiation then we skip to CRUL method.
		 * Else, proceed with API method.
		 */
		if($this->APIGetURL != NULL){
			$final = $this->accessAPI($filters);
			if(is_array($final))
				return $final;
		}
		
		$final = $this->accessHTML($filters);
		if(is_array($final))
			return $final;
		
		//if gets here that means there's an error
		
		return -1;
	}
	
	/**
	 * Retriving poetempest.com data through its API URL. Data holds maps with current tempest's preffix and suffix along with 
	 * a vote count submitted by users to check for validity.
	 * 
	 * @param array[] $filters	Contains the preffix and/or suffix that we we use to match with the map mods.
	 * 
	 * @return array[]|int	If success, an array of maps are returned, else -1 is returned for failure. 
	 */
	private function accessAPI($filters){
		$result = [];
		
		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL => $this->APIGetURL,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 10
		));

		$jsonResult = json_decode(curl_exec($ch));
		$retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if($retcode == 200){
			//var_dump($jsonResult);
			foreach($jsonResult as $map => $value){
				foreach($filters as $noti){
					if(strpos(strtolower($value->name), $noti) !== false && $value->votes >= $this->votesRequired)
						$result[] = array(ucwords(str_replace('_', ' ', $map)), ucwords($noti));
				}
			}
		} else {
			error_log('JSON Loading Failed: Can not reach API');
			return -1;
		}
		
		//var_dump($result);
		return $result;
	}
	
	/**
	 * Alternate method to retrive poetempest.com data if API fails. It uses CURL to directly access poetempest.com
	 * DOM. Using DOMDocument, we traverse through the retrived DOM and look for the 'map' element. DOM holds maps 
	 * with current tempest's preffix and suffix along with a vote count submitted by users to check for validity.
	 * 
	 * @param array[] $filters	Contains the preffix and/or suffix that we we use to match with the map mods.
	 * 
	 * @return array[]|int	If success, an array of maps are returned, else -1 is returned for failure. 
	 */
	private function accessHTML($filters){
		$result = [];
		$mapIndex = 0;
		
		$dom = new DOMDocument();
		$dom->loadHTMLFile($this->url);
		$dom->preserveWhiteSpace = false;

		if($dataset = $dom->getElementById($this->id)) {
			foreach($dataset->getElementsByTagName('tr') as $node){
				if($node->parentNode->nodeName != 'thead'){
					$temp = strtolower($node->nodeValue);
					foreach($filters as $noti){
						if(strpos($temp, $noti) !== false){
							$rowData = $node->getElementsByTagName('td');
							//$this->debugDOMNode($rowData);
							if(trim($rowData->item($mapIndex+1)->nodeValue) >= $this->votesRequired)
								$result[] = array(trim(substr($rowData->item($mapIndex)->nodeValue, 0, strpos($rowData->item($mapIndex)->nodeValue, '(')-1)), ucwords($noti));
						}
					}
				} else if($node->parentNode->nodeName == 'thead'){
					$header = $node->getElementsByTagName('th');
					for($i=0; $i<$header->length; $i++){
						if(trim($header->item($i)->nodeValue) == 'Map'){
							$mapIndex = $i;
							break;
						}
					}
				}
			}
		}  else {
			error_log('URL Loading Failed: Can not find ID='.$this->id);
			return -1;
		}
		
		//var_dump($result);
		return $result;
	}
	
	/**
	 * DOMNode and DOMNodelist debugger
	 * 
	 * @param DOMNode $domEle	DOMNode to debug.
	 */
	private function debugDOMNode($domEle){
		$debugArray = [];
		foreach($domEle as $node){
			$debugArray[] = $node->nodeValue;
		}
		
		//print_r($debugArray);
		error_log($debugArray, 3, 'debugs.log');
	}
}
?>

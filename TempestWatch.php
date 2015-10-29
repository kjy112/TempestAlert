<?php 
class TempestWatch{
	protected $APIGetURL ;
	protected $url;
	protected $id = 'maps';
	protected $votesRequired;
	
	public function __construct($url, $votes=1, $api=NULL){
		$this->url = $url;
		$this->APIGetURL = $api;
		$this->votesRequired = $votes;
	}
	
	public function execute($filters){
		$final = $this->accessAPI($filters);
		if(is_array($final))
			return $final;
		
		$final = $this->accessHTML($filters);
		if(is_array($final))
			return $final;
		
		//if gets here that means there's an error
		
		return -1;
	}

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
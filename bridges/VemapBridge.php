<?php
class VemapBridge extends BridgeAbstract {

	const MAINTAINER = 'izintu.at';
	const NAME = 'Ausschreibungen - Vemap';
	const URI = 'https://bekanntmachungen.vemap.com/vemap-kdq-01.xml';
	const CACHE_TIMEOUT = 3600;// 1h
	const DESCRIPTION = 'Ausschreibungen auf Vemap';
	const WEBROOT = 'https://vemap.at';
	const PARAMETERS = array(
			'Suchbegriffe' => array(
				"keywords" => array(
					'name' => 'Suchbegriffe',
					'type' => 'text',
					'required' => 'false',
					'title' => 'Suchbegriffe, getrennt durch ,'
				),
				"mark" => array(
					'name' => 'Markieren',
					'type' => 'checkbox',
					'required' => 'false',
					'title' => 'Markieren statt filtern',
					'defaultValue' => false
				)
			)
		);
 

	public function collectData(){
	
		$xml = getXMLDOMObject(self::URI)
		or returnServerError('Could not Request Vemap.');
		$item = array();
		$xml_childnodes = $xml->childNodes->item(1)->childNodes;

		$datelimit = new DateTime("now");
		$datelimit->sub(new DateInterval('P1D'));		

		foreach ($xml_childnodes as $xml_item) {
			if ($xml_item->nodeName == "item") {
				$dateitem = new DateTime($xml_item->getAttribute("lastmod"));
				if ($dateitem > $datelimit ) {				
					$detail_xml = getXMLDOMObject($xml_item->getElementsByTagName('url')->item(0)->nodeValue);
					//Relevant tenders only
					//Bundesvergabegesetz 2018
					//VII 1 Z2
					//VII 1 Z3
					//VII 1 Z4
					//VII 1 Z5
					$type = null;
					switch ($detail_xml->firstChild->nodeName) {
						case "KD_8_1_Z2":
							$type = "Bekanntmachung von zu vergebenden Aufträgen, Rahmenvereinbarungen und die Einrichtung bzw. Einstellung von dynamischen Beschaffungssystemen";
						case "KD_8_1_Z3":
							$type = "Bekanntmachung von Wettbewerben";
						case "KD_8_1_Z4":
							$type = "Freiwillige Bekanntmachung eines Vergabeverfahrens ohne vorherige Bekanntmachung";
						case "KD_8_1_Z5":
							$type = "Bekanntmachung einer Direktvergabe mit vorheriger Bekanntmachung";
					}
					
					if ($type != null) {
						// Daten in Variablen überführen
						$rss_title = $detail_xml->getElementsByTagName('TITLE')->item(0)->nodeValue;
						$rss_officialname = $detail_xml->getElementsByTagName('OFFICIALNAME')->item(0)->nodeValue;
						$rss_description = $detail_xml->getElementsByTagName('SHORT_DESCR')->item(0)->nodeValue;
						
						$keyword_found = false;

						//final step: filtering if keywords are supplied
						if ($this->getInput('keywords') != null) {
							$keyword_array = explode(",", htmlspecialchars($this->getInput('keywords')));
							foreach ($keyword_array as $keyword) {
								$keyword_found = preg_match("~".preg_quote($keyword)."~i", $rss_title);
								if ($keyword_found == true) break;
								$keyword_found = preg_match("~".preg_quote($keyword)."~i", $rss_officialname);
								if ($keyword_found == true) break;
								$keyword_found = preg_match("~".preg_quote($keyword)."~i", $rss_description);
								if ($keyword_found == true) break;
							}
						}
						$rss_content = null;

						if ($keyword_found) {
							$rss_content = "Typ:<br/>" . $type . "<br/>Ausschreibende Stelle:<br/>" . $rss_officialname . "<br/>" . $rss_description;
							$rss_title = "[MATCH]". $rss_title;
						} else if $this->getInput('keywords') == null) {
							$rss_content = "Typ:<br/>" . $type . "<br/>Ausschreibende Stelle:<br/>" . $rss_officialname . "<br/>" . $rss_description;
						} else if (!$keyword_found && $this->getInput('keywords') != null && $this->getInput('mark') == true) {
							$rss_content = 'Eintrag in den Suchbegriffen "' . htmlspecialchars($this->getInput('keywords')) . '" nicht enthalten!<br/><br/>' . "Typ:<br/>" . $type . "<br/>Ausschreibende Stelle:<br/>" . $rss_officialname . "<br/>" . $rss_description;
						}
							if ($rss_content != null) {
								$item['uri'] = $detail_xml->getElementsByTagName('URL_DOCUMENT')->item(0)->nodeValue;
								$item['title'] = $rss_title;
								$item['timestamp'] = $dateitem->format('c');
								$item['content'] = $rss_content;
								$item['uid'] = $detail_xml->getElementsByTagName('REFERENCE_NUMBER')->item(0)->nodeValue; 
								$this->items[] = $item;
							}


					}
				}
			}

		}
	}
}

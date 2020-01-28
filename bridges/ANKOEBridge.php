<?php
class ANKOEBridge extends BridgeAbstract {

	const MAINTAINER = 'izintu.at';
	const NAME = 'Ausschreibungen - ANKÖ';
	const URI = 'http://ogd.ankoe.at/api/v1/notices';
	const CACHE_TIMEOUT = 86400; // 24h
	const DESCRIPTION = 'Ausschreibungen auf ANKÖ';
	const WEBROOT = 'https://ankoe.at';

	public function collectData(){
		$xml = getXMLDOMObject(self::URI)
		or returnServerError('Could not Request ANKÖ.');
		$item = array();
		$x1 = $xml->documentElement;
		foreach ($x1->childNodes() as $xml_item) {
			if ($xml_item->nodeName == "item") {				
				$detail_xml = getXMLDOMObject($xml_item->getElementsByTagName('url')->item(0)->nodeValue);
				$item['uri'] = $detail_xml->getElementsByTagName('URL_DOCUMENT')->item(0)->nodeValue;
				$item['title'] = $detail_xml->getElementsByTagName('OBJECT_CONTRACT')->item(0)->nodeValue;
			$item['timestamp'] = strtotime($detail_xml->getElementsByTagName('DATETIME_LAST_CHANGE')->item(0)->nodeValue);
			$item['content'] = $detail_xml->getElementsByTagName('SHORT_DESCR')->item(0)->nodeValue;
			$item['uid'] = $detail_xml->getElementsByTagName('REFERENCE_NUMBER')->item(0)->nodeValue; 

			$this->items[] = $item;
				}
			}

		}
	}
}

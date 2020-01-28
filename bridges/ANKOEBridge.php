<?php
class ANKOEBridge extends BridgeAbstract {

	const MAINTAINER = 'izintu.at';
	const NAME = 'Ausschreibungen - ANKÖ';
	const URI = 'http://ogd.ankoe.at/api/v1/notices';
	const CACHE_TIMEOUT = 86400; // 24h
	const DESCRIPTION = 'Ausschreibungen auf ANKÖ';
	const WEBROOT = 'https://ankoe.at';

	public function collectData(){
		$html = getSimpleHTMLDOM(self::URI)
		or returnServerError('Could not Request MedTechCluster.');

		$html = defaultLinkTo($html, self::WEBROOT);

		$item = array();

		$baseitems = $html->find('item');

		foreach ($baseitems as $element) {

			$item_url = $element->find('url', 0);
			$html_detail = getSimpleHTMLDOM($item_url);
			$item['uri'] = $html_detail->find('URL_DOCUMENT',0)->innertext;
			$item['title'] = $html_detail->find('OBJECT_CONTRACT TITLE P',0)->innertext;
			$item['timestamp'] = strtotime($html_detail->find('DATETIME_LAST_CHANGE', 0)->innertext);
			$item['content'] = $html_detail->find('OBJECT_CONTRACT SHORT_DESCR P',0)->innertext;
			$item['uid'] = $html_detail->find('REFERENCE_NUMBER',0)->innertext; 

			$this->items[] = $item;

			}
		}
	}
}

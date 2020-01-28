<?php
class MedizintechnikClusterBridge extends BridgeAbstract {

	const MAINTAINER = 'izintu.at';
	const NAME = 'Medizintechnik Cluster Oberösterreich ';
	const URI = 'https://medizintechnik-cluster.at/news-presse';
	const CACHE_TIMEOUT = 86400; // 24h
	const DESCRIPTION = 'News aus dem Medizintechnik-Cluster Oberösterreich';
	const WEBROOT = 'https://medizintechnik-cluster.at';

	public function collectData(){
		$html = getSimpleHTMLDOM(self::URI)
		or returnServerError('Could not Request MedTechCluster.');

		$html = defaultLinkTo($html, self::WEBROOT);

		$item = array();

		$articles = $html->find('div.ecx-maincontent div.ecx-item');

		foreach ($articles as $element) {
			$attributes = $element->find('a', 1);
			if ($attributes != NULL ) {

			$attributes = $attributes->attr;
			$item['uri'] = $attributes['href'];
			$item['title'] = $attributes['title'];
			$item['timestamp'] = strtotime($element->find('p[class="ecx-date"]', 0)->innertext);
			$item['content'] = $element->find('p', 1)->innertext;
			$item['uid'] = $attributes['title'];

			$this->items[] = $item;

			}
		}
	}
}

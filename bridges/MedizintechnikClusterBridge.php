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
		$articles = $html->find('div[class="ecx-item"]');

		foreach ($articles as $element) {
			$item['uri'] = $element->find('a', 0)->getAttribute('href');
			$title = $element->find('a', 0)->innertext;
			$item['title'] = $title;
			$item['timestamp'] = strtotime($element->find('p[class="ecx-date"]', 0)->innertext);
			$item['content'] = $element->find('p', 1)->innertext;
			$item['uid'] = $title;

			$this->items[] = $item;
		}
	}
}

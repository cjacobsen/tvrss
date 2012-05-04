<?
// La Doña
$series['ladonna'] = array(
	'title' => 'La Doña',
	'url_base' => 'http://www.chilevision.cl/home/',
	'url_list' => 'http://www.chilevision.cl/home/component/option,com_contentchv/task,blogcategorychv/id,1641/Itemid,3209/',
	'regex_list' => '/<a href="([^"]+)"><h3 class="header3">([^<]+)<\/h3>/',
	'relative_list' => true,
	'regex_ep' => "/playerCHV\('([^']+)'\);/",
	'relative_ep' => false
);

// Mundos Opuestos
$series['mop'] = array(
	'title' => 'Mundos Opuestos',
	'url_base' => 'http://www.13.cl',
	'url_list' => 'http://www.13.cl/programa/mundos-opuestos/videos/capitulos',
	'regex_list' => '/<span class="field-content"><div id="titulo"><a href="(\/programa\/mundos-opuestos\/capitulos\/[^"]+)">([^<]+)/',
	'relative_list' => true,
	'regex_ep' => "/var articuloVideo = \"([^\"]+)\";/",
	'relative_ep' => false
);

foreach($series as $feed => $serie) {
	$episodes = parse($serie);
	$file = 'rss/'.$feed.'.xml';
	echo "Writing $file\n";
	file_put_contents('rss/'.$feed.'.xml', render($serie, $episodes));
}

//Sync
`lftp -u jacobsen_cl-ftp,cpe1704t -e "mirror -R /home/wawi/public_html/tv_parser/rss /web/rss" www.jacobsen.cl`;

//==============================================

function parse($serie) {
	echo "Parsing list...";
	$html_list = file_get_contents($serie['url_list']);
	preg_match_all($serie['regex_list'], $html_list, $arr);
	$episodes = array();
	foreach($arr[0] as $key => $val) {
		$episodes[] = array(
			'desc' => utf8_decode(trim($arr[2][$key])),
			'url' => ($serie['relative_list'] ? $serie['url_base'] : '').html_entity_decode($arr[1][$key])
		);
	}
	echo count($episodes)." episodes\n";
	foreach($episodes as $key => $ep) {
		echo "Parsing {$ep['url']}...";
		$html_ep = file_get_contents($ep['url']);
		if(preg_match($serie['regex_ep'], $html_ep, $arr)) {
				$episodes[$key]['stream'] = ($serie['relative_ep'] ? $serie['url_base'] : '').urldecode($arr[1]);
				echo "OK\n";
		}
		else {
				echo "Not found\n";
		}
	}
	return($episodes);
}

function render($serie, $episodes) {
	$buffer = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
<rss version=\"2.0\">
	<channel>
		<title>{$serie['title']}</title>
		<link>{$serie['url_list']}</link>
		<description>{$serie['title']}</description>
";
foreach($episodes as $ep) {
	$buffer .= "
		<item>
			<title>{$ep['desc']}</title>
			<link>{$ep['stream']}</link>
			<description>{$ep['desc']}</description>
		</item>";
}
	$buffer .= "
	</channel>
</rss>
";
	return $buffer;
}


?>

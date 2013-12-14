<?PHP

/**
 * Plugin for fetching full-text content from aftenposten.no.
 */
class Af_Aftenposten extends Plugin {
	private $host;

	function about() {
		return array(1.0,
			"Fetch full articles from aftenposten.no",
			"andlin");
	}

	function init($host) {
		$this->host = $host;

		$host->add_hook($host::HOOK_ARTICLE_FILTER, $this);
	}

	function hook_article_filter($article) {
		$owner_uid = $article["owner_uid"];

		$force = false;

		if (strpos($article["link"], "aftenposten.no") !== FALSE) {
			if (strpos($article["plugin_data"], "aftenposten,$owner_uid:") === FALSE || $force) {
				$doc = new DOMDocument();
				@$doc->loadHTML(fetch_file_contents($article["link"]));
				if ($doc) {
					$found = true;

					if ($found) {
						$art = $doc->createElement( 'article' );
						$elem = $doc->getElementById("content");
						$xpath = new DomXPath( $doc );

						$title = $xpath->query( '//h1[contains(@class, "articleTitle")]', $elem )->item( 0 );
						$publishInfo = $xpath->query( '//div[contains(@class, "publishInfo")]', $elem )->item( 0 );
						$storyContent = $xpath->query( '//div[contains(@class, "storyContent")]', $elem )->item( 0 );
						$art->appendChild( $title );
						$art->appendChild( $publishInfo );
						$art->appendChild( $storyContent );

						$article["content"] = $doc->saveXML( $art );
						if (!$force) $article["plugin_data"] = "aftenposten,$owner_uid:" . $article["plugin_data"];
					}
				}
			} else if (isset($article["stored"]["content"])) {
				$article["content"] = $article["stored"]["content"];
			}
		}

		return $article;
	}

	function api_version() {
		return 2;
	}

}
?>

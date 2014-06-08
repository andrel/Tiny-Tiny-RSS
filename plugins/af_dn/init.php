<?PHP

/**
 * Plugin for fetching full-text content from dn.no.
 */
class Af_Dn extends Plugin {
	private $host;

	function about() {
		return array(1.0,
			"Fetch full articles from dn.no",
			"andlin");
	}

	function init($host) {
		$this->host = $host;

		$host->add_hook($host::HOOK_ARTICLE_FILTER, $this);
	}

	function hook_article_filter($article) {
		$owner_uid = $article["owner_uid"];

		// TODO andlin: Set to false
		$force = false;

		if (strpos($article["link"], "dn.no") !== FALSE) {
			if (strpos($article["plugin_data"], "dn,$owner_uid:") === FALSE || $force) {
				$doc = new DOMDocument();
				@$doc->loadHTML(fetch_file_contents($article["link"]));
				if ($doc) {
					$found = true;

					if ($found) {
						$art = $doc->createElement( 'article' );
						$elem = $doc->getElementById("editorial");
						$xpath = new DomXPath( $doc );

						$isPreview = $xpath->query( '//a[contains(@class, "articlePreviewLink")]', $elem)->length != 0;
						if ( $isPreview ) {
							$art->appendChild( '<h1>DN_PREVIEW</h1>' );
						}
						

						$title = $xpath->query( '//div[contains(@class, "article_top")]/h1', $elem )->item( 0 );
						$leadText = $xpath->query( '//p[contains(@class, "leadtext")]', $elem )->item( 0 );
						$publishInfo = $xpath->query( '//div[contains(@class, "bylines_and_timestamp")]', $elem )->item( 0 );
						$storyContent = $xpath->query( '//div[contains(@class, "body_text")]', $elem )->item( 0 );
						$art->appendChild( $title );
						if ( $leadText !== null) {
							$art->appendChild( $leadText );
						}
						if ( $publishInfo !== null ) {
							$art->appendChild( $publishInfo );
						}
						$art->appendChild( $storyContent );

						$article["content"] = $doc->saveXML( $art );
						if (!$force) $article["plugin_data"] = "dn,$owner_uid:" . $article["plugin_data"];
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

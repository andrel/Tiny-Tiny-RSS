<?php
class Af_Arstechnica extends Plugin {
	private $host;

	function about() {
		return array(1.0,
			"Fetch full articles from Ars Technica",
			"andlin");
	}

	function init($host) {
		$this->host = $host;

		$host->add_hook($host::HOOK_ARTICLE_FILTER, $this);
	}

	function hook_article_filter($article) {
		$owner_uid = $article["owner_uid"];

error_log("ars-technica: " . $article["link"]);
		$force = true;

		if (strpos($article["link"], "arstechnica.com") !== FALSE) {
			if (strpos($article["plugin_data"], "arstechnica,$owner_uid:") === FALSE || $force) {
				$doc = new DOMDocument();
                                @$doc->loadHTML(fetch_file_contents($article["link"]));
				if ($doc) {
					$found = true;

					if ($found) {
						$art = $doc->createElement( 'article' );
						$elem = $doc->getElementById("standalone");
						$xpath = new DomXPath( $doc );

						$title = $xpath->query( '//h1[contains(@class, "heading")]', $elem )->item( 0 );
						$title2 = $xpath->query( '//h2[contains(@class, "standalone-deck")]', $elem )->item( 0 );
						$publishInfo = $xpath->query( '//p[contains(@class, "byline")]', $elem )->item( 0 );
						$storyContent = $xpath->query( '//div[contains(@class, "article-content")]', $elem )->item( 0 );
						$art->appendChild( $title );
						$art->appendChild( $title2 );
						$art->appendChild( $publishInfo );
						$art->appendChild( $storyContent );

						$article["content"] = $doc->saveXML( $art );
						if (!$force) $article["plugin_data"] = "arstechnica,$owner_uid:" . $article["plugin_data"];
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

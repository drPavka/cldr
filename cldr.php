#!/usr/bin/php
<?php
/**
 * @file      cldr.php
 *
 *
 * @version   1.0.0
 */
error_reporting( E_ALL );

try {
	if(!defined('STDOUT')) {
		throw new ErrorException('This script must be run only in CLI mode');
	}
	if(!file_exists('supplementalData.xml')){
		define('CLDR_SRC', 'http://unicode.org/repos/cldr/trunk/common/supplemental/supplementalData.xml');
	}
	else {
		//for local development
		define( 'CLDR_SRC', 'supplementalData.xml' );
	}


	/**
	 * receive and return data from the external data source
	 *
	 * @return SimpleXMLElement
	 * @throws Exception
	 */
	function get_data(){
		static $dataSource = null;

		if(is_null($dataSource)){
			$opts = [
				'http' => [
					'user_agent' => 'PHP libxml agent',
				],
			];

			$context = stream_context_create( $opts );
			libxml_set_streams_context( $context );
			libxml_use_internal_errors( true );
			if ( ! $dataSource = @simplexml_load_file( CLDR_SRC ) ) {
				$error = libxml_get_last_error();
				throw new Exception( $error->message, $error->code );
			}
		}

		return $dataSource;
	}

	/**
	 * Return languages data
	 * [$lang_abr => [$lang_script]]
	 * @param SimpleXMLElement $dataSource
	 *
	 *
	 * @return array
	 * @throws Exception
	 */
	function get_languages_from(SimpleXMLElement $dataSource){
		//get all languages
		if ( ! ( $languages = $dataSource->xpath( '/supplementalData/languageData/language[@type and @territories]' ) ) ) {
			throw new Exception( 'Bad language xpath.' );
		}
		$langSet = [];
		foreach ( $languages as $langNode ) {
			$scripts = null;
			$abbr = (string)$langNode['type'];
			if(!array_key_exists($abbr, $langSet)){
				$langSet[$abbr] = [];
			}
			$langSet[$abbr] = array_merge(
				$langSet[$abbr],
				( $langNode['scripts'] && sizeof( $scripts = explode( ' ', $langNode['scripts'] ) ) > 1 )?array_map(function($script) use ($abbr){
					return $abbr.'_'.(string)$script;
				}, $scripts):[$abbr]
			);
		}

		return $langSet;
	}

	$dataSource = get_data();
	$langSet = get_languages_from($dataSource);

	$result = [];

	//prepare $result
	array_walk($langSet, function($lang, $langAbbr) use (&$result){
		//yes, we can get data source from context :)
		$dataSource = get_data();
		foreach ($lang as $langAbbrScript){
			$territoryNodes = $dataSource->xpath('/supplementalData/territoryInfo/territory[languagePopulation[@type="'.$langAbbrScript.'"]]');
			if($territoryNodes) {
				foreach ($territoryNodes as $territoryNode){
					$population = (int)$territoryNode['population'];

					if($langPercentNode = $territoryNode->xpath('languagePopulation[@type="'.$langAbbrScript.'"]/@populationPercent')){
						list($langPercent) = $langPercentNode;
						$langPercent = (float)$langPercent;
						if(!isset($result[$langAbbr])) $result[$langAbbr] = 0;

						$result[$langAbbr] += round(($population * $langPercent)/100);
					}
				}
			}
		}
	});

	foreach($result as $langAbbr => $langUsageNumber){
		fputs(STDOUT, implode(' ', [$langAbbr, locale_get_display_language($langAbbr), $langUsageNumber]) . PHP_EOL, 4096);
	}
}
catch (ErrorException $e){
	echo $e->getMessage();
}
catch ( Exception $e ) {
	fwrite(STDERR, 'Error: ' . $e->getMessage() . ' in file ' . $e->getFile() . '[' . $e->getLine() . ']' . PHP_EOL);
}

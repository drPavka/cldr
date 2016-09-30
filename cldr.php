<?php
/**
 * @file      cldr.php
 *
 *
 * @version   1.0.0
 */
error_reporting( E_ALL );

try {
	//define('CLDR_SRC', 'http://unicode.org/repos/cldr/trunk/common/supplemental/supplementalData.xml');
	define( 'CLDR_SRC', 'supplementalData.xml' );
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

	array_walk($langSet, function($lang, $langAbbr) use ($dataSource){
		foreach ($lang as $langAbbrScript){
			$territoryNodes = $dataSource->xpath('/supplementalData/territoryInfo/territory[languagePopulation[@type="'.$langAbbrScript.'"]]');
			if($territoryNodes) {
				foreach ($territoryNodes as $territoryNode){
					$population = (int)$territoryNode['population'];

					if($langPercentNode = $territoryNode->xpath('languagePopulation[@type="'.$langAbbrScript.'"]/@populationPercent')){
						list($langPercent) = $langPercentNode;
						$langPercent = (float)$langPercent;

						echo $langAbbr,'[',$langPercent,'-',$population, ']',' ', round($population * $langPercent),"\r\n";
					}
				}
			}
		}
	});


} catch ( Exception $e ) {
	echo 'Error: ', $e->getMessage();
}

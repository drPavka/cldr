<?php
/**
 * @file      cldr.php
 *
 *
 * @version   1.0.0
 */
try {
	//define('CLDR_SRC', 'http://unicode.org/repos/cldr/trunk/common/supplemental/supplementalData.xml');
	define('CLDR_SRC', 'supplementalData.xml');
	$doc = new DOMDocument('1.0', 'UTF-8');

	$opts = [
		'http' => [
			'user_agent' => 'PHP libxml agent',
		],
	];

	$context = stream_context_create( $opts );
	libxml_set_streams_context( $context );
	$doc->load(CLDR_SRC);
	$xsltDoc = new DOMDocument('1.0', 'UTF-8');
	$xsltDoc->load('supplementalData.xsl');

	$xslProc = new XSLTProcessor();
	$xslProc->importStyleSheet($xsltDoc);

	echo $xslProc->transformToXML($doc);
}
catch(Exception $e){

}

<?php
//ini_set('memory_limit', '64M');
ini_set('display_errors', 1);
error_reporting(E_ALL);
//define( 'MAX_FILE_SIZE', 4000000 ); // Max file size 4MB appox.
include('/export/www_mtu_edu_docroot/htdocs/mtu_resources/php/htmldom/simple_html_dom-1.9.1.php');

	$page = file_get_html("https://status.kraken.io/");
	$status = $page->find('div[data-component-id=f5sydpy2zn8z] span', 1)->plaintext;




	if(file_get_contents('http://www.mtu.edu/mtu_resources/php/cms/getHttps/status.php?url=http://kraken.io') != 0 && stripos($status,"Operational") != '') {

		echo 'Kraken is up!';
		
	} else {
		echo '0';
	}

?>
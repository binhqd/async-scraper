<?php
error_reporting(E_ALL & E_NOTICE);
ini_set("display_error", "On");

require("libs/functions.php");

include ('libs/mining.php');
$script = new \Mining\MiningComponent ();

//$script->combineBook("Genesis");
$dir = dirname(__FILE__) . "/books/";
$serialized = file_get_contents("{$dir}/index.txt");

$bookName = '';
if (isset($_GET['book'])) {
	$bookName = $_GET['book'];
}

$bookIndex = unserialize($serialized);
$books = array();
if (!empty($bookName)) {
	$bookContents = $script->combineBook($bookName);
	$books[] = implode("\n", $bookContents);
} else {
	$oldTesaments = $bookIndex['oldTesaments'];
	$newTesaments = $bookIndex['newTesaments'];
	
	// This is old Tesaments
	foreach($oldTesaments as $book) {
		if ($book['text'] == '') continue;
		$bookContents = $script->combineBook($book['text']);
		
		$books[] = implode("\n", $bookContents);
	}


	// This is new Tesaments
	foreach($newTesaments as $book) {
		if ($book['text'] == '') continue;
		$bookContents = $script->combineBook($book['text']);
		
		$books[] = implode("\n", $bookContents);
	}
}

$file = dirname(__FILE__) . "/books/combined.html";

$head = '<html>
<head>
<meta name="pb_title" content="Expositor\'s Bible Commentary - OT">
<meta name="pb_abbrev" content="EPCOT">
<meta name="pb_copyright" content="Copyright &copy; 1990 by Robert B. Hughes and J. Carl Laney. All rights reserved.">
<meta name="pb_publisher" content="Laridian, Inc.">
<meta name="pb_author" content="Hughes, Robert B and J. Carl Laney">
<meta name="pb_city" content="Cedar Rapids, IA">
<meta name="pb_date" content="2004">
<meta name="pb_pubid" content="101">
<meta name="pb_bookid" content="36">
<meta name="pb_editionid" content="1">
<meta name="pb_revisionid" content="1">
<meta name="pb_synctype" content="verse">
</head> 
<body>';

if (!empty($bookName)) {
	
$html = $head;
$html .= implode("\n\n\n", $books);
$html .= '
</body>
</html>
';
echo $html;
}
	
else {
	file_put_contents($file, $head);
	foreach ($books as $bookContent) {
		file_put_contents($file, $bookContent, FILE_APPEND);
		file_put_contents($file, "\n\n\n", FILE_APPEND);
		file_put_contents($file, "\n\n\n", FILE_APPEND);
	}

	$footer .= '
</body>
</html>
';
	file_put_contents($file, $footer, FILE_APPEND);
	echo 'Books combined';
}
<?php
error_reporting(E_ALL & E_NOTICE);
ini_set("display_error", "On");

require("libs/functions.php");

include ('libs/mining.php');
$script = new \Mining\MiningComponent ();

//$script->combineBook("Genesis");
$bookName = '';
if (isset($_GET['book'])) {
	$bookName = $_GET['book'];
}

$chapter = '';
if (isset($_GET['chapter'])) {
	$chapter = $_GET['chapter'];
}

$books = array();
if (!empty($bookName)) {
	$bookContents = $script->combineBook($bookName, $chapter);
	$books[] = implode("\n", $bookContents);
}

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

$html = $head;
$html .= implode("\n\n\n", $books);
$html .= '
</body>
</html>
';

file_put_contents("books/extracted.html", $html);
echo $html;
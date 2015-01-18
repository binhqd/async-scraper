<?php
namespace Mining;
error_reporting(E_ALL);
// call library

define("OWNER", 541);
define("GROUP", 538);
	
include_once ("simple_html_dom.php");
/**
 * 
 * @author Binhqd
 * @email binhqd@gmail.com
 * 
 */

class MiningComponent {
	const CALLS = 1;
	const PUTS = 2;
	
	/**
	 * Define data schema
	 * @var static $fields
	 */
	public static $fields = array(
		
	);
	const PART_FIRST = 1;
	const PART_SECOND = 2;
	const PART_BOTH = 3;
	
	
	/**
	 * This method is used to return directory for cache
	 * @return string
	 */
	public static function cacheDir() {
		$dir = realpath(dirname(__FILE__) . "/../cache");
		if (!is_dir($dir)) {
			mkdir($dir);
			chmod($dir, 0777);
			chown($dir, OWNER);
			chgrp($dir, GROUP);

		}
		return $dir;
	}
	
	/**
	 * This method is used to get all books
	 * @param string $url
	 */
	public function getBooks($url, $part = self::PART_BOTH) {
		$html = file_get_html ( $url );
		
		// first table
		$table = $html->find ( 'table', 0 );
		
		$data = array(
			'oldTesaments'	=> array(),
			'newTesaments'	=> array(),
		);
		// get old testaments
		if ($part == self::PART_FIRST || $part == self::PART_BOTH) {
			$oldTesaments = $table->find( 'table', 0);
			
			foreach ( $oldTesaments->find ( 'a' ) as $link ) {
				$text = $link->plaintext;
				$href = $link->getAttribute('href');
				
				if (empty($text) || empty($href)) continue;
	
				$data['oldTesaments'][] = array(
					'text'	=> $text,
					'href'	=> $href
				);
				
				// create folder
				$bookFolder = dirname(__FILE__) . "/../books/{$text}";
				if (!is_dir($bookFolder)) {
					mkdir($bookFolder);
					chmod($bookFolder, 0777);
					chown($bookFolder, OWNER);
					chgrp($bookFolder, GROUP);
				}
			}
		}
		
		if ($part == self::PART_SECOND || $part == self::PART_BOTH) {
			$newTesaments = $table->find( 'table', 1);
			foreach ( $newTesaments->find ( 'a' ) as $link ) {
				$text = $link->plaintext;
				$href = $link->getAttribute('href');
				
				if (empty($text) || empty($href)) continue;
				
				$data['newTesaments'][] = array(
					'text'	=> $text,
					'href'	=> $href
				);
	
				// create folder
				$bookFolder = dirname(__FILE__) . "/../books/{$text}";
				
				if (!is_dir($bookFolder)) {
					mkdir($bookFolder);
					chmod($bookFolder, 0777);
					chown($bookFolder, OWNER);
					chgrp($bookFolder, GROUP);
				}
			}
		}
		
		// cache book index
		file_put_contents( dirname(__FILE__) . "/../books/index.txt", serialize($data));
		
		return $data;
	}
	
	/**
	 * This method is used to get all chapter of a book
	 * @param string $url
	 * @return array $chapters
	 */
	public function getChapters($url) {
		$html = file_get_html ( $url );
		
		// Read book info
		$bookData = $html->find('.crumbtrail b', 0);
		$bookName = $bookData->plaintext;
		
		// first table
		$container = $html->find ( 'table', 0 );
		
		$chapters = array();
		
		// get old testaments
		$chaptersContainer = $container->find( 'table', 0);
		foreach ( $chaptersContainer->find ( 'a' ) as $link ) {
			$text = $link->plaintext;
			$href = $link->getAttribute('href');
			
			$chapters[] = array(
				'text'	=> $text,
				'href'	=> $href
			);
			
			// create chapter folders
			$dir = dirname(__FILE__) . "/../books/{$bookName}/{$text}";
			if (!is_dir($dir)) {
				mkdir($dir);
				chmod($dir, 0777);
				chown($dir, OWNER);
				chgrp($dir, GROUP);
			}
		}
		
		$output = array(
			"book"	=> $bookName,
			"chapters"	=> $chapters
		);
		
		// cache chapter index
		file_put_contents(dirname(__FILE__) . "/../books/{$bookName}/index.txt", serialize($output));
		
		return $output;
	}
	
	/**
	 * This method is used to read the content of a book
	 * @param string $url
	 */
	public function getBookContent($url) {
		$content = file_get_contents($url);
		$content = preg_replace('/[a-zA-Z0-9\/\r\n+]{128,}/', "", $content);
		
		$html = str_get_html ( $content );
		
		// Read book info
		$chapterData = $html->find('.crumbtrail b', 0);
		$chapterName = $chapterData->plaintext;
		
		$bookData = $html->find('.crumbtrail a', 3);
		$bookName = $bookData->plaintext;
		// first table
		$verseContainer = $html->find ( '.content_main .general .large');
		
		$verses = array();
		if (is_array($verseContainer) && count($verseContainer) > 0) {
			foreach ( $verseContainer as $verseItem ) {
				$text = trim($verseItem->plaintext);
				//$href = $link->getAttribute('href');
				
				$paragraphs = array();
				foreach ( $verseItem->parent()->find('p') as $paragraph ) {
					$paragraphs[] = $paragraph->plaintext;
				}
				$verses[] = array(
					'text'	=> $text,
					'paragraphs'	=> $paragraphs
				);
			}
		} else {
			$contentContainer = $html->find ( '.content_main .general div', 2);
			if (!!$contentContainer) {
				$text = "Introduction";
				//$href = $link->getAttribute('href');
				
				$paragraphs = array();
				foreach ( $contentContainer->find('p') as $paragraph ) {
					$p = trim($paragraph->plaintext);
					
					if (!empty($p)) {
						$paragraphs[] = $p;
					}
				}
				$verses[] = array(
					'text'			=> $text,
					'paragraphs'	=> $paragraphs
				);
			} else {
				header( "HTTP/1.1 404 Not Found", true );
			}
		}
		
		$output = array(
			"book"	=> $bookName,
			"chapter"	=> $chapterName,
			"verses"	=> $verses
		);
		
		$verseFile = dirname(__FILE__) . "/../books/{$bookName}/{$chapterName}/verses.txt";
		file_put_contents($verseFile, serialize($output));
		
		return $output;
	}
	
	public function combineBooks() {
		
	}
	
	private static function formatText($string) {
		$illegalChars = array("", "&#149;", "&#133;", "&#147;", "&#151;");
		
		$paragraph = strip_tags($string);
						
		//$paragraph = str_replace(array("<", ">"), array("&lt;", "&gt;"), $paragraph);
	
		$paragraph = preg_replace('/[^\00-\255]+/u', "", $paragraph);
		
		$paragraph = htmlentities($paragraph);
		$paragraph = str_replace("&amp;", "&", $paragraph);
		$paragraph = str_replace($illegalChars, "", $paragraph);
		
		
		
		//$paragraph = htmlspecialchars($paragraph, ENT_SUBSTITUTE);
		//$paragraph = htmlspecialchars($paragraph, ENT_DISALLOWED);
		
		$paragraph = iconv("UTF-8","ISO-8859-1//IGNORE",$paragraph);
		$paragraph = iconv("ISO-8859-1","UTF-8",$paragraph);
		$paragraph = iconv('UTF-8', 'ASCII//TRANSLIT', $paragraph);

		$paragraph=str_replace("\n\n",  "z000"        ,$paragraph);
	    $paragraph=str_replace("\n",    "z111"        ,$paragraph);
	    $paragraph = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $paragraph);
// 	    /*$paragraph=filter_var($paragraph,FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
	    $paragraph=str_replace("z000",  "\n\n"  ,$paragraph);
	    $paragraph=str_replace("z111",  "\n"       ,$paragraph);
		$paragraph = preg_replace('/\&\#149([^\d]{1})/', "$1", $paragraph);
		$paragraph = preg_replace('/\&\#149$/', "", $paragraph);
		$paragraph = preg_replace('/\&\#133([^\d]{1})/', "$1", $paragraph);
		$paragraph = preg_replace('/\&\#133$/', "", $paragraph);
		$paragraph = preg_replace('/\&\#147([^\d]{1})/', "$1", $paragraph);
		$paragraph = preg_replace('/\&\#147$/', "", $paragraph);
		$paragraph = preg_replace('/\&\#151([^\d]{1})/', "$1", $paragraph);
		$paragraph = preg_replace('/\&\#151$/', "", $paragraph);
// 		$paragraph = recode_string("us..flat", $paragraph);
		return $paragraph;
	}
	
	private function formatVerse($bookName, $chapterNumber, &$contents, $verse) {
		$verseNumber = str_replace("Verse ", "", $verse['text']);
		$verseNumber = str_replace("Verses ", "", $verseNumber);
		
		$contents[] = "<h2 pb_toc=\"index\">{$chapterNumber}:{$verseNumber}</h2>";
		
		if (strtolower($verseNumber) == "introduction") {
			$contents[] = "<p><b>".substr($bookName, 0, 3)." {$chapterNumber}:{$verseNumber}</b></p>";
		} else {
			$contents[] = "<p><pb_sync type='verse' value=\"{$bookName} {$chapterNumber}:{$verseNumber}\" display=\"now\" /><b>".substr($bookName, 0, 3)." {$chapterNumber}:{$verseNumber}</b></p>";
		}
		
		// Two blank lines
		$contents[] = '';
		$contents[] = '';
		
		foreach ($verse['paragraphs'] as $paragraph) {
			$paragraph = self::formatText($paragraph);
			$contents[] = "<p>{$paragraph}</p>";
		}
	}
	public function combineBook($bookName, $chapterNumber = '') {
		$contents = array();
		$contents[] = "<h1 pb_toc=\"index\">{$bookName}</h1>";
		
		$dir = dirname(__FILE__) . "/../books/{$bookName}";
		
		if (!file_exists("{$dir}/index.txt")) {
			return array();
		}
		$index = file_get_contents("{$dir}/index.txt");
		
		$BookChapters = unserialize($index);
//		if (!isset($BookChapters['chapters'])) dump(array($bookName, $index));
		$chapters = $BookChapters['chapters'];
		
		if (!empty($chapterNumber)) {
			if (!file_exists("{$dir}/Chapter {$chapterNumber}/verses.txt"))
				continue;

			$verseIndex = file_get_contents("{$dir}/Chapter {$chapterNumber}/verses.txt");

			$unserialize = unserialize($verseIndex);
			$verses = $unserialize['verses'];
			
			foreach ($verses as $verse) {
				$this->formatVerse($bookName, $chapterNumber, $contents, $verse);
			}
		} else {
			foreach ($chapters as $chapter) {
				$chapterNumber = str_replace("Chapter ", "", $chapter['text']);
				
				if (!file_exists("{$dir}/{$chapter['text']}/verses.txt"))
					continue;
				$verseIndex = file_get_contents("{$dir}/{$chapter['text']}/verses.txt");
				
				$unserialize = unserialize($verseIndex);
				$verses = $unserialize['verses'];
				
				foreach ($verses as $verse) {
					$this->formatVerse($bookName, $chapterNumber, $contents, $verse);
				}
			}
		}
		
		
		return $contents;
	}
}

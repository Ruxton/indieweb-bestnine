<?php

namespace BestNine;

$siteUrl = $_POST['url'];
$direction = 'next';

if(!isset($siteUrl)) {
	echo "No url sent.\n";
	exit;
}
require __DIR__ . '/vendor/autoload.php';

use Mf2;
use phpFastCache\CacheManager;
const BEST_OF_YEAR = 2017;

const CACHE_EXPIRY = 3600; //3600 seconds = 1 hour
$CACHE = CacheManager::getInstance('files');

function like_sorter($a,$b) {
	$aCount = $a['likes'] + $a['replies'];
	$bCount = $b['likes'] + $b['replies'];

	if ($aCount == $bCount) {
		return 0;
	}
	return ($aCount < $bCount) ? 1 : -1;
}

function array_last($array) {
    if (empty($array)) {
        return null;
    }
    foreach (array_slice($array, -1) as $value) {
        return $value;
    }
}

function get_posts($mformat) {
	$photos = array();

	foreach ($mformat['items'] as $microformat) {
		if($microformat['type'][0] == 'h-feed') {
			$photos = $microformat['children'];
		}
	}

	return $photos;
}

function filter_to_photos($photos) {
	foreach($photos as $index => $photo) {
		$keep = false;
		if(in_array("h-entry",$photo['type'])) {
			if(in_array("h-as-image",$photo['type'])) {
				$keep = true;
			} elseif(isset($photo['properties']['photo'])) {
				$keep = true;
			}
		}
		if(!$keep) {
			unset($photos[$index]);
		}
	}

	return $photos;
}

function get_photo_url($url) {
	$mf = cache_fetch($url);
	$photo = $mf['items'][0]['properties']['photo'][0];
	return $photo;
}

function photo_interaction_count($photos) {
	$return = array();
	foreach($photos as $photo) {
		$url = $photo["properties"]["url"][0];

		$mf = cache_fetch($url);

		if(isset($mf["items"][0]["properties"]["like"])) {
			$likeCount = count($mf["items"][0]["properties"]["like"]);
		} else {
			$likeCount = 0;
		}

		if(isset($mf["items"][0]["properties"]["reply"])) {
			$replyCount = count($mf["items"][0]["properties"]["reply"]);
		} else {
			$replyCount = 0;
		}

		$return[$url] = array("likes" => $likeCount, "replies" => $replyCount);
	}

	return $return;
}

function cache_fetch($url) {
	global $CACHE;

	$key = str_replace(array("/",":"),"-",$url);

	$cachedResult = $CACHE->getItem($key);
	if (is_null($cachedResult->get())) {
		$mf = Mf2\fetch($url);
		$cachedResult->set($mf)->expiresAfter(CACHE_EXPIRY);
		$CACHE->save($cachedResult);
	} else {
		$mf = $cachedResult->get();
	}
	return $mf;
}

function find_posts($url,$firstRun=false) {
	global $direction;

	$mf = cache_fetch($url);

	if($firstRun) {
		if(!isset($mf['rels']['next']) && isset($mf['rels']['prev'])) {
			$direction = 'prev';
		}
	}

	if(isset($mf['rels'][$direction])) {
		$next = $mf['rels'][$direction]['0'];
	} else {
		$next = false;
	}
	return array(get_posts($mf),$next);
}

function indexToCoords($index)
{
 $x = ($index % 3) * (300 + 0) + 0;
 $y = floor($index / 3) * (300 + 0) + 0;
 return Array($x, $y);
}

list($posts,$next_page) = find_posts($siteUrl,true);
if(!$next_page) {
	echo "Site does not implement rel=\"next\" or rel=\"prev\"";
	exit;
}
$last_post = array_last($posts);
$dateParsed = date_parse($last_post["properties"]["published"][0]);

while($dateParsed["year"] >= BEST_OF_YEAR) {
	list($newPosts,$next_page) = find_posts($next_page);
	$last_post = array_last($newPosts);
	$dateParsed = date_parse($last_post["properties"]["published"][0]);
	$posts = array_merge($posts,$newPosts);
}

$photos = filter_to_photos($posts);


foreach($photos as $key => $photo) {
	$dateParsed = date_parse($photo["properties"]["published"][0]);
	if($dateParsed["year"] != BEST_OF_YEAR) {
		unset($photos[$key]);
	}
}

if(count($photos) < 1) {
	echo "Error, no photos found";
	exit;
} else {
	$photocount = photo_interaction_count($photos);
	uasort($photocount,'BestNine\like_sorter');

	$bestNine = array_slice($photocount, 0, 9);

	$bestNinePhotos = array();

	foreach($bestNine as $url => $one) {
		$bestNinePhotos[] = get_photo_url($url);
	}

	$mapImage = imagecreatetruecolor(900, 900);
	foreach ($bestNinePhotos as $index => $srcPath)
	{
		list ($x, $y) = indexToCoords($index);
		$tileImg = imagecreatefromjpeg($srcPath);

	  imagecopyresampled($mapImage, $tileImg, $x, $y, 0, 0, 300, 300,900,900);
	  imagedestroy($tileImg);
	}

	header('Content-Type: image/jpeg');
	imagejpeg($mapImage);
	imagedestroy($mapImage);
}


?>

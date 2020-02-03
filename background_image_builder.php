<?php
namespace Bestnine;

require __DIR__ . '/vendor/autoload.php';

$siteUrl = $argv[1];

$year = $argv[2];
$month = $argv[3];
$direction = 'next';

$siteKey = hash('sha256',trim($siteUrl.$year.$month));

$disallowedCommentAuthors = [ 'https://swarmapp.com/', 'https://martymcgui.re/' ];

use Mf2;
use phpFastCache\CacheManager;

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
		if($microformat['type'][0] == 'h-entry') {
			$photos[] = $microformat;
		}
	}
	if(count($photos) == 0) {
		$photos = $mformat['items'];
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
	global $disallowedCommentAuthors;
	$return = array();
	foreach($photos as $photo) {
		$url = $photo["properties"]["url"][0];

		$mf = cache_fetch($url);

		if(isset($mf["items"][0]["properties"]["like"])) {
			$likeCount = count($mf["items"][0]["properties"]["like"]);
		} else {
			$likeCount = 0;
		}

		if(isset($mf["items"][0]["properties"]["comment"])) {
                        $allowedReplies = array_filter(
				$mf["items"][0]["properties"]["comment"],
				function($comment) use (&$disallowedCommentAuthors) {
					return(! in_array($comment['properties']['author'][0]['properties']['url']['0'], $disallowedCommentAuthors));
				});
			$replyCount = count($allowedReplies);
		} else {
			$replyCount = 0;
		}

		$return[$url] = array("likes" => $likeCount, "replies" => $replyCount);
	}

	return $return;
}

function cache_fetch($url) {
	global $CACHE,$siteKey;
	$cacheKey = hash('sha256',$url);
	$cachedResult = $CACHE->getItem($cacheKey);
	if (is_null($cachedResult->get())) {
		$mf = Mf2\fetch($url);
		$cachedResult->set($mf)->expiresAfter(CACHE_EXPIRY);
		$CACHE->save($cachedResult);
	} else {
		$mf = $cachedResult->get();
	}
	// echo "Microformat Data: \n";
	// echo var_dump($mf);
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

function indexToCoords($index, $tilePx)
{
 $x = ($index % 3) * ($tilePx + 0) + 0;
 $y = floor($index / 3) * ($tilePx + 0) + 0;
 return Array($x, $y);
}

list($posts,$next_page) = find_posts($siteUrl,true);
if(!$next_page) {
	echo "Site does not implement rel=\"next\" or rel=\"prev\"\n";
	exit;
}
$last_post = array_last($posts);
$dateParsed = date_parse($last_post["properties"]["published"][0]);

while($dateParsed["year"] >= $year) {
	list($newPosts,$next_page) = find_posts($next_page);
	if($newPosts == null) { break; }
	$last_post = array_last($newPosts);
	$dateParsed = date_parse($last_post["properties"]["published"][0]);
	$posts = array_merge($posts,$newPosts);
}

echo "Found ".count($posts)." posts.\n";

$photos = filter_to_photos($posts);
echo "Processing ".count($photos)." photos.";

foreach($photos as $key => $photo) {
	$dateParsed = date_parse($photo["properties"]["published"][0]);
	if($dateParsed["year"] != $year) {
		unset($photos[$key]);
	}
	if($month != 0 && $dateParsed["month"] != $month) {
		unset($photos[$key]);
	}
}

if(count($photos) < 1) {
	echo "Error, no photos found\n";
	copy(__DIR__.DIRECTORY_SEPARATOR."noimage.png",__DIR__.DIRECTORY_SEPARATOR."images".DIRECTORY_SEPARATOR.$siteKey.".jpg");
	exit;
} else {
	echo "Sorting ".count($photos)." photos.";
	$photocount = photo_interaction_count($photos);
	uasort($photocount,'BestNine\like_sorter');
	$bestNine = array_slice($photocount, 0, 9);

	$bestNinePhotos = array();

	foreach($bestNine as $url => $one) {
		$bestNinePhotos[] = get_photo_url($url);
		$str = ''; $sep = '';
		foreach(array('likes' => 'like', 'replies' => 'reply') as $key => $singular) {
			if($one[$key] > 0) {
				$str .= $sep . $one[$key] . ' ' . (($one[$key] > 1) ? $key : $singular);
				$sep = ', ';
			}
		}
		print($url . (!empty($str) ? " ({$str})" : '') . "\n");
	}

	$bestNinePhotos = array_pad($bestNinePhotos,9,__DIR__.DIRECTORY_SEPARATOR."filler".DIRECTORY_SEPARATOR."unknown.png");

	$mapPx = 960;
	$mapImage = imagecreatetruecolor($mapPx, $mapPx);
	foreach ($bestNinePhotos as $index => $srcPath)
	{
		list ($x, $y) = indexToCoords($index, $mapPx / 3);
		$tileSizeMeta = getimagesize($srcPath);
		switch (exif_imagetype($srcPath)) {
			case IMAGETYPE_JPEG :
					$tileImg = imagecreatefromjpeg($srcPath);
					break;

			case IMAGETYPE_PNG :
					$tileImg = imagecreatefrompng($srcPath);
					break;

			case IMAGETYPE_GIF :
					$tileImg = imagecreatefromgif($srcPath);
					break;

			default:
				$tileImg = imagecreatefromjpeg($srcPath);
				break;
		}

		$tileWidth = $tileSizeMeta[0];
		$tileHeight = $tileSizeMeta[1];
		$minPx = ($tileWidth >= $tileHeight) ? $tileHeight : $tileWidth;
		$tileXoff = ($tileWidth > $minPx) ? (($tileWidth - $minPx) / 2) : 0;
		$tileYoff = ($tileHeight > $minPx) ? (($tileHeight - $minPx) / 2) : 0;
		imagecopyresampled($mapImage, $tileImg, $x, $y, $tileXoff, $tileYoff, $mapPx / 3, $mapPx / 3, $minPx, $minPx);
		imagedestroy($tileImg);
	}

	imagejpeg($mapImage,__DIR__.DIRECTORY_SEPARATOR."images".DIRECTORY_SEPARATOR.$siteKey.".jpg");
	imagedestroy($mapImage);
}

?>

<?php
/* Load required lib files. */
session_start();
require_once('twitteroauth/twitteroauth.php');
require_once('config.php');
require_once('functions/utility.php');

$error_string = "";	

// TODO: Implement Caching 
// $friend_id_file_name = 'cache/friends-id.txt';
// $users_file_name = 'cache/users.txt';
// 
// //Create files structure
// if(!file_exists ('cache')){
// 	mkdir('cache');
// }
// if(!file_exists ($friend_id_file_name)){
// 	$fp = fopen($friend_id_file_name,"w"); 
// 	fclose($fp);
// }
// if(!file_exists ($users_file_name)){
// 	$fp = fopen($users_file_name,"w"); 
// 	fclose($fp);
// }
 
//$current_time = time(); // Current time
 
//check if cache has expired
//if($current_time-filemtime($users_file_name) <= REFRESH_TWEET_FREQ && filesize($users_file_name) > 0) {
//	$users = unserialize(file_get_contents($users_file_name));
//} else { //renew the cache
	
	if(!file_exists ("img")){
		mkdir("img");
	}
	if(!file_exists ("img/profiles")){
		mkdir("img/profiles");
	}
	
	/* Create a TwitterOauth object with consumer/user tokens. */
	$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, HANNAH_TOKEN, HANNAH_TOKEN_SECRET);
	$timeline = $connection->get('statuses/home_timeline', array("count"=>200));
	$tweets = array();
	$sort_by = array();
	
	foreach ($timeline as $tweet) {
		// Get basic details
		$status = TwitterEntitiesLinker::getHtml($tweet);
		$created_time = strtotime($tweet->created_at);
		$rt = $tweet->retweet_count;
		if(empty($rt)) $rt = 0;

		$tpd = $tweet->user->statuses_count / ((time()-strtotime($tweet->user->created_at))/86400);

		// Get Follower/Following Ratio
		if($tweet->user->friends_count > 0){
			$fr = $tweet->user->followers_count/$tweet->user->friends_count;
		}else{
			$fr = $tweet->user->followers_count;
		}
		if(!file_exists ("img/profiles/".$tweet->user->screen_name.".jpg")){
			copy(str_replace('_normal', '_bigger', $tweet->user->profile_image_url), 'img/profiles/'.$tweet->user->screen_name.".jpg");
		}
		$tweets[] = array(	'id'=> $tweet->id_str,
							'tweet'=>$status, 
							'screen_name' => $tweet->user->screen_name, 
							'name' => $tweet->user->name,
							'profile_img' => "img/profiles/".$tweet->user->screen_name.".jpg",
							'created_time' => $created_time,
							'tweets_per_day' => $tpd,
							'min_since_tweet' => 0,
							'retweets' => $rt,
							'follow_ratio' => $fr);
		// Get key of this tweet
		end($tweets);
		$key = key($tweets);
		$last_id = $tweet->id_str;
		
		// Add relevant value to the sort_by list
		$sort_by[$key] = $tweets[$key]['tweets_per_day'];
	}
	
	// Sort users
	array_multisort($sort_by, SORT_ASC, $tweets);
	
	// Preload each image
	$dir_file_contents = "/*@pjs preload=\"";
	opendir('img/profiles');
	if ($handle = opendir('img/profiles')) {
	    while (false !== ($entry = readdir($handle))) {
			if ($entry != "." && $entry != "..") {
	        	$dir_file_contents .= 'img/profiles/'.$entry.", ";
			}
	    }
	    closedir($handle);
		$dir_file_contents = substr ( $dir_file_contents , 0, -2 )."\";*/\n";
		file_put_contents('directives.pde', $dir_file_contents);
	}
	
	
	$js_array = json_encode($tweets);
		
		// Cache user details
		//file_put_contents($users_file_name, serialize($users));
//	}
//}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Twitter Bubbles</title>
    <link rel="stylesheet" href="css/default.css">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--[if IE]>
        <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
	<script type="text/javascript" src="js/jquery-1.7.1.min.js"></script>
    <script type="text/javascript" src="js/processing-1.4.1.js"></script>
    <script type="text/javascript">
		var tId,pjs,cnt=0;
		$(document).ready(function(){
			<?php echo "var javascript_array = eval(". $js_array . ");\n"; ?>
			tId=setInterval(function() {
			    pjs = Processing.getInstanceById('bubble_stream');
			
				pjs.createBubbles($('body').width(),$('body').height()-<?php echo $header_height+10;?>, javascript_array, true);
				
				$('.container').width($('body').width());
				$('.container').height($('body').height()-<?php echo $header_height+10;?>);
			    if (pjs) clearInterval(tId);
			  },500);
			
		});
    </script>
</head>

<body class="bubbles">
	<div class="header_bar">
		<a href="" class="active">bubbles</a>
		<a href="friends.php">friends</a>
	</div>
	<div class="container">
		<canvas id="bubble_stream" data-processing-sources="directives.pde bubbles.pde"></canvas>
	</div>
</body>
</html>
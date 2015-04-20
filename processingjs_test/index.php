<?php
session_start();
require_once('twitteroauth/twitteroauth.php');
require_once('config.php');
require_once('utility.php');

$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, HANNAH_TOKEN, HANNAH_TOKEN_SECRET);
$timeline = $connection->get('statuses/home_timeline');
$tweets = array();
if(!file_exists ("img")){
	mkdir("img");
}
if(!file_exists ("img/profiles")){
	mkdir("img/profiles");
}
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
	$tweets[$tweet->id_str] = array(	'tweet'=>$status, 
										'screen_name' => $tweet->user->screen_name, 
										'name' => $tweet->user->name,
										'profile_img' => "img/profiles/".$tweet->user->screen_name.".jpg",
										'created_time' => $created_time,
										'tweets_per_day' => $tpd,
										'min_since_tweet' => 0,
										'retweets' => $rt,
										'follow_ratio' => $fr);
}

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
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>My Processing Page</title>
    <script type="text/javascript" src="jquery-1.7.1.min.js"></script>
    <script type="text/javascript" src="processing-1.4.1.js"></script>
    <script type="text/javascript">
		var tId,pjs,cnt=0;
		$(document).ready(function(){
			<?php echo "var javascript_array = eval(". $js_array . ");\n"; ?>
			tId=setInterval(function() {
			    pjs = Processing.getInstanceById('bubble_stream');
			
				pjs.createBubbles($('body').width(),$('body').height(), javascript_array);
				
				$('div').width($('body').width());
				$('div').height($('body').height());
			    if (pjs) clearInterval(tId);
			  },500);
			//var pjs = Processing.getInstanceById('mysketch2');
			//var text = "testing";
		    //pjs.drawText(text);
			// bindJavascript();
			// 		$(window).on('resize', resizeSketches);
			// handle page resizing
			/*$(window).resize(function() {
				var pjs = Processing.getInstanceById('bubble_stream');
				pjs.resizeSketch($('body').width(),$('body').height());
			} );*/
		});
    </script>
	<style type="text/css" media="screen">
		html, body{
			width:100%;
			height:100%;
			margin:0;
			padding:0;
		}
		canvas:focus{
			outline: 0;
		}
		div{
			margin:0 auto;
		}
	</style>
  </head>
  <body>
    <div>
    	<canvas id="bubble_stream" data-processing-sources="directives.pde bubbles_scale.pde"></canvas>
    </div>
 		<!-- <input type="textfield" value="my text" id="inputtext">
               <button type="button" onclick="drawSomeText('mysketch2')">place</button> -->
    </body>
</html>
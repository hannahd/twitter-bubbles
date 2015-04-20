<?php
/* Load required lib files. */
session_start();
require_once('twitteroauth/twitteroauth.php');
require_once('config.php');
require_once('functions/utility.php');

$error_string = "";	

$friend_id_file_name = 'cache/friends-id.txt';
$users_file_name = 'cache/users.txt';

//Create files structure
if(!file_exists ('cache')){
	mkdir('cache');
}
if(!file_exists ($friend_id_file_name)){
	$fp = fopen($friend_id_file_name,"w"); 
	fclose($fp);
}
if(!file_exists ($users_file_name)){
	$fp = fopen($users_file_name,"w"); 
	fclose($fp);
}
 
$current_time = time(); // Current time
 
//check if cache has expired
if($current_time-filemtime($users_file_name) <= REFRESH_TWEET_FREQ && filesize($users_file_name) > 0) {
	$users = unserialize(file_get_contents($users_file_name));
} else { //renew the cache
	/* Create a TwitterOauth object with consumer/user tokens. */
	$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, HANNAH_TOKEN, HANNAH_TOKEN_SECRET);
	$raw_users = array();
	$users = array();
	$sort_by = array();
	$user_id_csv = "";
	
	if($current_time-filemtime($friend_id_file_name) <= REFRESH_FRIEND_FREQ && filesize($friend_id_file_name) > 0){
		$user_id_csv = file_get_contents($friend_id_file_name);
	}else{
		// Fetch the user's friends from the TwitterAPI
		$user_ids = $connection->get('friends/ids');
		$user_id_csv = "";
		// Create CSV list with ids
		foreach ($user_ids->ids as $id) {
			$user_id_csv .= $id.",";
		}
		// Remove the last comma
		if($user_id_csv != "") substr($user_id_csv, 0, -1);
		
		// Save list to cache
		file_put_contents($friend_id_file_name, $user_id_csv);
	}
	
	if($user_id_csv == ""){
		$error_string = "You do are not following anyone.  Please follow some users on Twitter in order to use Twitter Bubbles.";
	}else{
		// Get user details from the TwitterAPI
		$raw_users = $connection->get('users/lookup', array('user_id' => $user_id_csv, 'include_entities' => true));
		
		// Pull relevant data
		foreach ($raw_users as $u) {
			
			// Get basic details
			if(isset($u->status)){
				$tweet = TwitterEntitiesLinker::getHtml($u->status);
				$created_time = strtotime($u->status->created_at);
				$rt = $u->status->retweet_count;
				if(empty($rt)) $rt = 0;
				$tweet_id = $u->status->id_str;
			}else{
				$tweet = "<span class='soft'>There aren't any tweets from this user yet.</span>";
				$created_time = 0;
				$rt = 0;
				$tweet_id = 0;
			}
			
			// Get Tweets Per Day
			$tpd = $u->statuses_count / ((time()-strtotime($u->created_at))/86400);
			if($tpd >= 1 || $tpd < .1){
				$tpd = number_format($tpd,0);
			}else{
				$tpd = number_format($tpd,1);
			}
			
			// Get Follower/Following Ratio
			if($u->friends_count > 0){
				$fr = $u->followers_count/$u->friends_count;
				if($fr >= 1 || $fr < .01){
					$fr = number_format($fr,0, ".", "");
				}else{
					$fr = number_format($fr,1);
				}
			}else{
				$fr = $u->followers_count;
				if($fr >= 1){
					$fr = number_format($fr,0, ".", "");
				}else if($fr < .1){
					$fr = 0;
				}else{
					$fr = number_format($fr,1);
				}
			}
	
			$users[] = array(	'screen_name' => $u->screen_name, 
								'name' => $u->name, 
								'profile_img' => str_replace('_normal', '_bigger', $u->profile_image_url),
								'last_tweet' => $tweet,
								'last_tweet_id' => $tweet_id,
								'created_time' => $created_time,
								'tweets_per_day' => $tpd,
								'min_since_tweet' => 0,
								'retweets' => $rt,
								'follow_ratio' => $fr 
								);
								
			// Get key of this user
			end($users);
			$key = key($users);
			
			// Add relevant value to the sort_by list
			$sort_by[$key] = $users[$key]['created_time'];
		}
		
		// Sort users
		array_multisort($sort_by, SORT_DESC, $users);
		
		// Cache user details
		file_put_contents($users_file_name, serialize($users));
	}
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Friends on Twitter Bubbles</title>
    <link rel="stylesheet" href="css/default.css">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--[if IE]>
        <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
</head>

<body class="bg-bubbles">
	<div class="header_bar">
		<a href="bubbles.php">bubbles</a>
		<a href="" class="active">friends</a>
	</div>
	<div class="container">
		<ul>
			<?php
				foreach ($users as $u) {
					//echo(print_to_console($profile_img));
					$time_ago = get_formatted_date_dif($u['created_time']);
					?>
					
					<li>
						<a href="https://twitter.com/
						<?php
							if(intval($u['last_tweet_id'])>0){
								echo $u['screen_name'] .'/status/'.$u['last_tweet_id'];
							}else{
								echo $u['screen_name'];
							}
						?>" target="_blank" class="profile_pic"><img src="<?echo $u['profile_img'];?>" width="100%"/></a>
						<div class="tweet">
							<div class="tweet_content">
								<h3><a href="https://twitter.com/<?echo $u['screen_name'];?>" target="_blank"><strong><?echo $u['name'];?></strong> <em>@<?echo $u['screen_name'];?></em></a></h3>
								<span><?echo $time_ago;?></span>
								<p><?echo $u['last_tweet'];?></p>
							</div>
							<div class="tweet_stats">
								<span class="data"><?echo $u['tweets_per_day'];?>
								<span class="label">average tweets per&nbsp;day</span></span>
								<!-- <span class="data"><?echo $u['min_since_tweet'];?>
																<span class="label">minutes since last&nbsp;tweet</span></span> -->
								<span class="data"><?echo $u['retweets'];?>
								<span class="label">retweets</span></span>
								<span class="data"><?echo $u['follow_ratio'];?> 
								<span class="label">follower to following ratio</span></span>
							</div>
						</div>
					</li>
					<?php
				}
			?>
		</ul>
	</div>
	
	<div class="footer">
		Twitter Bubbles is an experiment by <a href="http://www.hannahdeering.com">Hannah Deering</a> to turn down social media noise and emphasize quieter voices. 
	</div>
	
</body>
</html>
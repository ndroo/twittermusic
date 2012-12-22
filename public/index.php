<pre>
<?php

$mentioned_handles = array();

require_once("../libs/twittermusic/reader.class.php");

//tweets to get?
$count = 1;
$handle= "triplejplays";

$reader = new Reader($handle);
$contents = $reader->GetFeed($handle,$count);

//convert to object
$tweets = json_decode($contents);
foreach($tweets as $tweet)
{
	$track = array();
	/*
	   construct track info
	   - artist twitter handle
	   - artist name
	   - track title
	   - time played
	*/

	//extract the artists name OR twitter handle
	$basic_info = explode(" - ",$tweet->text);

	//gather the artist info
	if(strstr($basic_info[0],".@"))
	{
		//are there multiple handles?
		$handles = explode(" & ",$basic_info[0]);
		foreach($handles as $handle)
		{
			$artist = array();
			//found a handle!
			$handle = str_replace(".@","",$handle);
			$artist['twitter'] = $handle;
			$user_info = json_decode($reader->GetUserInfo($handle));
			$artist['name'] = $user_info[0]->user->name;
			
			//add to the artist list
			$track['artists'][] = $artist;
		}

		//curl the api (or our cache) for the artists name
	}
	else
	{
		$artist = array();
		$artist['twitter'] = $null;
		$artist['name'] = $basic_info[0];

		//add to the artist list
		$track['artists'][] = $artist;
	}

	//extract the title and time played
	$song_info = explode(" [",$basic_info[1]);
	$track['title'] = $song_info[0];
	$track['time_played'] = str_replace("]","",$song_info[1]);
	$track['source_date'] = $tweet->created_at;

	//friendly name
	if(isset($track['artists'][0]['name']))
	{
		$track['friendly_title'] = $track['artists'][0]['name'] . " - " . $track['title'];
	}
	else
	{
		$track['friendly_title'] = $track['artists'][0]['twitter'] . " - " . $track['title'];
	}

	$track['twitter_id'] = $tweet->id;
	$track['twitter_text'] = $tweet->text;
	$track['track_hash'] = md5($track['friendly_title']);

	//add to the list of tracks
	$tracks[] = $track;


	//connect to the db
	$dbserver = "dev.twittermusic.com";
	$link = mysql_pconnect("$dbserver", "root", "root");
	mysql_select_db('twittermusic');

	//insert the track to the track table (if it doesnt already exist)
	$new_track = false;
	$sql = "select * from `tracks` where hash = '".mysql_escape_string($track['track_hash'])."' limit 1;";
	$res = mysql_query($sql);
	if(mysql_num_rows($res) == 0)
	{
		//we didnt know about this track, lets add it!
		$sql = "insert into `tracks` (hash,friendly_title,timestamp_added,raw) values('".mysql_escape_string($track['track_hash'])."','".mysql_escape_string($track['friendly_title'])."','".time()."','".mysql_escape_string(json_encode($track))."');";
		mysql_query($sql);
		$new_track = true;
	}

	//log that it was played (if we have not already logged this)
	//get the latest track that was played
	$play_logged = false;
	$sql = "select * from `play_log` order by timestamp desc limit 1;"; 
	$res = mysql_query($sql);
	$obj = mysql_fetch_object($res);
	if($obj->track_hash != $track['track_hash'])
	{
		$sql = "insert into `play_log` (track_hash,timestamp) values('".mysql_escape_string($track['track_hash'])."','".time()."');";
		mysql_query($sql);
		$play_logged = true;
	}

	//log if this was a new track and if we logged it as played
	$track['new_track'] = $new_track;
	$track['play_logged'] = $play_logged;
}

print_r($track);

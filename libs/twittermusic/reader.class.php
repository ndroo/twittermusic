<?php

class Reader
{
	private $_handle;
	private $_api_url;

	public function __construct($handle)
	{
		$this->_handle = $handle;
		$this->_api_url = "https://api.twitter.com/1/";
	}

	public function GetFeed($handle,$count = 200)
	{
		//support using a custom handle
		if($handle == null)
			$handle = $this->_handle;

		$contents = $this->Query($handle,$count,false);
		return $contents;
	}

	private function Query($screen_name,$count = 0,$include_rts = false)
	{
		return file_get_contents($this->_api_url . "statuses/user_timeline.json?screen_name=$screen_name&count=$count&include_rts=$include_rts");
	}

	public function GetUserInfo($handle)
	{
		//is in cache?
		if(isset($cache[$handle]))
		{
			return $cache[$handle];
		}
		else
		{
			//cache it
			$result =  $this->GetFeed($handle,0);
			$cache[$handle] = $result;
			return $result;
		}

	}
}

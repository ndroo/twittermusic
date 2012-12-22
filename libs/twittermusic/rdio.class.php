<?php

class Rdio
{
	private $_key;
	private $_secret;
	private $_api_url;

	public function __construct($key = "",$secret = "",$api_url = null)
	{
		$this->_key = $key;
		$this->_secret = $secret;
		if($api_url == null)
		{
			$this->_api_url = "http://api.rdio.com/1/";
		}
		else
		{
			$this->_api_url = $api_url;
		}

	}

	public function Search($search_string)
	{
		$fields = array();
		$fields['types'] = "Tracks";
		$fields['query'] = $search_string;
		$fields['method'] = "search";
		$result = $this->Query("",$fields);
	}

	public function Query($url_vars,$fields)
	{
		//set POST variables
		$url = $this->_api_url . "?$url_vars";

		//url-ify the data for the POST
		foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
		rtrim($fields_string, '&');

		//open connection
		$ch = curl_init();

		//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_POST, count($fields));
		curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);

		//execute post
		$result = curl_exec($ch);

		//close connection
		curl_close($ch);

		return $result;
	}

	public function GetToken()
	{
		/*
		   response = client.request('http://api.rdio.com/1/', 'POST', urllib.urlencode({'method': 'get', 'keys': 'a184236,a254895,a242205'}))
		   */
		$fields = array();
		$fields['method'] = "get";
		$fields['keys'] = $this->_key;
		print_r($fields);
		$result = $this->Query("",$fields);
	}
}

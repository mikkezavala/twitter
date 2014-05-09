<?php namespace Mikke\Twitter;
/*
* Mikke Zavala!
* Please do not steal, collaborate :)
* Takes a bit from https://code.google.com/p/oauth/ in the helper tools!
*/
class Twitter{
	/**
	* The Application APi URL.
	*
	* @var string
	*/
	protected $api_url = 'https://api.twitter.com/';
	/**
	* The Application APi URL.
	*
	* @var string
	*/
	protected $api_callback = '';
	/**
	* The Application APi Method.
	*
	* @var string
	*/
	protected $api_method = 'POST';	
	/**
	* The Application Current Endpoint.
	*
	* @var string
	*/
	protected $api_endpoint = '/';
	/**
	* The Application Current Request URL.
	*
	* @var string
	*/
	protected $api_request_url = '';
	/**
	* The Application Api Key.
	*
	* @var string
	*/
	protected $api_key = '';
	/**
	* The Application Api Secret.
	*
	* @var string
	*/
	protected $api_secret = '';
	/**
	* The Application Access Token.
	*
	* @var string
	*/
	protected $api_token = '';
	/**
	* The Application Access Token Secret.
	*
	* @var string
	*/
	protected $api_token_secret = '';	
	/**
	* The Oauth Signature.
	*
	* @var mixed
	*/
	protected $oauth_signature = '';	
	/**
	* The Request Header.
	*
	* @var mixed
	*/
	protected $api_headers = array();
	/**
	* The Api Body.
	*
	* @var mixed
	*/
	protected $api_body = array();
	
	
	/**
	* The Setting up with user credentials.
	*
	* @var array
	*/	
	public function __construct($setup = array()){
		if(is_array($setup) && sizeof($setup) > 0){
			$this->api_key = array_get($setup, 'api_key');
			$this->api_secret = array_get($setup, 'api_secret');
			$this->api_token = array_get($setup, 'api_token');
			$this->api_token_secret = array_get($setup, 'api_token_secret');
			$this->api_callback = array_get($setup, 'api_callback');
			
			$this->api_url = array_get($setup, 'api_url', $this->api_url);
			$this->time_stamp = time();
		}else{
			throw new \Exception('Incomplete Settings');
		}
	}
	public function requestToken(){
		$this->api_endpoint = 'oauth/request_token';
		$this->api_request_url = join(array($this->api_url, $this->api_endpoint));
		
		$request_opts = array(
							'oauth_callback'			=> $this->api_callback,
							'oauth_consumer_key'		=> $this->api_key,
							'oauth_nonce'				=> $this->generate_nonce(),							
							'oauth_signature_method'	=> 'HMAC-SHA1',
							'oauth_timestamp'			=> $this->time_stamp,
							'oauth_token'				=> $this->api_token,
							'oauth_version'				=> '1.0'
						);
		$this->oauth_signature = $this->make_signature($request_opts);
		array_set($request_opts, 'oauth_signature', $this->oauth_signature);
		uksort($request_opts, 'strcmp');
		
		$auth_nodes = array();
		foreach($request_opts as $k => $v){
			$auth_nodes[] = $k.'="'.$v.'"';
		}
		$this->api_headers[] = 'Authorization: OAuth '.implode(', ', $auth_nodes);
		$tokens = array();
		parse_str($this->callService(), $tokens);

		if(is_array($tokens) && sizeof($tokens) > 0){
			$this->api_token = array_get($tokens, 'oauth_token');
			$this->api_token_secret = array_get($tokens, 'oauth_token_secret');
		}
		
		return (true);

	}
	public function authenticate(){
		//Tokens!
		if(empty($this->api_token)){
			$this->requestToken();
		}
		
		$this->api_endpoint = 'oauth/authenticate';
		$this->api_request_url = join(array($this->api_url, $this->api_endpoint)).'?oauth_token='.$this->api_token;
		
		ob_clean();
		header('Location: '.$this->api_request_url);
		exit;
		return(true);		
	}
	public function authorize(){
		//Tokens!
		if(empty($this->api_token)){
			$this->requestToken();
		}
		
		$this->api_endpoint = 'oauth/authorize';
		$this->api_request_url = join(array($this->api_url, $this->api_endpoint)).'?oauth_token='.$this->api_token;
		
		ob_clean();
		header('Location: '.$this->api_request_url);
		exit;
		return(true);
	}
	/*! 
	* Usually you want to save this credentials!
	* use this Tokens response to make call to TW API
	* This already saves it for this instance to call the services 
	* in this wrapper
	*/
	public function access_token($oauth_verifier = ''){
		$this->api_endpoint = 'oauth/access_token';
		$this->api_request_url = join(array($this->api_url, $this->api_endpoint));
		
		$request_opts = array(
							'oauth_consumer_key'		=> $this->api_key,
							'oauth_nonce'				=> $this->generate_nonce(),							
							'oauth_signature_method'	=> 'HMAC-SHA1',
							'oauth_timestamp'			=> $this->time_stamp,
							'oauth_token'				=> $this->api_token,
							'oauth_version'				=> '1.0'
						);
		$this->oauth_signature = $this->make_signature($request_opts);
		array_set($request_opts, 'oauth_signature', $this->oauth_signature);
		uksort($request_opts, 'strcmp');
		
		$auth_nodes = array();
		foreach($request_opts as $k => $v){
			$auth_nodes[] = $k.'="'.$v.'"';
		}
		
		$this->api_headers[] = 'Authorization: OAuth '.implode(', ', $auth_nodes);
		$tokens = array();
		
		$this->api_body = $this->make_params(array('oauth_verifier' => $oauth_verifier));
		
		parse_str($this->callService(), $tokens);


		if(is_array($tokens) && sizeof($tokens) > 0){			
			$this->api_token = array_get($tokens, 'oauth_token');
			$this->api_token_secret = array_get($tokens, 'oauth_token_secret');
			
		}
		return ($tokens);
	}
	/*!
	* 
	* Just to get the user info :)
	*
	*/
	public function user_data(){
		$this->api_headers = array();
		$this->api_body = '';
		
		$this->api_method = 'GET';
		$this->api_endpoint = '1.1/account/verify_credentials.json';
		$this->api_request_url = join(array($this->api_url, $this->api_endpoint));
		
		
		$request_opts = array(
							'oauth_consumer_key'		=> $this->api_key,
							'oauth_nonce'				=> $this->generate_nonce(),							
							'oauth_signature_method'	=> 'HMAC-SHA1',
							'oauth_timestamp'			=> $this->time_stamp,
							'oauth_token'				=> $this->api_token,
							'oauth_token_secret'		=> $this->api_token_secret,
							'oauth_version'				=> '1.0'
						);
			
		$this->oauth_signature = $this->make_signature($request_opts);
		array_set($request_opts, 'oauth_signature', $this->oauth_signature);
		uksort($request_opts, 'strcmp');		
		$auth_nodes = array();
		foreach($request_opts as $k => $v){
			$auth_nodes[] = $k.'="'.$v.'"';
		}
		
		$this->api_headers[] = 'Authorization: OAuth '.implode(', ', $auth_nodes);
		
		return (json_decode($this->callService()));	
	}
	/*!
	 * Call to the server
	 *
	 * @param  array  $parameters
	 * @return mixed
	 */
	private function callService($params = array()){
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, $this->api_request_url);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->api_headers);
		
		if($this->api_method == 'POST'){
			curl_setopt($ch, CURLOPT_POST, TRUE);
		}
		if($this->api_body != ''){
			curl_setopt($ch,CURLOPT_POSTFIELDS, $this->api_body);
		}
		
		$res = curl_exec($ch);		
		
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$header = substr($res, 0, $header_size);
		$body = substr($res, $header_size);

		
		curl_close($ch);
		
		return($body);
	}
	/*!
	* Just a parser for params
	*/
	private function make_params($params){
		//Lazy guy!
		$to_implode = array();
		foreach($params as $k => $v){
			$to_implode[] = $k.'='.$v;
		}
		return(implode('&', $to_implode));
	}
	
	/*!
	* Thanks! https://code.google.com/p/oauth/
	* Just a simple gen for the timestamps
	*/
	private function generate_nonce() {
		$mt = microtime();
		$rand = mt_rand();
		return md5($mt.$rand);
	}
	/*!
	* Encode rfc3986
	* Thanks! https://code.google.com/p/oauth/
	*/
	private function std_urlencode($input){
	  if(is_array($input)){
	    return array_map(array($this, 'std_urlencode'), $input);
	  }else if (is_scalar($input)){
	    return str_replace('+',' ', str_replace('%7E', '~', rawurlencode($input)));
	  } else { return ''; }
	}
	/*!
	* Signature Process :s!
	*/
	private function make_signature($params) {
		uksort($params, 'strcmp');
		$keys = implode('&', $this->std_urlencode(array($this->api_secret, $this->api_token_secret)));	

		$signature = $this->std_urlencode(array($this->api_method, $this->api_request_url));
		$signature[] = $this->std_urlencode(http_build_query($params));
		$this->oauth_signature = implode('&', $signature);
		
		$this->oauth_signature = base64_encode(hash_hmac('sha1', $this->oauth_signature, $keys, true));
		
		return $this->std_urlencode($this->oauth_signature);
	}
}

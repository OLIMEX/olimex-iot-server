<?php 

class IoT {
	
	const DEFAULT_USER     = 'olimex';
	const DEFAULT_PASSWORD = 'olimex';
	
	protected $ip       = NULL;
	protected $user     = NULL;
	protected $password = NULL;
	
	public function __construct($ip, $user = IoT::DEFAULT_USER, $password = IoT::DEFAULT_PASSWORD) {
		if (empty($ip)) {
			throw new Exception('Invalid IoT node IP');
		}
		
		if (empty($user) || empty($password)) {
			throw new Exception('Missing IoT node user/password');
		}
		
		$this->ip       = $ip;
		$this->user     = $user;
		$this->password = $password;
	}
	
	public function user($user = NULL) {
		if (func_num_args() == 0) {
			return $this->user;
		}
		$this->user = $user;
	}
	
	public function password($password = NULL) {
		if (func_num_args() == 0) {
			return $this->password;
		}
		$this->password = $password;
	}
	
	protected function request($options) {
		$curl = curl_init();
		curl_setopt_array(
			$curl, 
			array_replace(
				$options,
				array(
					CURLOPT_HTTPAUTH        => CURLAUTH_BASIC,
					CURLOPT_USERPWD         => $this->user.':'.$this->password,
					CURLOPT_TIMEOUT	        => 5,
					CURLOPT_RETURNTRANSFER  => TRUE,
					CURLOPT_SSL_VERIFYPEER  => FALSE,
				)
			)
		);
		
		$response = curl_exec($curl);
		if ($response === FALSE) {
			throw new Exception(curl_error($curl), curl_errno($curl));
		}
		$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
		
		if ($http_code != 200) {
			if ($http_code == 401) {
				throw new Exception('Unauthorized.', 401);
			}
			throw new Exception('HTTP Status '.$http_code, $http_code);
		}
		
		if (empty($response)) {
			throw new Exception('Empty response received');
		}
		
		return json_decode($response);
	}
	
	protected function response($response) {
		if (empty($response->Status) || $response->Status != 'OK') {
			if (!empty($response->Error)) {
				throw new Exception($response->Error);
			}
			throw new Exception('IoT failure.');
		}
		
		return $response;
	}
	
	public function get($url) {
		return $this->response(
			$this->request(
				array( 
					CURLOPT_URL        => 'http://'.$this->ip.$url,
					CURLOPT_HTTPGET    => TRUE,
				)
			)		
		);
	}
	
	public function post($url, array $data) {
		return $this->response(
			$this->request(
				array( 
					CURLOPT_URL        => 'http://'.$this->ip.$url,
					CURLOPT_POST       => TRUE,
					CURLOPT_POSTFIELDS => json_encode($data, JSON_FORCE_OBJECT),
				)
			)
		);
	}
}

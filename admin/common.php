<?php 

requireDirectory(realpath(__DIR__.'/class'));

Config::init(
	realpath(__DIR__.'/../config.json')
);
Config::system('data', realpath(__DIR__.'/../data'));

Cryptography::init(
	Config::system('data').'/keys.json'
);

function requireDirectory($path) {
	$directory = new \DirectoryIterator($path);
	foreach ($directory as $entry) {
		if ($entry->isDot()) continue;
		
		if ($entry->isFile() && $entry->getExtension () == 'php') {
			require_once $entry->getRealPath();
		}
	}
	
	foreach ($directory as $entry) {
		if ($entry->isDot()) continue;
		if ($entry->isDir()) {
			requireDirectory($entry->getRealPath());
		}
	}
}

function HandleError(Exception $e) {
	echo '<div class="status error">';
	switch ($e->getCode()) {
		case 400 :
		case 401 :
		case 404 :
			header('Status: '.$e->getCode().' '.$e->getMessage(), TRUE, $e->getCode());
			echo $e->getMessage();
		break;
		
		default:
			if (DEVELOPMENT) {
				echo $e->getMessage();
			} else {
				echo 'Something went wrong!';
			}
		break;
	}
	echo '<br/><br/>';
	echo '<button type="button">Back</button>';
	echo '</div>';
}

function server_url() {
	return 'http'.(Request::isHTTPS() ? 's' : '').'://'.Request::host();
}

function prmGET($name, $empty = NULL, $notset = NULL) {
	if (isset($_GET[$name])) {
		return (empty($_GET[$name]) ?
			$empty
			:
			$_GET[$name]
		);
	}
	
	if (func_num_args() < 3) {
		return $empty;
	}
	return $notset;
}

function prmPOST($name, $empty = NULL, $notset = NULL) {
	if (isset($_POST[$name])) {
		return (empty($_POST[$name]) ?
			$empty
			:
			$_POST[$name]
		);
	}
	
	if (func_num_args() < 3) {
		return $empty;
	}
	return $notset;
}

function sessPOST($name, $empty = NULL, $notset = NULL) {
	if (!isset($_SESSION['post'])) {
		if (func_num_args() < 3) {
			return $empty;
		}
		return $notset;
	}
	
	if (isset($_SESSION['post'][$name])) {
		return (empty($_SESSION['post'][$name]) ?
			$empty
			:
			$_SESSION['post'][$name]
		);
	}
	
	if (func_num_args() < 3) {
		return $empty;
	}
	return $notset;
}

function random_str($length = 32) {
	$bin = str_split(openssl_random_pseudo_bytes($length));
	$str = '';
	foreach ($bin as $c) {
		if (
			($c >= '0' && $c <= '9') ||
			($c >= 'A' && $c <= 'Z') ||
			($c >= 'a' && $c <= 'z')
		) {
			$str .= $c;
		} else {
			if (rand(1, 100) > 50) {
				$str .= bin2hex($c);
			} else {
				$str .= strtoupper(bin2hex($c));
			}
		}
	}
	
	return $str;
}

function modifyDate(DateTime $date, $modify) {
	$modifyed = clone $date;
	$modifyed->modify($modify);
	return $modifyed;
}

function nodeREQUEST($url, $data = NULL, User $user = NULL) {
	if (empty($user)) {
		$user = UserManager::current();
	}
	
	if (empty($user)) {
		// Unauthorized
		return FALSE;
	}
	
	$url .= '/api-key/'.$user->apiKey();
	$http_options = array(
		'timeout' => 5 // in seconds
	);
	
	if (!empty($data)) {
		$http_options['method']  = 'POST';
		$http_options['header']  = 'Content-Type: application/json';
		$http_options['content'] = json_encode($data, JSON_FORCE_OBJECT);
	}
	
	$context = stream_context_create(
		array(
			'http' => $http_options
		)
	);
	
	try {
		return @json_decode(
			@file_get_contents(
				'http://localhost'.$url,
				FALSE,
				$context
			)
		);
	} catch (Exception $e) {
		return FALSE;
	}
}

function nodeGET($url, User $user = NULL) {
	return nodeREQUEST($url, NULL, $user);
}

function nodePOST($url, $data, User $user = NULL) {
	return nodeREQUEST($url, $data, $user);
}

function execute($command, $debug = FALSE) {
	$output = array();
	exec('sudo '.$command, $output, $status);
	if ($debug) {
		echo '<pre>';
		echo 'Command: '.$command."\n";
		echo 'Status: '.$status."\n";
		echo 'Output: '."\n";
		echo join("\n", $output);
		echo '</pre>';
	}
	
	return ($status == 0 ?
		$output
		:
		FALSE
	);
}

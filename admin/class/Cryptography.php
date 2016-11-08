<?php 

class Cryptography {
	
	protected static $privateKey = NULL;
	protected static $publicKey  = NULL;
	
	public static function init($keys_file) {
		if (file_exists($keys_file)) {
			try {
				$data = @json_decode(file_get_contents($keys_file));
				if (!empty($data->privateKey) && !empty($data->publicKey)) {
					self::$privateKey = openssl_pkey_get_private($data->privateKey);
					self::$publicKey  = openssl_pkey_get_public($data->publicKey);
					
					if (self::$privateKey === FALSE || self::$publicKey === FALSE) {
						throw new Exception();
					}
				} else {
					throw new Exception();
				}
			} catch (Exception $e) {
				throw new Exception('Failed to load Cryptography keys');
			}
			
			return;
		}
		
		$config = array(
			'digest_alg'       => 'sha512',
			'private_key_bits' => 2048,
			'private_key_type' => OPENSSL_KEYTYPE_RSA,
		);
			
		// Create the private and public key
		$keys = openssl_pkey_new($config);
		
		// Extract the private key from $keys to $privateKey
		if (openssl_pkey_export($keys, $privateKey) === FALSE) {	
			throw new Exception('Can not export Private Key');
		}
		
		// Extract the public key from $keys to $publicKey
		$details = openssl_pkey_get_details($keys);
		if (empty($details)) {
			throw new Exception('Can not export Public Key');
		}
		$publicKey = $details['key'];
		
		if (
			file_put_contents(
				$keys_file, 
				json_encode(
					array(
						'privateKey' => $privateKey, 
						'publicKey'  => $publicKey
					),
					JSON_FORCE_OBJECT
				)
			) === FALSE
		) {
			throw new Exception('Failed to save Cryptography keys');
		}
		chmod($keys_file, 0600);
		self::init($keys_file);
	}
	
	public static function seal($data) {
		openssl_seal(
			$data,
			$message,
			$keys,
			array(self::$publicKey)
		);
		
		return bin2hex($message).':'.bin2hex($keys[0]);
	}
	
	public static function open($data) {
		list(
			$message,
			$key
		) = array_pad(preg_split('/:/', $data), 2, NULL);
		
		openssl_open(
			hex2bin($message),
			$decrypted,
			hex2bin($key),
			self::$privateKey
		);
		
		return $decrypted;
	}
}

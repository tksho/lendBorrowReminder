<?php

class Util {

        public static $instance;
        public static function getInstance() {
                if (self::$instance === null) {
                        self::$instance = new self();
                }
                return self::$instance;
        }

	/**
	 * Create random string
	 * 
	 * Creating random string fromo user id.
	 * 
	 * @param int Lenght of string  
	 * @retun string Random string
	 */
	public static function getRandomString( $byte = 16) {
		$byte = (int) $byte / 2;
		return bin2hex(openssl_random_pseudo_bytes($byte));
	}

	public static function getPasswordHash($string) {
		return password_hash($string, PASSWORD_BCRYPT, ['cost'=> 11]);
	}

	public static function createRsaKeyPair() {

		$config = array(
			'digest_alg'       => 'sha512',
			'private_key_bits' => 2048,
			'private_key_type' => OPENSSL_KEYTYPE_RSA
		);

		$res = openssl_pkey_new($config);
		openssl_pkey_export($res, $privKey);
		$pubKey = openssl_pkey_get_details($res);
		$pubKey = $pubKey["key"];

		$key = new stdClass();
		$key->public  = $pubKey;
		$key->private = $privKey;

		return $key;
	}
	
	public static function createProviderId($id, $salt) {
		return preg_replace('/(\w{10})(\w{5})(\w{5})(\w{10})(\w{10})/', 'provider:${1}-${2}-${3}-${4}-${5}', sha1($id . self::getRandomString(10) . $salt ));
	}
/**	
	public static createID($id) {
		return preg_replace('/(\w{8})(\w{4})(\w{4})(\w{4})(\w{12})/', '${1}-${2}-${3}-${4}-${5}', $id);
	}
**/
}

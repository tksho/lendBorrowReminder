<?php

class Route {

	public static $params = [];
	public static $gets = [];
	public static $posts = [];
	public static $patchs = [];
	public static $deletes = [];
	public static $any = [];
	public static $instance;
	public static function getInstance() {
 		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	function __construct() {
	}

	function __destruct() {
	}

	public static function patch($url, $callback) {
		return self::set('patch', $url, $callback);
	}

	public static function delete($url, $callback) {
		return self::set('delete', $url, $callback);
	}

	public static function post($url, $callback) {
		return self::set('post', $url, $callback);
	}

	public static function get($url, $callback) {
		return self::set('get', $url, $callback);
	}

	public static function set($request, $url , $callback) {
		$obj = new stdClass();
		$obj->route = $url;
		$obj->request = $request;
		$obj->path  = $url;
		$obj->slugs = [];
		$obj->callback = $callback;
		switch( $request ) {
			case 'get' :
				self::$gets[] = $obj;
				break;
			case 'post' :
				self::$posts[] = $obj;
				break;
			case 'patch' :
				self::$patchs[] = $obj;
				break;
			case 'delete' :
				self::$deletes[] = $obj;
				break;
			default :
				//
		}
		self::$any[] = $obj;
		return self::getInstance();
	}
	public static function show(){
	}

	public static function match($request = 'any', $path = null ) {
		$params;
		switch( $request ) {
                        case 'get' :
                                $params = self::$gets;
                                break;
                        case 'post' :
                                $params = self::$posts;
                                break;
                        case 'patch' :
                                $params = self::$patchs;
                                break;
                        case 'delete' :
                                $params = self::$deletes;
                                break;
                        default :
				$params = self::$any;

		}

		//usort(self::$params, create_function('$b,$a','return mb_strlen($a->path, "UTF-8") - mb_strlen($b->path, "UTF-8");'));
		usort($params, create_function('$b,$a','return strlen($a->path) - strlen($b->path);'));
		if( !empty($path) ) {
			foreach($params as $route) {
				if( preg_match( '{^' .$route->path . '$}', $path, $matches) ) {
					array_shift( $matches );

					// スラッグ名ととマッチ結果で連想配列を作成
					$args = array_combine( $route->slugs, $matches );
					// リクエストパラメーターと結合
					$_REQUEST = array_merge($_REQUEST, $args);

					// bodyがある場合追加
					$body = file_get_contents('php://input');
					if(!empty($body)) {
						$_REQUEST = array_merge($_REQUEST, array('body' => $body));
					}
					switch( gettype( $route->callback ) ) {
						case 'object' :
							call_user_func_array($route->callback, $args);
							break;
						case 'string' :
							list($controller, $method) = explode('@', $route->callback);
							call_user_func( array( ClassFactory::newInstance($controller, null) , $method) , $_REQUEST);	
							break;
						default :
							break;
					}
					return true;
				}
			}
		}
		return false;
	
	}

	public static function where($slugs) {
		$last = end(self::$any);
		$routes = explode('/', $last->route);	
		$array_path = [];
		foreach($routes as $route) {
			if( $route == "")
				continue;

			$match = false;
			foreach($slugs as $slug => $slug_pattern) {
				if( '{' . $slug . '}' == $route) {
					$pattern = '/\{' . $slug . '\}/';
					$last->slugs[] = $slug;
					$array_path[] = preg_replace($pattern, '(' . $slug_pattern . ')', $route);
					$match = true;
					break;
				}
			}

			if(!$match) {
				$array_path[] = $route;
			}	
		}
		$last->path = str_replace('/', '\/', '/' . implode('/', $array_path));
	}
}

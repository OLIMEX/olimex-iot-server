<?php

class Template {
	
	const TEMPLATE = '/index.php';
	
	static protected $page  = array();
	static protected $title = array();
	
	protected $request        = NULL;
	protected $templateDir    = NULL;
	
	public function __construct($templateDir, $url = NULL) {
		$this->request = Request::parse($url);
		if (empty($this->request['path'])) {
			$this->request['path'] = '/';
		}
		$this->request['path'] = preg_replace('/\/+/', '/', $this->request['path']);
		
		$this->templateDir = $templateDir;
		
		if (!file_exists($this->templateDir.self::TEMPLATE)) {
			throw new Exception('Template not found!', 404);
		}
	}
	
	protected function local() {
		$local = str_replace('\\', '/', realpath(getcwd().rawurldecode($this->request['path'])));
		if (!file_exists($local)) {
			return NULL;
		}
		return $local;
	}
	
	protected function path() {
		return (
			'/'.join('/', preg_split('/\/+/', $this->request['path'], -1, PREG_SPLIT_NO_EMPTY))
		);
	}
	
	public static function page($path, $title = NULL) {
		if (!in_array($path, self::$page)) {
			self::$page[] = $path;
			if (!empty($title)) {
				self::$title[$path] = $title;
			}
		}
	}
	
	public function isPage($path = NULL) {
		if (empty($path)) {
			$path = $this->path();
		}
		return in_array($path, self::$page);
	}
	
	public function is404() {
		$method = NULL;
		try {
			$method = $this->getMethodTemplate($this->path());
		} catch (Exception $e) {
			$method = NULL;
		}
		
		return (
			!$this->isRoot() &&
			$this->local() == NULL &&
			$method == NULL
		);
	}
	
	protected function getMethodTemplate($method) {
		$method   = join('/', preg_split('/\/+/', $method, -1, PREG_SPLIT_NO_EMPTY));
		$template = $this->templateDir.'/'.$method.'.php';
		if (!file_exists($template)) {
			throw new Exception('Template ['.$method.'] not found!', 404);
		}
		return $template;
	}
	
	public function isRoot() {
		 return ($this->request['path'] == '/');
	}
	
	public function isSecure() {
		return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on');
	}
	
	public function src($url) {
		if (preg_match('/^https?:\/\/(.+)$/', $url, $matches)) {
			return 'http'.($this->isSecure() ? 's' : '').'://'.$matches[1];
		}
		return $url;
	}
	
	public function version($url) {
		if (preg_match('/^https?:\/\/(.+)$/', $url)) {
			// External URL no version
			return $url;
		}
		
		$filename = NULL;
		if (preg_match('/^\/(.+)$/', $url, $matches)) {
			// Site root relative URL
			$filename = realpath($matches[1]);
		} else {
			$filename = realpath($this->local().'/'.$url);
		}
		
		if (file_exists($filename)) {
			return $url.'?'.filemtime($filename);
		}
		
		return $url;
	}
	
	public function title($seo = TRUE) {
		if ($this->is404()) {
			return 'Page not found';
		}
		
		if ($this->isRoot()) {
			return NULL;
		}
		
		$title = !empty(self::$title[$this->path()]) ?
			self::$title[$this->path()]
			:
			preg_replace('/\/+/', ' ', $this->path())
		;
		
		return preg_match('/[A-Z]/', $title) ? $title : ucwords($title);
	}
	
	public function __call($method, $parameters = array()) {
		if (isset($this->_level_) && $this->_level_ > 0) {
			$m = $this->_method_;
			$p = $this->_parameters_;
		} else {
			$this->_level_ = 0;
		}
		
		$template = $this->getMethodTemplate($method);
		$this->_method_     = $method;
		$this->_parameters_ = $parameters;
		
		ob_start();
		
		$this->_level_++;
		include $template;
		$this->_level_--;
		
		if ($this->_level_ > 0) {
			$this->_method_     = $m;
			$this->_parameters_ = $p;
		}
		return ob_get_clean();
	}
	
	public function render() {
		ob_start();
		if ($this->isRoot() || $this->isPage() || $this->is404()) {
			include $this->templateDir.self::TEMPLATE;
		} else {
			echo $this->__call($this->path());
		}
		return ob_get_clean();
	}
}
<?php 

use Mail\Mime;

class Mail {
	/**
	 * @var Mail\Mime
	 */
	protected $mime    = NULL;
	
	protected $from    = NULL;
	
	protected $to      = array();
	protected $cc      = array();
	protected $bcc     = array();
	
	protected $subject = NULL;
	
	public function __construct() {
		$this->mime = new Mime(
			strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' ?
				"\r\n"
				:
				"\n"
		);
	}
	
	/**
	 * @return Mail
	 */
	public static function create() {
		return new self();
	}
	
	/**
	 * @return Mail
	 */
	public function addTo($email) {
		if (
			!empty($email) &&
			!in_array($email, $this->to)  &&
			!in_array($email, $this->cc)  &&
			!in_array($email, $this->bcc)
		) {
			$this->to[] = $email;
		}
		return $this;
	}
	
	/**
	 * @return Mail
	 */
	public function addCc($email) {
		if (
			!empty($email) &&
			!in_array($email, $this->to)  &&
			!in_array($email, $this->cc)  &&
			!in_array($email, $this->bcc)
		) {
			$this->cc[] = $email;
			$this->mime->addCc($email);
		}
		return $this;
	}
	
	/**
	 * @return Mail
	 */
	public function addBcc($email) {
		if (
			!empty($email) &&
			!in_array($email, $this->to)  &&
			!in_array($email, $this->cc)  &&
			!in_array($email, $this->bcc)
		) {
			$this->bcc[] = $email;
			$this->mime->addBcc($email);
		}
		return $this;
	}
	
	/**
	 * @return Mail
	 */
	public function setFrom($from) {
		$this->from = $from;
		$this->mime->setFrom($this->from);
		return $this;
	}
	
	/**
	 * @return Mail
	 */
	public function setSubject($subject) {
		$this->subject = $subject;
		return $this;
	}
	
	public function send() {
		return mail(
			$this->mime->encodeRecipients(join(', ', $this->to)), 
			$this->subject, 
			$this->mime->get(), 
			$this->mime->txtHeaders(),
			'-r'.$this->from
		);
	}
	
	public function __call($name, $arguments) {
		$function = array($this->mime, $name);
		if (is_callable($function)) {
			$result = call_user_func_array($function, $arguments);
			if (is_null($result) || is_bool($result)) {
				return $this;
			}
			return $result;
		}
		throw new \Exception('Unknown method '.get_called_class().'::'.$name.'()');
	}
}
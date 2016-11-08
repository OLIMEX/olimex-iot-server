<?php

namespace Mail;

use Mail\Part,
	Mail\Exception;

/**
 * The Mime class is used to create MIME E-mail messages
 *
 * The Mime class provides an OO interface to create MIME
 * enabled email messages. This way you can create emails that
 * contain plain-text bodies, HTML bodies, attachments, inline
 * images and specific headers.
 *
 * Compatible with PHP version 5
 *
 * LICENSE: This LICENSE is in the BSD license style.
 * Copyright (c) 2002-2003, Richard Heyes <richard@phpguru.org>
 * Copyright (c) 2003-2006, PEAR <pear-group@php.net>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or
 * without modification, are permitted provided that the following
 * conditions are met:
 *
 * - Redistributions of source code must retain the above copyright
 *   notice, this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright
 *   notice, this list of conditions and the following disclaimer in the
 *   documentation and/or other materials provided with the distribution.
 * - Neither the name of the authors, nor the names of its contributors 
 *   may be used to endorse or promote products derived from this 
 *   software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF
 * THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category  Mail
 * @package   Mime
 * @author	  Richard Heyes  <richard@phpguru.org>
 * @author	  Tomas V.V. Cox <cox@idecnet.com>
 * @author	  Cipriano Groenendal <cipri@php.net>
 * @author	  Sean Coates <sean@php.net>
 * @author	  Aleksander Machniak <alec@php.net>
 * @copyright 2003-2006 PEAR <pear-group@php.net>
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version   CVS: $Id$
 * @link	  http://pear.php.net/package/Mail_Mime2
 *
 *			This class is based on HTML Mime Mail class from
 *			Richard Heyes <richard@phpguru.org> which was based also
 *			in the mime_mail.class by Tobias Ratschiller <tobias@dnet.it>
 *			and Sascha Schumann <sascha@schumann.cx>
 */


/**
 * Part.php contains the code required to
 * create all the different parts a mail can
 * consist of.
 */
require_once 'Part.php';

/**
 * The Mime class provides an OO interface to create MIME
 * enabled email messages. This way you can create emails that
 * contain plain-text bodies, HTML bodies, attachments, inline
 * images and specific headers.
 *
 * @category  Mail
 * @package   Mime
 * @author	  Richard Heyes  <richard@phpguru.org>
 * @author	  Tomas V.V. Cox <cox@idecnet.com>
 * @author	  Cipriano Groenendal <cipri@php.net>
 * @author	  Sean Coates <sean@php.net>
 * @copyright 2003-2006 PEAR <pear-group@php.net>
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version   Release: @package_version@
 * @link	  http://pear.php.net/package/Mail_Mime2
 */
class Mime {
	/**
	 * Contains the plain text part of the email
	 * @var string
	 * @access private
	 */
	private $_txtbody;

	/**
	 * Contains the html part of the email
	 * @var string
	 * @access private
	 */
	private $_htmlbody;

	/**
	 * list of the attached images
	 * @var array
	 * @access private
	 */
	private $_html_images = array();

	/**
	 * list of the attachements
	 * @var array
	 * @access private
	 */
	private $_parts = array();

	/**
	 * Headers for the mail
	 * @var array
	 * @access private
	 */
	private $_headers = array();

	/**
	 * Build parameters
	 * @var array
	 * @access private
	 */
	private $_build_params = array(
		// What encoding to use for the headers
		// Options: quoted-printable or base64
		'head_encoding' => 'quoted-printable',
		// What encoding to use for plain text
		// Options: 7bit, 8bit, base64, or quoted-printable
		'text_encoding' => 'quoted-printable',
		// What encoding to use for html
		// Options: 7bit, 8bit, base64, or quoted-printable
		'html_encoding' => 'quoted-printable',
		// The character set to use for html
		'html_charset'  => 'UTF-8',
		// The character set to use for text
		'text_charset'  => 'UTF-8',
		// The character set to use for headers
		'head_charset'  => 'UTF-8',
		// End-of-line sequence
		'eol'		   => "\r\n",
		// Delay attachment files IO until building the message
		'delay_file_io' => FALSE
	);


	/**
	 * Constructor function
	 *
	 * @param mixed $params Build parameters that change the way the email
	 *              is built. Should be an associative array.
	 *              See $_build_params.
	 *
	 * @return void
	 * @access public
	 */
	public function __construct($params = array()) {
		// Backward-compatible EOL setting
		if (is_string($params)) {
			$this->_build_params['eol'] = $params;
		}

		// Update build parameters
		if (!empty($params) && is_array($params)) {
			foreach ($params as $key => $value) {
				$this->_build_params[$key] = $value;
			}
		}
	}

	/**
	 * Set build parameter value
	 *
	 * @param string $name  Parameter name
	 * @param string $value Parameter value
	 *
	 * @return void
	 * @access public
	 * @since 1.6.0
	 */
	public function setParam($name, $value) {
		$this->_build_params[$name] = $value;
	}

	/**
	 * Get build parameter value
	 *
	 * @param string $name Parameter name
	 *
	 * @return mixed Parameter value
	 * @access public
	 * @since 1.6.0
	 */
	public function getParam($name) {
		return (isset($this->_build_params[$name]) ? 
			$this->_build_params[$name]
			: 
			NULL
		);
	}

	/**
	 * Accessor function to set the body text. Body text is used if
	 * it's not an html mail being sent or else is used to fill the
	 * text/plain part that emails clients who don't support
	 * html should show.
	 *
	 * @param string $data   Either a string or
	 *                       the file name with the contents
	 * @param bool   $isFile If true the first param should be treated
	 *					     as a file name, else as a string (default)
	 * @param bool   $append If true the text or file is appended to
	 *                       the existing body, else the old body is
	 *                       overwritten
	 * @access public
	 */
	public function setTXTBody($data, $isFile = FALSE, $append = FALSE) {
		if (!$isFile) {
			if (!$append) {
				$this->_txtbody = $data;
			} else {
				$this->_txtbody .= $data;
			}
		} else {
			$cont = $this->_file2str($data);
			if (!$append) {
				$this->_txtbody = $cont;
			} else {
				$this->_txtbody .= $cont;
			}
		}
		return TRUE;
	}

	/**
	 * Get message text body
	 *
	 * @return string Text body
	 * @access public
	 * @since 1.6.0
	 */
	public function getTXTBody() {
		return $this->_txtbody;
	}

	/**
	 * Adds a html part to the mail.
	 *
	 * @param string $data   Either a string or the file name with the
	 *					   contents
	 * @param bool   $isfile A flag that determines whether $data is a
	 *					   filename, or a string(false, default)
	 *
	 * @return bool		  True on success
	 * @access public
	 */
	public function setHTMLBody($data, $isFile = FALSE) {
		$this->_htmlbody = $isFile ?
			$this->_file2str($data)
			:
			$data
		;
		
		return TRUE;
	}

	/**
	 * Get message HTML body
	 *
	 * @return string HTML body
	 * @access public
	 * @since 1.6.0
	 */
	public function getHTMLBody() {
		return $this->_htmlbody;
	}

	/**
	 * Adds an image to the list of embedded images.
	 *
	 * @param string $file	   The image file name OR image data itself
	 * @param string $c_type	 The content type
	 * @param string $name	   The filename of the image.
	 *						   Only used if $file is the image data.
	 * @param bool   $isFile	 Whether $file is a filename or not.
	 *						   Defaults to true
	 * @param string $content_id Desired Content-ID of MIME part
	 *						   Defaults to generated unique ID
	 *
	 * @return bool		  True on success
	 * @access public
	 */
	public function addHTMLImage(
		$file,
		$c_type     = 'application/octet-stream',
		$name       = '',
		$isFile     = TRUE,
		$content_id = NULL
	) {
		$bodyfile = NULL;

		if ($isFile) {
			// Don't load file into memory
			if ($this->_build_params['delay_file_io']) {
				$filedata = NULL;
				$bodyfile = $file;
			} else {
				$filedata = $this->_file2str($file);
			}
			$filename = ($name ? $name : $file);
		} else {
			$filedata = $file;
			$filename = $name;
		}

		if (!$content_id) {
			$content_id = md5(uniqid(time()));
		}

		$this->_html_images[] = array(
			'body'	    => $filedata,
			'body_file' => $bodyfile,
			'name'	    => $filename,
			'c_type'    => $c_type,
			'cid'       => $content_id
		);

		return TRUE;
	}

	/**
	 * Adds a file to the list of attachments.
	 *
	 * @param string $file		  The file name of the file to attach
	 *							  or the file contents itself
	 * @param string $c_type      The content type
	 * @param string $name		  The filename of the attachment
	 *							  Only use if $file is the contents
	 * @param bool   $isFile      Whether $file is a filename or not. Defaults to true
	 * @param string $encoding	  The type of encoding to use. Defaults to base64.
	 *							  Possible values: 7bit, 8bit, base64 or quoted-printable.
	 * @param string $disposition The content-disposition of this file
	 *							  Defaults to attachment.
	 *							  Possible values: attachment, inline.
	 * @param string $charset     The character set of attachment's content.
	 * @param string $language	  The language of the attachment
	 * @param string $location	  The RFC 2557.4 location of the attachment
	 * @param string $n_encoding  Encoding of the attachment's name in Content-Type
	 *							  By default filenames are encoded using RFC2231 method
	 *							  Here you can set RFC2047 encoding (quoted-printable
	 *							  or base64) instead
	 * @param string $f_encoding  Encoding of the attachment's filename
	 *							  in Content-Disposition header.
	 * @param string $description Content-Description header
	 * @param string $h_charset   The character set of the headers e.g. filename
	 *							  If not specified, $charset will be used
	 * @param array  $add_headers Additional part headers. Array keys can be in form
	 *							  of <header_name>:<parameter_name>
	 *
	 * @return mixed			  True on success or PEAR_Error object
	 * @access public
	 */
	public function addAttachment(
		$file,
		$c_type	     = 'application/octet-stream',
		$name        = '',
		$isFile	     = TRUE,
		$encoding	 = 'base64',
		$disposition = 'attachment',
		$charset	 = '',
		$language	 = '',
		$location	 = '',
		$n_encoding  = 'base64',
		$f_encoding  = NULL,
		$description = '',
		$h_charset   = 'UTF-8',
		$add_headers = array()
	) {
		$bodyfile = NULL;

		if ($isFile) {
			// Don't load file into memory
			if ($this->_build_params['delay_file_io']) {
				$filedata = NULL;
				$bodyfile = $file;
			} else {
				$filedata = $this->_file2str($file);
			}
			// Force the name the user supplied, otherwise use $file
			$filename = ($name ? $name : $file);
		} else {
			$filedata = $file;
			$filename = $name;
		}

		if (!strlen($filename)) {
			throw new Exception("The supplied filename for the attachment can't be empty");
		}
		$filename = self::_basename($filename);

		$this->_parts[] = array(
			'body'              => $filedata,
			'body_file'         => $bodyfile,
			'name'              => $filename,
			'c_type'            => $c_type,
			'charset'           => $charset,
			'encoding'	        => $encoding,
			'language'	        => $language,
			'location'	        => $location,
			'disposition'       => $disposition,
			'description'       => $description,
			'add_headers'       => $add_headers,
			'name_encoding'	    => $n_encoding,
			'filename_encoding' => $f_encoding,
			'headers_charset'   => $h_charset,
		);

		return TRUE;
	}

	/**
	 * Get the contents of the given file name as string
	 *
	 * @param string $file_name Path of file to process
	 *
	 * @return string		   Contents of $file_name
	 * @access private
	 */
	private function _file2str($file_name) {
		// Check state of file and raise an error properly
		if (!file_exists($file_name)) {
			throw new Exception('File not found: '.$file_name);
		}
		if (!is_file($file_name)) {
			throw new Exception('Not a regular file: '.$file_name);
		}
		if (!is_readable($file_name)) {
			throw new Exception('File is not readable: '.$file_name);
		}

		// Temporarily reset magic_quotes_runtime and read file contents
		$magic_quote_setting = get_magic_quotes_runtime();
		if ($magic_quote_setting) {
			@ini_set('magic_quotes_runtime', 0);
		}
		$cont = file_get_contents($file_name);
		if ($magic_quote_setting) {
			@ini_set('magic_quotes_runtime', $magic_quote_setting);
		}

		return $cont;
	}

	/**
	 * Adds a text subpart to the mimePart object and
	 * returns it during the build process.
	 *
	 * @param mixed  $obj The object to add the part to, or
	 *					 null if a new object is to be created.
	 * @param string $text The text to add.
	 *
	 * @return object	  The text mimePart object
	 * @access private
	 */
	private function _addTextPart($obj, $text) {
		$params['content_type'] = 'text/plain';
		$params['encoding']	 = $this->_build_params['text_encoding'];
		$params['charset']	  = $this->_build_params['text_charset'];
		$params['eol']		  = $this->_build_params['eol'];

		if (is_object($obj)) {
			$ret = $obj->addSubpart($text, $params);
			return $ret;
		} else {
			$ret = new Part($text, $params);
			return $ret;
		}
	}

	/**
	 * Adds a html subpart to the mimePart object and
	 * returns it during the build process.
	 *
	 * @param mixed $obj The object to add the part to, or
	 *					null if a new object is to be created.
	 *
	 * @return object	 The html mimePart object
	 * @access private
	 */
	private function _addHtmlPart($obj) {
		$params['content_type'] = 'text/html';
		$params['encoding']	 = $this->_build_params['html_encoding'];
		$params['charset']	  = $this->_build_params['html_charset'];
		$params['eol']		  = $this->_build_params['eol'];

		if (is_object($obj)) {
			$ret = $obj->addSubpart($this->_htmlbody, $params);
			return $ret;
		} else {
			$ret = new Part($this->_htmlbody, $params);
			return $ret;
		}
	}

	/**
	 * Creates a new Part object, using multipart/mixed as
	 * the initial content-type and returns it during the
	 * build process.
	 *
	 * @return object The multipart/mixed mimePart object
	 * @access private
	 */
	private function _addMixedPart() {
		$params				 = array();
		$params['content_type'] = 'multipart/mixed';
		$params['eol']		  = $this->_build_params['eol'];

		// Create empty multipart/mixed Mail\Part object to return
		$ret = new Part('', $params);
		return $ret;
	}

	/**
	 * Adds a multipart/alternative part to a mimePart
	 * object (or creates one), and returns it during
	 * the build process.
	 *
	 * @param mixed $obj The object to add the part to, or
	 *					null if a new object is to be created.
	 *
	 * @return object	 The multipart/mixed mimePart object
	 * @access private
	 */
	private function _addAlternativePart($obj) {
		$params['content_type'] = 'multipart/alternative';
		$params['eol']		  = $this->_build_params['eol'];

		if (is_object($obj)) {
			return $obj->addSubpart('', $params);
		} else {
			$ret = new Part('', $params);
			return $ret;
		}
	}

	/**
	 * Adds a multipart/related part to a mimePart
	 * object (or creates one), and returns it during
	 * the build process.
	 *
	 * @param mixed $obj The object to add the part to, or
	 *					null if a new object is to be created
	 *
	 * @return object	 The multipart/mixed mimePart object
	 * @access private
	 */
	private function _addRelatedPart($obj) {
		$params['content_type'] = 'multipart/related';
		$params['eol']		  = $this->_build_params['eol'];

		if (is_object($obj)) {
			return $obj->addSubpart('', $params);
		} else {
			$ret = new Part('', $params);
			return $ret;
		}
	}

	/**
	 * Adds an html image subpart to a mimePart object
	 * and returns it during the build process.
	 *
	 * @param object $obj  The mimePart to add the image to
	 * @param array  $value The image information
	 *
	 * @return object	   The image mimePart object
	 * @access private
	 */
	private function _addHtmlImagePart($obj, $value) {
		$params['content_type'] = $value['c_type'];
		$params['encoding']	 = 'base64';
		$params['disposition']  = 'inline';
		$params['filename']	 = $value['name'];
		$params['cid']		  = $value['cid'];
		$params['body_file']	= $value['body_file'];
		$params['eol']		  = $this->_build_params['eol'];

		if (!empty($value['name_encoding'])) {
			$params['name_encoding'] = $value['name_encoding'];
		}
		if (!empty($value['filename_encoding'])) {
			$params['filename_encoding'] = $value['filename_encoding'];
		}

		$ret = $obj->addSubpart($value['body'], $params);
		return $ret;
	}

	/**
	 * Adds an attachment subpart to a mimePart object
	 * and returns it during the build process.
	 *
	 * @param object $obj  The mimePart to add the image to
	 * @param array  $value The attachment information
	 *
	 * @return object	   The image mimePart object
	 * @access private
	 */
	private function _addAttachmentPart($obj, $value) {
		$params['eol']		  = $this->_build_params['eol'];
		$params['filename']	 = $value['name'];
		$params['encoding']	 = $value['encoding'];
		$params['content_type'] = $value['c_type'];
		$params['body_file']	= $value['body_file'];
		$params['disposition']  = isset($value['disposition']) ? 
								  $value['disposition'] : 'attachment';

		// content charset
		if (!empty($value['charset'])) {
			$params['charset'] = $value['charset'];
		}
		// headers charset (filename, description)
		if (!empty($value['headers_charset'])) {
			$params['headers_charset'] = $value['headers_charset'];
		}
		if (!empty($value['language'])) {
			$params['language'] = $value['language'];
		}
		if (!empty($value['location'])) {
			$params['location'] = $value['location'];
		}
		if (!empty($value['name_encoding'])) {
			$params['name_encoding'] = $value['name_encoding'];
		}
		if (!empty($value['filename_encoding'])) {
			$params['filename_encoding'] = $value['filename_encoding'];
		}
		if (!empty($value['description'])) {
			$params['description'] = $value['description'];
		}
		if (is_array($value['add_headers'])) {
			$params['headers'] = $value['add_headers'];
		}

		$ret = $obj->addSubpart($value['body'], $params);
		return $ret;
	}

	/**
	 * Returns the complete e-mail, ready to send using an alternative
	 * mail delivery method. Note that only the mailpart that is made
	 * with Mail_Mime2 is created. This means that,
	 * YOU WILL HAVE NO TO: HEADERS UNLESS YOU SET IT YOURSELF 
	 * using the $headers parameter!
	 * 
	 * @param string $separation The separation between these two parts.
	 * @param array  $params	 The Build parameters passed to the
	 *						   &get() function. See &get for more info.
	 * @param array  $headers	The extra headers that should be passed
	 *						   to the &headers() function.
	 *						   See that function for more info.
	 * @param bool   $overwrite  Overwrite the existing headers with new.
	 *
	 * @return mixed The complete e-mail or PEAR error object
	 * @access public
	 */
	public function getMessage(
		$separation = NULL, 
		$params     = NULL, 
		$headers    = NULL,
		$overwrite  = FALSE
	) {
		if ($separation === NULL) {
			$separation = $this->_build_params['eol'];
		}

		$body = $this->get($params);

		$head = $this->txtHeaders($headers, $overwrite);
		$mail = $head . $separation . $body;
		return $mail;
	}

	/**
	 * Returns the complete e-mail body, ready to send using an alternative
	 * mail delivery method.
	 * 
	 * @param array $params The Build parameters passed to the
	 *                      &get() function. See &get for more info.
	 *
	 * @return mixed The e-mail body or PEAR error object
	 * @access public
	 * @since 1.6.0
	 */
	public function getMessageBody($params = NULL) {
		return $this->get($params, NULL, TRUE);
	}

	/**
	 * Writes (appends) the complete e-mail into file.
	 * 
	 * @param string $filename  Output file location
	 * @param array  $params	The Build parameters passed to the
	 *                          &get() function. See &get for more info.
	 * @param array  $headers   The extra headers that should be passed
	 *                          to the &headers() function.
	 *                          See that function for more info.
	 * @param bool   $overwrite Overwrite the existing headers with new.
	 *
	 * @return mixed True or PEAR error object
	 * @access public
	 * @since 1.6.0
	 */
	public function saveMessage($filename, $params = NULL, $headers = NULL, $overwrite = FALSE) {
		// Check state of file and raise an error properly
		if (file_exists($filename) && !is_writable($filename)) {
			throw new Exception('File is not writable: '.$filename);
		}

		// Temporarily reset magic_quotes_runtime and read file contents
		$magic_quote_setting = get_magic_quotes_runtime();
		if ($magic_quote_setting) {
			@ini_set('magic_quotes_runtime', 0);
		}

		if (!($fh = fopen($filename, 'ab'))) {
			throw new Exception('Unable to open file: '.$filename);
		}

		// Write message headers into file (skipping Content-* headers)
		$head = $this->txtHeaders($headers, $overwrite, TRUE);
		if (fwrite($fh, $head) === FALSE) {
			throw new Exception('Error writing to file: '.$filename);
		}

		fclose($fh);

		if ($magic_quote_setting) {
			@ini_set('magic_quotes_runtime', $magic_quote_setting);
		}

		// Write the rest of the message into file
		$res = $this->get($params, $filename);

		return $res ? $res : TRUE;
	}

	/**
	 * Writes (appends) the complete e-mail body into file.
	 * 
	 * @param string $filename Output file location
	 * @param array  $params   The Build parameters passed to the
	 *                         &get() function. See &get for more info.
	 *
	 * @return mixed True or PEAR error object
	 * @access public
	 * @since 1.6.0
	 */
	public function saveMessageBody($filename, $params = NULL) {
		// Check state of file and raise an error properly
		if (file_exists($filename) && !is_writable($filename)) {
			throw new Exception('File is not writable: '.$filename);
		}

		// Temporarily reset magic_quotes_runtime and read file contents
		$magic_quote_setting = get_magic_quotes_runtime();
		if ($magic_quote_setting) {
			@ini_set('magic_quotes_runtime', 0);
		}

		if (!($fh = fopen($filename, 'ab'))) {
			throw new Exception('Unable to open file: '.$filename);
		}

		// Write the rest of the message into file
		$res = $this->get($params, $filename, TRUE);

		return $res ? $res : TRUE;
	}

	/**
	 * Builds the multipart message from the list ($this->_parts) and
	 * returns the mime content.
	 *
	 * @param array	$params	      Build parameters that change the way the email
	 *                            is built. Should be associative. See $_build_params.
	 * @param resource $filename  Output file where to save the message instead of
	 *                            returning it
	 * @param boolean  $skip_head True if you want to return/save only the message
	 *                            without headers
	 *
	 * @return mixed The MIME message content string, null or PEAR error object
	 * @access public
	 */
	public function get($params = NULL, $filename = NULL, $skip_head = FALSE) {
		if (isset($params)) {
			foreach ($params as $key => $value) {
				$this->_build_params[$key] = $value;
			}
		}

		if (isset($this->_headers['From'])) {
			// Bug #11381: Illegal characters in domain ID
			if (preg_match('#(@[0-9a-zA-Z\-\.]+)#', $this->_headers['From'], $matches)) {
				$domainID = $matches[1];
			} else {
				$domainID = '@localhost';
			}
			foreach ($this->_html_images as $i => $img) {
				$cid = $this->_html_images[$i]['cid']; 
				if (!preg_match('#'.preg_quote($domainID).'$#', $cid)) {
					$this->_html_images[$i]['cid'] = $cid . $domainID;
				}
			}
		}

		if (count($this->_html_images) && isset($this->_htmlbody)) {
			foreach ($this->_html_images as $key => $value) {
				$regex   = array();
				$regex[] = '#(\s)((?i)src|background|href(?-i))\s*=\s*(["\']?)' .
							preg_quote($value['name'], '#') . '\3#';
				$regex[] = '#(?i)url(?-i)\(\s*(["\']?)' .
							preg_quote($value['name'], '#') . '\1\s*\)#';

				$rep   = array();
				$rep[] = '\1\2=\3cid:' . $value['cid'] .'\3';
				$rep[] = 'url(\1cid:' . $value['cid'] . '\1)';

				$this->_htmlbody = preg_replace($regex, $rep, $this->_htmlbody);
				$this->_html_images[$key]['name']
					= self::_basename($this->_html_images[$key]['name']);
			}
		}

		$this->_checkParams();

		$null        = NULL;
		$attachments = count($this->_parts)                ? TRUE : FALSE;
		$html_images = count($this->_html_images)          ? TRUE : FALSE;
		$html        = strlen($this->_htmlbody)            ? TRUE : FALSE;
		$text        = (!$html && strlen($this->_txtbody)) ? TRUE : FALSE;

		switch (TRUE) {
		case $text && !$attachments:
			$message = $this->_addTextPart($null, $this->_txtbody);
			break;

		case !$text && !$html && $attachments:
			$message = $this->_addMixedPart();
			for ($i = 0; $i < count($this->_parts); $i++) {
				$this->_addAttachmentPart($message, $this->_parts[$i]);
			}
			break;

		case $text && $attachments:
			$message = $this->_addMixedPart();
			$this->_addTextPart($message, $this->_txtbody);
			for ($i = 0; $i < count($this->_parts); $i++) {
				$this->_addAttachmentPart($message, $this->_parts[$i]);
			}
			break;

		case $html && !$attachments && !$html_images:
			if (isset($this->_txtbody)) {
				$message = $this->_addAlternativePart($null);
				$this->_addTextPart($message, $this->_txtbody);
				$this->_addHtmlPart($message);
			} else {
				$message = $this->_addHtmlPart($null);
			}
			break;

		case $html && !$attachments && $html_images:
			// * Content-Type: multipart/alternative;
			//	* text
			//	* Content-Type: multipart/related;
			//	   * html
			//	   * image...
			if (isset($this->_txtbody)) {
				$message = $this->_addAlternativePart($null);
				$this->_addTextPart($message, $this->_txtbody);

				$ht = $this->_addRelatedPart($message);
				$this->_addHtmlPart($ht);
				for ($i = 0; $i < count($this->_html_images); $i++) {
					$this->_addHtmlImagePart($ht, $this->_html_images[$i]);
				}
			} else {
				// * Content-Type: multipart/related;
				//	* html
				//	* image...
				$message = $this->_addRelatedPart($null);
				$this->_addHtmlPart($message);
				for ($i = 0; $i < count($this->_html_images); $i++) {
					$this->_addHtmlImagePart($message, $this->_html_images[$i]);
				}
			}
			/*
			// #13444, #9725: the code below was a non-RFC compliant hack
			// * Content-Type: multipart/related;
			//	* Content-Type: multipart/alternative;
			//		* text
			//		* html
			//	* image...
			$message = $this->_addRelatedPart($null);
			if (isset($this->_txtbody)) {
				$alt = $this->_addAlternativePart($message);
				$this->_addTextPart($alt, $this->_txtbody);
				$this->_addHtmlPart($alt);
			} else {
				$this->_addHtmlPart($message);
			}
			for ($i = 0; $i < count($this->_html_images); $i++) {
				$this->_addHtmlImagePart($message, $this->_html_images[$i]);
			}
			*/
			break;

		case $html && $attachments && !$html_images:
			$message = $this->_addMixedPart();
			if (isset($this->_txtbody)) {
				$alt = $this->_addAlternativePart($message);
				$this->_addTextPart($alt, $this->_txtbody);
				$this->_addHtmlPart($alt);
			} else {
				$this->_addHtmlPart($message);
			}
			for ($i = 0; $i < count($this->_parts); $i++) {
				$this->_addAttachmentPart($message, $this->_parts[$i]);
			}
			break;

		case $html && $attachments && $html_images:
			$message = $this->_addMixedPart();
			if (isset($this->_txtbody)) {
				$alt = $this->_addAlternativePart($message);
				$this->_addTextPart($alt, $this->_txtbody);
				$rel = $this->_addRelatedPart($alt);
			} else {
				$rel = $this->_addRelatedPart($message);
			}
			$this->_addHtmlPart($rel);
			for ($i = 0; $i < count($this->_html_images); $i++) {
				$this->_addHtmlImagePart($rel, $this->_html_images[$i]);
			}
			for ($i = 0; $i < count($this->_parts); $i++) {
				$this->_addAttachmentPart($message, $this->_parts[$i]);
			}
			break;

		}

		if (!isset($message)) {
			return NULL;
		}

		// Use saved boundary
		if (!empty($this->_build_params['boundary'])) {
			$boundary = $this->_build_params['boundary'];
		} else {
			$boundary = NULL;
		}

		// Write output to file
		if ($filename) {
			// Append mimePart message headers and body into file
			$headers = $message->encodeToFile($filename, $boundary, $skip_head);
			$this->_headers = array_merge($this->_headers, $headers);
			return NULL;
		} else {
			$output = $message->encode($boundary, $skip_head);
			$this->_headers = array_merge($this->_headers, $output['headers']);
			$body = $output['body'];
			return $body;
		}
	}

	/**
	 * Returns an array with the headers needed to prepend to the email
	 * (MIME-Version and Content-Type). Format of argument is:
	 * $array['header-name'] = 'header-value';
	 *
	 * @param array $xtra_headers Assoc array with any extra headers (optional)
	 *							(Don't set Content-Type for multipart messages here!)
	 * @param bool  $overwrite	Overwrite already existing headers.
	 * @param bool  $skip_content Don't return content headers: Content-Type,
	 *							Content-Disposition and Content-Transfer-Encoding
	 * 
	 * @return array			  Assoc array with the mime headers
	 * @access public
	 */
	public function headers($xtra_headers = NULL, $overwrite = FALSE, $skip_content = FALSE) {
		// Add mime version header
		$headers['MIME-Version'] = '1.0';

		// Content-Type and Content-Transfer-Encoding headers should already
		// be present if get() was called, but we'll re-set them to make sure
		// we got them when called before get() or something in the message
		// has been changed after get() [#14780]
		if (!$skip_content) {
			$headers += $this->_contentHeaders();
		}

		if (!empty($xtra_headers)) {
			$headers = array_merge($headers, $xtra_headers);
		}

		if ($overwrite) {
			$this->_headers = array_merge($this->_headers, $headers);
		} else {
			$this->_headers = array_merge($headers, $this->_headers);
		}

		$headers = $this->_headers;

		if ($skip_content) {
			unset($headers['Content-Type']);
			unset($headers['Content-Transfer-Encoding']);
			unset($headers['Content-Disposition']);
		} else if (!empty($this->_build_params['ctype'])) {
			$headers['Content-Type'] = $this->_build_params['ctype'];
		}

		$encodedHeaders = $this->_encodeHeaders($headers);
		return $encodedHeaders;
	}

	/**
	 * Get the text version of the headers
	 * (usefull if you want to use the PHP mail() function)
	 *
	 * @param array $xtra_headers Assoc array with any extra headers (optional)
	 *							(Don't set Content-Type for multipart messages here!)
	 * @param bool  $overwrite	Overwrite the existing headers with new.
	 * @param bool  $skip_content Don't return content headers: Content-Type,
	 *							Content-Disposition and Content-Transfer-Encoding
	 *
	 * @return string			 Plain text headers
	 * @access public
	 */
	public function txtHeaders($xtra_headers = NULL, $overwrite = FALSE, $skip_content = FALSE) {
		$headers = $this->headers($xtra_headers, $overwrite, $skip_content);

		// Place Received: headers at the beginning of the message
		// Spam detectors often flag messages with it after the Subject: as spam
		if (isset($headers['Received'])) {
			$received = $headers['Received'];
			unset($headers['Received']);
			$headers = array('Received' => $received) + $headers;
		}

		$ret = '';
		$eol = $this->_build_params['eol'];

		foreach ($headers as $key => $val) {
			if (is_array($val)) {
				foreach ($val as $value) {
					$ret .= "$key: $value" . $eol;
				}
			} else {
				$ret .= "$key: $val" . $eol;
			}
		}

		return $ret;
	}

	/**
	 * Sets message Content-Type header.
	 * Use it to build messages with various content-types e.g. miltipart/raport
	 * not supported by _contentHeaders() function.
	 *
	 * @param string $type   Type name
	 * @param array  $params Hash array of header parameters
	 *
	 * @return void
	 * @access public
	 * @since 1.7.0
	 */
	public function setContentType($type, $params = array()) {
		$header = $type;

		$eol = !empty($this->_build_params['eol'])
			? $this->_build_params['eol'] : "\r\n";

		// add parameters
		$token_regexp = '#([^\x21\x23-\x27\x2A\x2B\x2D'
			. '\x2E\x30-\x39\x41-\x5A\x5E-\x7E])#';
		if (is_array($params)) {
			foreach ($params as $name => $value) {
				if ($name == 'boundary') {
					$this->_build_params['boundary'] = $value;
				}
				if (!preg_match($token_regexp, $value)) {
					$header .= ";$eol $name=$value";
				} else {
					$value = addcslashes($value, '\\"');
					$header .= ";$eol $name=\"$value\"";
				}
			}
		}

		// add required boundary parameter if not defined
		if (preg_match('/^multipart\//i', $type)) {
			if (empty($this->_build_params['boundary'])) {
				$this->_build_params['boundary'] = '=_' . md5(rand() . microtime());
			}

			$header .= ";$eol boundary=\"".$this->_build_params['boundary']."\"";
		}

		$this->_build_params['ctype'] = $header;
	}

	/**
	 * Sets the Subject header
	 *
	 * @param string $subject String to set the subject to.
	 *
	 * @return void
	 * @access public
	 */
	public function setSubject($subject) {
		$this->_headers['Subject'] = $subject;
	}

	/**
	 * Set an email to the From (the sender) header
	 *
	 * @param string $email The email address to use
	 *
	 * @return void
	 * @access public
	 */
	public function setFrom($email) {
		$this->_headers['From'] = $email;
	}

	/**
	 * Add an email to the To header
	 * (multiple calls to this method are allowed)
	 *
	 * @param string $email The email direction to add
	 *
	 * @return void
	 * @access public
	 */
	public function addTo($email) {
		if (isset($this->_headers['To'])) {
			$this->_headers['To'] .= ", $email";
		} else {
			$this->_headers['To'] = $email;
		}
	}

	/**
	 * Add an email to the Cc (carbon copy) header
	 * (multiple calls to this method are allowed)
	 *
	 * @param string $email The email direction to add
	 *
	 * @return void
	 * @access public
	 */
	public function addCc($email) {
		if (isset($this->_headers['Cc'])) {
			$this->_headers['Cc'] .= ", $email";
		} else {
			$this->_headers['Cc'] = $email;
		}
	}

	/**
	 * Add an email to the Bcc (blank carbon copy) header
	 * (multiple calls to this method are allowed)
	 *
	 * @param string $email The email direction to add
	 *
	 * @return void
	 * @access public
	 */
	public function addBcc($email) {
		if (isset($this->_headers['Bcc'])) {
			$this->_headers['Bcc'] .= ", $email";
		} else {
			$this->_headers['Bcc'] = $email;
		}
	}

	/**
	 * Since the PHP send function requires you to specify
	 * recipients (To: header) separately from the other
	 * headers, the To: header is not properly encoded.
	 * To fix this, you can use this public method to 
	 * encode your recipients before sending to the send
	 * function
	 *
	 * @param string $recipients A comma-delimited list of recipients
	 *
	 * @return string			Encoded data
	 * @access public
	 */
	public function encodeRecipients($recipients) {
		$input = array("To" => $recipients);
		$retval = $this->_encodeHeaders($input);
		return $retval["To"] ;
	}

	/**
	 * Encodes headers as per RFC2047
	 *
	 * @param array $input  The header data to encode
	 * @param array $params Extra build parameters
	 *
	 * @return array		Encoded data
	 * @access private
	 */
	private function _encodeHeaders($input, $params = array()) {
		$build_params = $this->_build_params;
		foreach ($params as $key => $value) {
			$build_params[$key] = $value;
		}

		foreach ($input as $hdr_name => $hdr_value) {
			if (is_array($hdr_value)) {
				foreach ($hdr_value as $idx => $value) {
					$input[$hdr_name][$idx] = Part::encodeHeader(
						$hdr_name, $value,
						$build_params['head_charset'], $build_params['head_encoding'],
						$this->_build_params['eol']
					);
				}
			} else {
				$input[$hdr_name] = Part::encodeHeader(
					$hdr_name, $hdr_value,
					$build_params['head_charset'], $build_params['head_encoding'],
					$this->_build_params['eol']
				);
			}
		}

		return $input;
	}

	/**
	 * Encodes a header as per RFC2047
	 *
	 * @param string $name	 The header name
	 * @param string $value	The header data to encode
	 * @param string $charset  Character set name
	 * @param string $encoding Encoding name (base64 or quoted-printable)
	 *
	 * @return string		  Encoded header data (without a name)
	 * @access public
	 * @since 1.5.3
	 * @deprecated Just use Mail\Part::encodeHeader() directly with the right EOL param.
	 */
	public function encodeHeader($name, $value, $charset, $encoding) {
		return Part::encodeHeader(
			$name, $value, $charset, $encoding, $this->_build_params['eol']
		);
	}

	/**
	 * Get file's basename (locale independent) 
	 *
	 * @param string $filename Filename
	 *
	 * @return string		  Basename
	 * @access private
	 */
	private static function _basename($filename) {
		// basename() is not unicode safe and locale dependent
		if (stristr(PHP_OS, 'win') || stristr(PHP_OS, 'netware')) {
			return preg_replace('/^.*[\\\\\\/]/', '', $filename);
		} else {
			return preg_replace('/^.*[\/]/', '', $filename);
		}
	}

	/**
	 * Get Content-Type and Content-Transfer-Encoding headers of the message
	 *
	 * @return array Headers array
	 * @access private
	 */
	private function _contentHeaders() {
		$attachments = count($this->_parts)               ? TRUE : FALSE;
		$html_images = count($this->_html_images)         ? TRUE : FALSE;
		$html		= strlen($this->_htmlbody)            ? TRUE : FALSE;
		$text		= (!$html && strlen($this->_txtbody)) ? TRUE : FALSE;
		$headers	 = array();

		// See get()
		switch (TRUE) {
		case $text && !$attachments:
			$headers['Content-Type'] = 'text/plain';
			break;

		case !$text && !$html && $attachments:
		case $text && $attachments:
		case $html && $attachments && !$html_images:
		case $html && $attachments && $html_images:
			$headers['Content-Type'] = 'multipart/mixed';
			break;

		case $html && !$attachments && !$html_images && isset($this->_txtbody):
		case $html && !$attachments && $html_images && isset($this->_txtbody):
			$headers['Content-Type'] = 'multipart/alternative';
			break;

		case $html && !$attachments && !$html_images && !isset($this->_txtbody):
			$headers['Content-Type'] = 'text/html';
			break;

		case $html && !$attachments && $html_images && !isset($this->_txtbody):
			$headers['Content-Type'] = 'multipart/related';
			break;

		default:
			return $headers;
		}

		$this->_checkParams();

		$eol = !empty($this->_build_params['eol'])
			? $this->_build_params['eol'] : "\r\n";

		if ($headers['Content-Type'] == 'text/plain') {
			// single-part message: add charset and encoding
			$charset = 'charset=' . $this->_build_params['text_charset'];
			// place charset parameter in the same line, if possible
			// 26 = strlen("Content-Type: text/plain; ")
			$headers['Content-Type']
				.= (strlen($charset) + 26 <= 76) ? "; $charset" : ";$eol $charset";
			$headers['Content-Transfer-Encoding']
				= $this->_build_params['text_encoding'];
		} else if ($headers['Content-Type'] == 'text/html') {
			// single-part message: add charset and encoding
			$charset = 'charset=' . $this->_build_params['html_charset'];
			// place charset parameter in the same line, if possible
			$headers['Content-Type']
				.= (strlen($charset) + 25 <= 76) ? "; $charset" : ";$eol $charset";
			$headers['Content-Transfer-Encoding']
				= $this->_build_params['html_encoding'];
		} else {
			// multipart message: and boundary
			if (!empty($this->_build_params['boundary'])) {
				$boundary = $this->_build_params['boundary'];
			} else if (!empty($this->_headers['Content-Type'])
				&& preg_match('/boundary="([^"]+)"/', $this->_headers['Content-Type'], $m)
			) {
				$boundary = $m[1];
			} else {
				$boundary = '=_' . md5(rand() . microtime());
			}

			$this->_build_params['boundary'] = $boundary;
			$headers['Content-Type'] .= ";$eol boundary=\"$boundary\"";
		}

		return $headers;
	}

	/**
	 * Validate and set build parameters
	 *
	 * @return void
	 * @access private
	 */
	private function _checkParams() {
		$encodings = array('7bit', '8bit', 'base64', 'quoted-printable');

		$this->_build_params['text_encoding']
			= strtolower($this->_build_params['text_encoding']);
		$this->_build_params['html_encoding']
			= strtolower($this->_build_params['html_encoding']);

		if (!in_array($this->_build_params['text_encoding'], $encodings)) {
			$this->_build_params['text_encoding'] = '7bit';
		}
		if (!in_array($this->_build_params['html_encoding'], $encodings)) {
			$this->_build_params['html_encoding'] = '7bit';
		}

		// text body
		if ($this->_build_params['text_encoding'] == '7bit'
			&& !preg_match('/ascii/i', $this->_build_params['text_charset'])
			&& preg_match('/[^\x00-\x7F]/', $this->_txtbody)
		) {
			$this->_build_params['text_encoding'] = 'quoted-printable';
		}
		// html body
		if ($this->_build_params['html_encoding'] == '7bit'
			&& !preg_match('/ascii/i', $this->_build_params['html_charset'])
			&& preg_match('/[^\x00-\x7F]/', $this->_htmlbody)
		) {
			$this->_build_params['html_encoding'] = 'quoted-printable';
		}
	}

} // End of class

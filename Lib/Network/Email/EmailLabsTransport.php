<?php
App::uses('AbstractTransport', 'Network/Email');
App::uses('HttpSocket', 'Network/Http');

class EmailLabsTransport extends AbstractTransport {

/**
 * CakeEmail
 *
 * @var CakeEmail
 */
	protected $_cakeEmail;

/**
 * Variable that holds EmailLabs connection
 *
 * @var HttpSocket
 */
	private $__connection;

/**
 * CakeEmail headers
 *
 * @var array
 */
	protected $_headers;

/**
 * Configuration to transport
 *
 * @var mixed
 */
	protected $_config = array();

/**
 * Sends out email via Emaillabs
 *
 * @return array
 */
	public function send(CakeEmail $email) {
		// CakeEmail
		$this->_cakeEmail = $email;

		$this->_config = $this->_cakeEmail->config();
		$this->_headers = $this->_cakeEmail->getHeaders(array('from', 'to', 'cc', 'bcc', 'replyTo', 'subject'));

		// Setup connection
		$this->__connection = & new HttpSocket();

		$this->__connection->configAuth('Basic', $this->_config['auth_key'], $this->_config['auth_secret']);

		// Build message
		$message = $this->__buildMessage();
		// Send message
		$return = $this->__connection->post($this->_config['uri'], $message);

		// Return data
		$result = json_decode($return, true);
		$headers = $this->_headersToString($this->_headers);
		$to = $message['to'][0];
		if ($this->_cakeEmail->emailFormat() === 'html') {
			$message = $message['html'];
		} else {
			$message = $message['text'];
		}

		$adapterResult = [
			"ErrorCode" => $result['code'] == 200 ? 0 : $result['code'],
			"Message" => $result['message'],
			"MessageID" => !empty($result['data']) ? $result['data'][0][$to] : null
		];

		return array_merge(array('EmailLabs' => $adapterResult), array('headers' => $headers, 'message' => $message));
	}

/**
 * Build message
 *
 * @return array
 */
	private function __buildMessage() {
		// Message
		$message = array();

		// From
		$message['smtp_account'] = $this->_config['smtp_account'];

		// To
		$message['to'] = [ $this->_headers['To'] ];

		// Cc
		$message['cc'] = $this->_headers['Cc'];

		// Bcc
		$message['bcc'] = $this->_headers['Bcc'];

		// ReplyTo
		if ($this->_headers['Reply-To'] !== false) {
			$message['reply_to'] = $this->_headers['Reply-To'];
		}

		// From
		$from = $this->_cakeEmail->from();
		reset($from); $from_key = key($from);
		$message['from'] = $from_key;
		$message['from_name'] = $from[$from_key];

		// Subject
		$message['subject'] = mb_decode_mimeheader($this->_headers['Subject']);

		// Tag
		if (isset($this->_headers['Tag'])) {
			$message['tags'] = [ $this->_headers['Tag'] ];
		}

		// HtmlBody
		if ($this->_cakeEmail->emailFormat() === 'html' || $this->_cakeEmail->emailFormat() === 'both') {
			$message['html'] = $this->_cakeEmail->message('html');
		}

		// TextBody
		if ($this->_cakeEmail->emailFormat() === 'text' || $this->_cakeEmail->emailFormat() === 'both') {
			$message['text'] = $this->_cakeEmail->message('text');
		}

		// Attachments
		$message['files'] = $this->__buildAttachments();

		return $message;
	}

/**
 * Build attachments
 *
 * @return array
 */
	private function __buildAttachments() {
		// Attachments
		$attachments = array();

		$i = 0;
		foreach ($this->_cakeEmail->attachments() as $fileName => $fileInfo) {
			if (isset($fileInfo['file'])) {
				$handle = fopen($fileInfo['file'], 'rb');
				$data = fread($handle, filesize($fileInfo['file']));
				$data = chunk_split(base64_encode($data)) ;
				fclose($handle);
				$attachments[$i]['content'] = $data;
			} elseif (isset($fileInfo['data'])) {
				$attachments[$i]['content'] = $fileInfo['data'];
			}
			$attachments[$i]['name'] = $fileName;
			$attachments[$i]['mime'] = $fileInfo['mimetype'];
			$i++;
		}

		return $attachments;
	}

}

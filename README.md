<h2>Installation</h2>
<h4>Composer</h4>
Add the following package to composer.json:
<pre>
"kiczek/EmailLabs-CakePHP-Plugin": "*"
</pre>
<h4>Basic</h4>
Copy all files to <i>Plugin/<b>EmailLabs</b></i> directory in your <b>app</b> path
<h2>Setup</h2>
Remember to load plugin in your <i>bootstrap.php</i> file: <pre>CakePlugin::load('EmailLabs');</pre>
<h4>Usage</h4>
In your Config/email.php add new config:
<pre>
public $emaillabs = array(
	'transport' => 'EmailLabs.EmailLabs',
	'uri' => 'https://api.emaillabs.net.pl/api/sendmail',
	'auth_key' => 'your_auth_key',
	'auth_secret' => 'your_auth_secret',
	'smtp_account' => 'you smtp account name'
);
</pre>
<h4>Example</h4>
<pre>
  App::uses('CakeEmail', 'Network/Email');
  $email = new CakeEmail();
  $email->config('emaillabs');
  $email->from(array('your@address.com' => 'Your Name'));
  $email->to($to);
  $email->subject($subject);
  $email->emailFormat('text');
  return $email->send($msg);
</pre>

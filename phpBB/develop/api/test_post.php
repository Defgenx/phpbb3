<?php

$data = array(
	'auth_key' => '78814e1245370095',
	'serial' => 18,

	'username' => '',
	'topic_type' => 0,

	'forum_id' => 2,
	'topic_id' => 0,
	'icon_id' => 0,

	'enable_bbcode' => true,
	'enable_smilies' =>false,
	'enable_urls' => true,
	'enable_sig' => true,

	'message' => 'tesasdasdt 元気ですか？',

	'topic_title' => 'tww22st',

	'notify' => false,
);

$imploded_request = implode('/', $data);

$hash = hash_hmac('sha256', $imploded_request, '0f6e699a5965969b');

$request['hash'] = $hash;
$request['data'] = json_encode($data);

$options = array(
	'http' => array(
		'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
		'method'  => 'POST',
		'content' => http_build_query($request),
		'ignore_errors' => true
	),
);
$context  = stream_context_create($options);
$result = file_get_contents('http://localhost/phpBB/phpBB/app.php/api/post', false, $context);

echo $result;

<?php

require('../config.php');
use theodorejb\ResponsiveCaptcha;

$qa = ResponsiveCaptcha\randomQuestion();

$answer = $qa->getAnswer();

$response = [];

$response['captcha'] = $qa->getQuestion();




$iv_size = openssl_cipher_iv_length(CYPHER);
$iv = openssl_random_pseudo_bytes($iv_size);

$encryptedMessage = openssl_encrypt($answer, CYPHER, getenv('ENCRYPTION_KEY'), 0, $iv);

$response['s'] = base64_encode($iv.$encryptedMessage);

echo json_encode($response);
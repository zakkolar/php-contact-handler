<?php
require('../config.php');
use theodorejb\ResponsiveCaptcha;

$errors=[];

if(empty($_POST['email'])){
	$errors[]="provide your email address";
}
elseif(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
	$errors[]="provide an email in the correct format";
}
else{
	$email=$_POST['email'];
}

if(empty($_POST['name'])){
	$errors[]="provide your name";
}
else{
	$name = $_POST['name'];
}

if(empty($_POST['message'])){
	$errors[]="provide a message";
}
else{
	$message=$_POST['message'];
}

if(getenv("CAPTCHA_MODE") === 'RECAPTCHA'){

	$secret = getenv("RECAPTCHA_SECRET");
	 
	$response = null;

	$reCaptcha = new \ReCaptcha\ReCaptcha($secret);

	if(empty($_POST['g-recaptcha-response'])){
		$errors[]="fill out the human confirmation";
	}
	elseif(!$reCaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR'])->isSuccess()){
		$errors[]="try the human confirmation again";
	}
}

if(getenv('CAPTCHA_MODE') === "RESPONSIVE"){

    $iv_size = openssl_cipher_iv_length(CYPHER);

    $data = base64_decode($_POST['captcha_s']);

    $iv = substr($data, 0, $iv_size);
    $answer = openssl_decrypt(substr($data, $iv_size), CYPHER, getenv('ENCRYPTION_KEY'), 0, $iv);

    if(is_numeric($answer)){
        $answer = intval($answer);
    }



    if(empty($_POST['captcha_response']) || empty($_POST['captcha_s'])){
        $errors[]="fill out the human confirmation";
    }
    elseif(!ResponsiveCaptcha\checkAnswer($_POST['captcha_response'], $answer)){
        $errors[]="try the human confirmation again";
    }

}



if(count($errors)==0){

    $emailDomain = explode("@",$email)[1];

    $blockedDomains = explode(",",getenv('BLOCKED_EMAIL_DOMAINS'));

    foreach($blockedDomains as $key => $value){
        $blockedDomains[$key] = trim($value);
    }
    if(in_array($emailDomain, $blockedDomains)){
        die();
    }



	if(!$mailgun->sendMessage(getenv("MAILGUN_DOMAIN"), array('from'=> "$name <$email>", 
                                'to'      => getenv("CONTACT_RECIPIENT"), 
                                'subject' => getenv("CONTACT_SUBJECT"), 
                                'text'    => $message))){
		header("HTTP/1.1 500 Server Error");

	}
	
}
else{
	header("HTTP/1.1 400 Bad Request");
	echo json_encode($errors);
}


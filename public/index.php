<?php
require('../config.php');
if(!empty(getenv('FORM_DOMAIN'))){
	$origins = explode(",",getenv('FORM_DOMAIN'));
	foreach($origins as $key => $value){
		$origins[$key] = trim($value);
	}
	if(in_array($_SERVER['HTTP_ORIGIN'], $origins)){
		$origin = $_SERVER['HTTP_ORIGIN'];
		header("Access-Control-Allow-Origin: $origin");
	}
	else{
		http_response_code(403);
		die("This domain is not authorized");
	}
	
}

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

if(!empty(getenv("RECAPTCHA_SECRET"))){

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


?>
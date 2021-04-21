<?php
session_start();

require 'vendor/autoload.php';


use Mailgun\Mailgun;
use ReCaptcha\ReCaptcha;

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$dotenv->required(['MAILGUN_API_KEY',"MAILGUN_DOMAIN","CONTACT_RECIPIENT"]);

if(getenv('CAPTCHA_MODE') === 'RECAPTCHA'){
    $dotenv->required('RECAPTCHA_SECRET');
}

if(getenv('CAPTCHA_MODE') === 'RESPONSIVE'){
    $dotenv->required('ENCRYPTION_KEY');
}

$client = new \Http\Adapter\Guzzle6\Client();
$mailgun = new \Mailgun\Mailgun(getenv("MAILGUN_API_KEY"), $client);

const CAPTCHA_KEY = 'CAPTCHA';

const CYPHER = "AES-256-CBC";


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
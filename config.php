<?php

require 'vendor/autoload.php';


use Mailgun\Mailgun;
use ReCaptcha\ReCaptcha;

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$dotenv->required(['MAILGUN_API_KEY',"MAILGUN_DOMAIN","CONTACT_RECIPIENT"]);

$client = new \Http\Adapter\Guzzle6\Client();
$mailgun = new \Mailgun\Mailgun(getenv("MAILGUN_API_KEY"), $client);

?>
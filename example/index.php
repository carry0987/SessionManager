<?php
require dirname(__DIR__).'/vendor/autoload.php';

$session_name = bin2hex(random_bytes(16));
$sessionManager = new \carry0987\SessionManager\SessionManager($session_name);

$sessionManager->set('username', 'helloworld');
$username = $sessionManager->get('username');
$sessionManager->destroy();
var_dump($username);

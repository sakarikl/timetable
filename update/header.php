<?php
require dirname(__FILE__).'/../config.php';

authenticate($valid_users);

$backup = dirname(__FILE__).'/backup.txt';
$file = dirname(__FILE__).'/../data.txt';

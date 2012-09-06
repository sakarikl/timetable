<?php

require 'functions.php';
require 'lang.class.php';

$l = new Language('fi');

$valid_users = array('username' => 'password');

$discard_lines_regexp = array('{PVM.*KLO.*PAIKKA}',
                              '{^Ajoitus:$}'
                              );

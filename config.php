<?php

require 'functions.php';
require 'lang.class.php';

$l = new Language('fi');

$discard_lines_regexp = array('{PVM.*KLO.*PAIKKA}',
                              '{^Ajoitus:$}',
                              '{^([0-9]+\.)?[0-9]+\.([0-9]+)?[ -]+[0-9]+\.[0-9]+\.[0-9]+$}'
                              );
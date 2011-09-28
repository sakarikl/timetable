<?php
require 'header.php';

file_put_contents($file, file_get_contents($backup));

header('Location: index.php?ok=2');
exit;
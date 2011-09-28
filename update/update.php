<?php
require 'header.php';

if (isset($_REQUEST['content']) && $_REQUEST['content'])
{
  file_put_contents($backup, file_get_contents($file));
  file_put_contents($file, $_REQUEST['content']);

  header('Location: index.php?ok=1');
  exit;
}

header('Location: index.php?error=1');
exit;
<?php

require 'header.php';

$file = file_get_contents(dirname(__FILE__).'/../data.txt');

$ok_message = '';

if (isset($_REQUEST['ok']))
{
  switch ($_REQUEST['ok'])
  {
    case 1:
      $ok_message = $l->ok_save;
      break;
    case 2:
      $ok_message = $l->ok_revert;
      break;
  }
}

$error_message = (isset($_REQUEST['error'])) ? $l->error_save : '';

echo_header('../');

echo <<< END

<form method="post" action="update.php" class="update_form">

<textarea id="update_area" name="content">$file</textarea>

<br />
<input type="submit" value="$l->submit" class="submit_button"/>
</form>

<form method="post" action="revert.php" class="update_form revert"><input type="submit" value="$l->revert" class="revert_button" onclick="return confirm('$l->confirm_undo')"/></form>

<span class="ok message">$ok_message</span>
<span class="error message">$error_message</span>

END;

echo_footer();
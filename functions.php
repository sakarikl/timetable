<?php

/**
 * get class for hour slot
 *
 * @param int $reserved_space
 * @param bool $red
 * @param int $next_ending
 * @param bool $subject
 * @param int $extra_slots
 * @return string
 */
function get_class_for_hour_slot($reserved_space, &$red, $next_ending, $subject = false, $extra_slots = false)
{
  static $last_red = false;

  $red = ($red && $reserved_space >= 0);

  $class = ($reserved_space >= 0 || $subject) ? (($reserved_space > 0) ? 'subject_continues' : 'subject') : 'subject empty';

  $class .= $red ? ' red' : '';

  $class .= ($red && $next_ending == 0 && $reserved_space > 0) ? ' dashed' : '';

  $class .= ($last_red && $red && $extra_slots !== false) ? ' dashed_top' : '';

  $last_red = $red;

  return $class;
}

/**
 * Get max hour
 *
 * @param array $week
 */
function get_max_hour($week)
{
  $max = 0;
  for ($day=1; $day<6; $day++)
  {
    if (!isset($week[$day])) continue;

    for($hour=1; $hour<25; $hour++)
    {
      if (!isset($week[$day][$hour])) continue;

      foreach ($week[$day][$hour] as $minute => $subjects)
      {
        if ($max < $hour) $max = $hour;

        foreach ($subjects as $item)
        {
          if ($item['end_h'] > $max && $item['end_m'] != '00') $max = $item['end_h'];
          if ($item['end_h'] > $max && $item['end_m'] == '00') $max = $item['end_h']-1;
        }
      }
    }
  }
  return $max;
}

/**
 * echo page break if needed
 *
 * @param int $week_counter
 * @param int $max_hours
 * @param int $page_hours
 * @param bool $no_break
 */
function echo_page_break(&$week_counter, $max_hours, &$page_hours, &$no_break)
{
  $page_hours += $max_hours;

  if ($page_hours > 48)
  {
    echo '<span  class="page_break "/>';
    $week_counter =  1;
    $page_hours = $max_hours;
  }
  else if ($week_counter++%3 === 0 && !$no_break)
  {
    echo '<span  class="page_break "/>';
    $page_hours = $max_hours;
  }
  else $no_break = false;
}

/**
 * echo html header
 *
 */
function echo_header()
{
  echo <<<END
<html>
<head>
<title>Timetable</title>
<link href="style.css" rel="stylesheet" type="text/css">
</head>
<body>
END;
}

/**
 * Echo html footer
 *
 */
function echo_footer()
{
  echo <<<END
</body>
</html>
END;
}


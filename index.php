<html>
<head>
<title>Timetable</title>
<link href="style.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php

$data = file('data.txt');

$subject = '';
$lectures = array();
$last_was_subject = false;

foreach ($data as $line)
{
  $line = trim($line);
  if (!$line) continue;

  if (preg_match('{PVM.*KLO.*PAIKKA}', $line)) continue;
  if (preg_match('{^Ajoitus:$}', $line)) continue;
  if (preg_match('{^([0-9]+\.)?[0-9]+\.([0-9]+)?[ -]+[0-9]+\.[0-9]+\.[0-9]+$}', $line)) continue;

  if (!preg_match('{([^0-9\.]*)([0-9\.]*).*?(([0-9]+)\.([0-9]+))-(([0-9]+)\.([0-9]+))(.*)}', $line, $times))
  {
    $subject = $last_was_subject ? $subject.' '.$line : $line;
    $last_was_subject = true;
    continue;
  }
  $last_was_subject = false;

  $start  = strtotime($times[2].' '.$times[4].':'.$times[5]);
  $end    = strtotime($times[2].' '.$times[7].':'.$times[8]);
  $year   = (int)date('Y', $start);
  $week   = (int)date('W', $start);
  $day    = (int)date('w', $start);
  $length = $end-$start;

  if (!$start)
  {
    echo '<b>VIRHEELINEN RIVI: '.$times[0].'</b><br />';
    continue;
  }

  $lectures[$year][$week][$day][$times[4]][$times[5]][$subject] = array('h' => $times[7],
                                                                        'm' => $times[8],
                                                                        'info' => $times[9],
                                                                        'start' => $start,
                                                                        'end' => $end,
                                                                        'length' => $length,
                                                                        );
}

$hours_range = range(8,20);

$times_div = '<div class="times"><div class="day_title">&nbsp;</div>';
foreach ($hours_range as $hour) $times_div .= '<div class="times_hour">'.$hour.'</div>';
$times_div .= '</div>';

foreach ($lectures as $year => $weeks)
{
  for ($week=1; $week<54; $week++)
  {
    if (!isset($weeks[$week])) continue;

    $week_dates = get_week_dates($week, $year);
    echo "\n".'<div id="year_'.$year.'_'.$week.'" class="week"><div class="week_header"><div class="week_header_content">'.$year.' VIIKKO <strong>'.$week.'</strong> '.$week_dates[1].' - '.$week_dates[7].'</div></div>'.$times_div;

    for ($day=1; $day<6; $day++)
    {
      echo "\n".'<div class="day"><div class="day_title">'.get_day($day).' '.$week_dates[$day].'</div>';
      $reserved_space = $next_ending = $dashed = $extra_slots = -1;
      $red = false;
      foreach ($hours_range as $hour)
      {
        if (!isset($weeks[$week][$day][$hour]))
        {
          $extra_slots--;
          echo "\n".'<div class="'.get_class_for_hour_slot($reserved_space--, $red, $next_ending--).'">&nbsp;</div>';
          continue;
        }
        $count = 0;

        foreach ($weeks[$week][$day][$hour] as $minute => $subjects)
        {
          $red = (count($subjects) > 1 || $reserved_space > 0);

          foreach ($subjects as $subject => $item)
          {
            if (!$count)
            {
              //counts needed space for item
              $extra_slots = (int)floor($item['length']/3601);
              $reserved_space = max($extra_slots, $reserved_space);
              $next_ending = min($extra_slots, $reserved_space);

              if (count($weeks[$week][$day][$hour]) > 1) $red = true;

              if (!$red)
              {
                for ($i=1; $i<$extra_slots; $i++)
                {
                  if (isset($weeks[$week][$day][$hour+$i]))
                  {
                    $red = true;
                    continue;
                  }
                }
              }
              echo "\n".'<div class="'.get_class_for_hour_slot($reserved_space--, $red, $next_ending--, true, $extra_slots--).'">';
            }
            else
            {
              // more than one item starting at the same time (tested only with two concurrent items)
              //calculations must be done again

              $tmp = (int)floor($item['length']/3601);

              //+1 because previous loop used -- already
              $tmp_extra_slots = max($tmp, $extra_slots+1);
              $tmp_reserved_space = max($tmp_extra_slots, $reserved_space+1);
              $tmp_next_ending = min($tmp, max($extra_slots+1, $reserved_space+1));

              //-- to match previous loop
              $extra_slots = --$tmp_extra_slots;
              $next_ending = --$tmp_next_ending;
              $reserved_space = --$tmp_reserved_space;
            }
            echo $hour.'.'.$minute.' - '.$item['h'].'.'.$item['m'].' '.$subject.'<br /><strong>'.$item['info'].'</strong><br />';
            $count++;
          }
        }
        echo "\n".'</div>';
      }
      echo "\n".'</div>';
    }
    echo "\n".'</div><br style="clear:both;"/>';
  }
}

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
 * get current day as text
 *
 * @param int $day
 * @return string
 */
function get_day($day)
{
  switch ($day)
  {
    case 0 :
      return 'Su';
    case 1 :
      return 'Ma';
    case 2 :
      return 'Ti';
    case 3 :
      return 'Ke';
    case 4 :
      return 'To';
    case 5 :
      return 'Pe';
    case 6 :
      return 'La';
    default:
      return 'Undefined';
  }
}

/**
 * get week dates
 *
 * @param int $week
 * @param int $year
 * @return array
 */
function get_week_dates($week, $year)
{
  $time = strtotime($year . '0104 +' . ($week - 1) . ' weeks');
  $monday = strtotime('-' . (date('w', $time) - 1) . ' days', $time);

  $times = array();
  for ($i=0; $i<7; ++$i) $times[$i+1] = date('j.n', strtotime('+'.$i.' days', $monday));

  return $times;
}

?>
</body>
</html>
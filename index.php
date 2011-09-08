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
      $next_reserved = 0;
      foreach ($hours_range as $hour)
      {
        if (!isset($weeks[$week][$day][$hour]))
        {
          $class = ($next_reserved > 0) ? (($next_reserved > 1) ? 'subject_continues' : 'subject') : 'subject empty';
          $next_reserved--;
          echo "\n".'<div class="'.$class.'">&nbsp;</div>';
          continue;
        }

        foreach ($weeks[$week][$day][$hour] as $minute => $subjects)
        {
          $red = (count($subjects) > 1) ? ' red' : '';
          $once = false;
          foreach ($subjects as $subject => $item)
          {
            if (!$once)
            {
              $next_reserved = floor($item['length']/3601);
              $class = ($next_reserved) ? 'subject_continues' : 'subject';
              echo "\n".'<div class="'.$class.$red.'">';
            }
            else echo "\n".'<div class="red second">';

            echo $hour.'.'.$minute.' - '.$item['h'].'.'.$item['m'].' '.$subject.'<br /><strong>'.$item['info'].'</strong>';

            if ($once) echo "\n".'</div>';

            $once = true;
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
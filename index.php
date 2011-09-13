<?php

require 'config.php';

echo_header();

$data = file('data.txt');

$subject = '';
$lectures = array();
$last_was_subject = false;

foreach ($data as $line)
{
  $line = trim($line);
  if (!$line) continue;

  foreach ($discard_lines_regexp as $regexp)
  {
    if (preg_match($regexp, $line)) continue 2;
  }

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

  if (!$times[2] || !$start || !$end || !$week || !$year || !$length)
  {
    echo '<b>'.$l->wrong_input_line.': '.$times[0].'</b><br />';
    continue;
  }

  $lectures[$year][$week][$day][$times[4]][$times[5]][$subject] = array('start_h' => $times[4],
                                                                        'start_m' => $times[5],
  																																			'end_h'   => $times[7],
                                                                        'end_m'   => $times[8],
                                                                        'info'	  => $times[9],
                                                                        'start'   => $start,
                                                                        'end'     => $end,
                                                                        'length'  => $length,
                                                                        );
}

$hours_range = range(8,20);
$week_counter = $page_hours = 0;
$no_break = true;
foreach ($lectures as $year => $weeks)
{
  for ($week=1; $week<54; $week++)
  {
    if (!isset($weeks[$week])) continue;

    $max_hours = get_max_hour($weeks[$week]);
    echo_page_break($week_counter, $max_hours, $page_hours, $no_break);

    $times_div = '<div class="times"><div class="day_title first">&nbsp;</div>';
    foreach (range(8,$max_hours) as $hour) $times_div .= '<div class="times_hour">'.$hour.'</div>';
    $times_div .= '</div>';

    $week_dates = $l->getWeekDates($week, $year);
    echo "\n".'<div id="year_'.$year.'_'.$week.'" class="week"><div class="week_header"><div class="week_header_content">'.$year.' '.$l->week.' <strong>'.$week.'</strong> '.$week_dates[1].' - '.$week_dates[7].'</div></div>'.$times_div;

    for ($day=1; $day<6; $day++)
    {
      echo "\n".'<div class="day"><div class="day_title">'.$l->getDay($day).' '.$week_dates[$day].'</div>';
      $reserved_space = $next_ending = $dashed = $extra_slots = -1;
      $red = false;
      foreach ($hours_range as $hour)
      {
        if ($hour > $max_hours) continue;
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
            echo $hour.'.'.$minute.' - '.$item['end_h'].'.'.$item['end_m'].' '.$subject.'<br /><strong>'.$item['info'].'</strong><br />';
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

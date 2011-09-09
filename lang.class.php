<?php

/**
 * Language support for timetables
 *
 * @author sakarikl
 *
 */
class Language
{
  /**
   * Language identifier
   *
   * @var string
   */
  private $lang;

  /**
   * Language file
   *
   * @var string
   */
  private $lang_file;

  /**
   * Language string container
   *
   * @var array
   */
  private $strings = array();

  public function __construct($lang)
  {
    $this->lang = $lang;
    $this->lang_file = dirname(__FILE__).'/lang/'.$lang.'.txt';

    if (!is_file($this->lang_file)) throw new Exception('Language file not found '.$this->lang_file);

    foreach (file($this->lang_file) as $line)
    {
      $line = trim($line);
      if (!$line) continue;

      if (preg_match("{(.*) = '(.*)'}", $line, $matches)) $this->strings[$matches[1]] = $matches[2];
    }
  }

  /**
   * get language items
   *
   * @param string $name
   * @return string
   */
  public function __get($name)
  {
    if (!isset($this->strings[$name])) throw new Exception('translation missing from language file '.$name);
    return $this->strings[$name];
  }

  /**
   * get current day as text
   *
   * @param int $day
   * @return string
   */
  public function getDay($day)
  {
    switch ($day)
    {
      case 0 :
        return $this->sunday_short;
      case 1 :
        return $this->monday_short;
      case 2 :
        return $this->tuesday_short;
      case 3 :
        return $this->wednesday_short;
      case 4 :
        return $this->thursday_short;
      case 5 :
        return $this->friday_short;
      case 6 :
        return $this->saturday_short;
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
  function getWeekDates($week, $year)
  {
    $time = strtotime($year . '0104 +' . ($week - 1) . ' weeks');
    $monday = strtotime('-' . (date('w', $time) - 1) . ' days', $time);

    $times = array();
    for ($i=0; $i<7; ++$i) $times[$i+1] = date($this->date_format, strtotime('+'.$i.' days', $monday));

    return $times;
  }
}
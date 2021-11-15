<?php

namespace App\Services;

use Carbon\Carbon;

class DateService
{
  private $slashFormat  = 'd/m/Y';
  private $hyphenFormat = 'Y-m-d';
  private $timeFormat   = 'H:i:s';

  public function toSlashFormat($date)
  {
    return Carbon::createFromFormat($this->hyphenFormat, $date)->format($this->slashFormat);
  }

  public function toHyphenFormat($date)
  {
    return Carbon::createFromFormat($this->slashFormat, $date)->format($this->hyphenFormat);
  }

  public function formatTimestamp($dateTime, $includeTime = false)
  {
    $format = $includeTime ? $this->slashFormat . ' ' . $this->timeFormat : $this->slashFormat;

    return Carbon::parse($dateTime)->format($format);
  }
}

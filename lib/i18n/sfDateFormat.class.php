<?php
/**
 * sfDateFormat class file.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the BSD License.
 *
 * Copyright(c) 2004 by Qiang Xue. All rights reserved.
 *
 * To contact the author write to {@link mailto:qiang.xue@gmail.com Qiang Xue}
 * The latest version of PRADO can be obtained from:
 * {@link http://prado.sourceforge.net/}
 *
 * @author     Wei Zhuo <weizhuo[at]gmail[dot]com>
 *
 * @version    $Id: sfDateFormat.class.php 6806 2007-12-29 07:53:10Z fabien $
 */

/**
 * Gets the encoding utilities.
 */

/**
 * sfDateFormat class.
 *
 * The sfDateFormat class allows you to format dates and times with
 * predefined styles in a locale-sensitive manner. Formatting times
 * with the sfDateFormat class is similar to formatting dates.
 *
 * Formatting dates with the sfDateFormat class is a two-step process.
 * First, you create a formatter with the getDateInstance method.
 * Second, you invoke the format method, which returns a string containing
 * the formatted date.
 *
 * DateTime values are formatted using standard or custom patterns stored
 * in the properties of a DateTimeFormatInfo.
 *
 * @author Xiang Wei Zhuo <weizhuo[at]gmail[dot]com>
 *
 * @version v1.0, last update on Sat Dec 04 14:10:49 EST 2004
 */
class sfDateFormat
{
    /**
     * A list of tokens and their function call.
     *
     * @var array
     */
    protected $tokens = ['G' => 'Era', 'y' => 'year', 'M' => 'mon', 'd' => 'mday', 'h' => 'Hour12', 'H' => 'hours', 'm' => 'minutes', 's' => 'seconds', 'E' => 'wday', 'D' => 'yday', 'F' => 'DayInMonth', 'w' => 'WeekInYear', 'W' => 'WeekInMonth', 'a' => 'AMPM', 'k' => 'HourInDay', 'K' => 'HourInAMPM', 'z' => 'TimeZone'];

    /**
     * A list of methods, to be used by the token function calls.
     *
     * @var array
     */
    protected $methods = [];

    /**
     * The sfDateTimeFormatInfo, containing culture specific patterns and names.
     *
     * @var sfDateTimeFormatInfo
     */
    protected $formatInfo;

    /**
     * Initializes a new sfDateFormat.
     *
     * @param mixed either, null, a sfCultureInfo instance, a DateTimeFormatInfo instance, or a locale
     *
     * @return sfDateFormat instance
     */
    public function __construct($formatInfo = null)
    {
        if (is_null($formatInfo)) {
            $this->formatInfo = sfDateTimeFormatInfo::getInvariantInfo();
        } elseif ($formatInfo instanceof sfCultureInfo) {
            $this->formatInfo = $formatInfo->DateTimeFormat;
        } elseif ($formatInfo instanceof sfDateTimeFormatInfo) {
            $this->formatInfo = $formatInfo;
        } else {
            $this->formatInfo = sfDateTimeFormatInfo::getInstance($formatInfo);
        }

        $this->methods = get_class_methods($this);
    }

    /**
     * Guesses a date without calling strtotime.
     *
     * @author Olivier Verdier <Olivier.Verdier@gmail.com>
     *
     * @param mixed the time as integer or string in strtotime format
     * @param string the input pattern; default is sql date or timestamp
     *
     * @return array same array as the getdate function
     */
    public function getDate($time, $pattern = null)
    {
        if (is_null($time)) {
            return null;
        }

        // if the type is not a php timestamp
        $isString = (string) $time !== (string) (int) $time;

        if ($isString) {
            if (!$pattern) {
                if (strlen((string) $time) == 10) {
                    $pattern = 'i';
                } else { // otherwise, default:
                    $pattern = 'I';
                }
            }

            $pattern = $this->getPattern($pattern);
            $tokens = $this->getTokens($pattern);
            $pregPattern = '';
            $matchNames = [];
            foreach ($tokens as $token) {
                if ($matchName = $this->getFunctionName($token)) {
                    $pregPattern .= '(\d+)';
                    $matchNames[] = $matchName;
                } else {
                    $pregPattern .= '[^\d]+';
                }
            }
            preg_match('@'.$pregPattern.'@', (string) $time, $matches);

            array_shift($matches);

            if (count($matchNames) == count($matches)) {
                $date = array_combine($matchNames, $matches);
                // guess the date if input with two digits
                if (strlen($date['year']) == 2) {
                    $date['year'] = date('Y', mktime(0, 0, 0, 1, 1, $date['year']));
                }
                $date = array_map('intval', $date);
            }
        }

        // the last attempt has failed we fall back on the default method
        if (!isset($date)) {
            if ($isString) {
                $numericalTime = @strtotime((string) $time);
                if ($numericalTime === false) {
                    throw new sfException(sprintf('Impossible to parse date "%s" with format "%s".', $time, $pattern));
                }
            } else {
                $numericalTime = $time;
            }
            $date = @getdate($numericalTime);
        }

        // we set default values for the time
        foreach (['hours', 'minutes', 'seconds'] as $timeDiv) {
            if (!isset($date[$timeDiv])) {
                $date[$timeDiv] = 0;
            }
        }

        return $date;
    }

    /**
     * Formats a date according to the pattern.
     *
     * @param mixed the time as integer or string in strtotime format
     *
     * @return string formatted date time
     */
    public function format($time, $pattern = 'F', $inputPattern = null, $charset = 'UTF-8')
    {
        $date = $this->getDate($time, $inputPattern);

        if (is_null($pattern)) {
            $pattern = 'F';
        }

        $pattern = $this->getPattern($pattern);
        $tokens = $this->getTokens($pattern);

        for ($i = 0, $max = count($tokens); $i < $max; ++$i) {
            $pattern = $tokens[$i];
            if ($pattern[0] == "'" && $pattern[strlen((string) $pattern) - 1] == "'") {
                $preg = $pattern ? preg_replace('/(^\')|(\'$)/', '', $pattern) : $pattern;
                $tokens[$i] = str_replace('``````', '\'', $preg);
            } elseif ($pattern == '``````') {
                $tokens[$i] = '\'';
            } else {
                $rawFunction = $this->getFunctionName($pattern);
                $function = $rawFunction ? ucfirst((string) $rawFunction) : null;
                if ($function != null) {
                    $fName = 'get'.$function;
                    if (in_array($fName, $this->methods)) {
                        $tokens[$i] = $this->$fName($date, $pattern);
                    } else {
                        throw new sfException(sprintf('Function %s not found.', $function));
                    }
                }
            }
        }

        return I18N_toEncoding(implode('', $tokens), $charset);
    }

    /**
     * For a particular token, get the corresponding function to call.
     *
     * @param string token
     *
     * @return mixed the function if good token, null otherwise
     */
    protected function getFunctionName($token)
    {
        if (isset($this->tokens[$token[0]])) {
            return $this->tokens[$token[0]];
        }
    }

    /**
     * Gets the pattern from DateTimeFormatInfo or some predefined patterns.
     * If the $pattern parameter is an array of 2 element, it will assume
     * that the first element is the date, and second the time
     * and try to find an appropriate pattern and apply
     * DateTimeFormatInfo::formatDateTime
     * See the tutorial documentation for futher details on the patterns.
     *
     * @param mixed a pattern
     *
     * @return string a pattern
     *
     * @see DateTimeFormatInfo::formatDateTime()
     */
    public function getPattern($pattern)
    {
        if (is_array($pattern) && count($pattern) == 2) {
            return $this->formatInfo->formatDateTime($this->getPattern($pattern[0]), $this->getPattern($pattern[1]));
        }

        return match ($pattern) {
            'd' => $this->formatInfo->ShortDatePattern,
            'D' => $this->formatInfo->LongDatePattern,
            'p' => $this->formatInfo->MediumDatePattern,
            'P' => $this->formatInfo->FullDatePattern,
            't' => $this->formatInfo->ShortTimePattern,
            'T' => $this->formatInfo->LongTimePattern,
            'q' => $this->formatInfo->MediumTimePattern,
            'Q' => $this->formatInfo->FullTimePattern,
            'f' => $this->formatInfo->formatDateTime($this->formatInfo->LongDatePattern, $this->formatInfo->ShortTimePattern),
            'F' => $this->formatInfo->formatDateTime($this->formatInfo->LongDatePattern, $this->formatInfo->LongTimePattern),
            'g' => $this->formatInfo->formatDateTime($this->formatInfo->ShortDatePattern, $this->formatInfo->ShortTimePattern),
            'G' => $this->formatInfo->formatDateTime($this->formatInfo->ShortDatePattern, $this->formatInfo->LongTimePattern),
            'i' => 'yyyy-MM-dd',
            'I' => 'yyyy-MM-dd HH:mm:ss',
            'M', 'm' => 'MMMM dd',
            'R', 'r' => 'EEE, dd MMM yyyy HH:mm:ss',
            's' => 'yyyy-MM-ddTHH:mm:ss',
            'u' => 'yyyy-MM-dd HH:mm:ss z',
            'U' => 'EEEE dd MMMM yyyy HH:mm:ss',
            'Y', 'y' => 'yyyy MMMM',
            default => $pattern,
        };
    }

    /**
     * Returns an easy to parse input pattern
     * yy is replaced by yyyy and h by H.
     *
     * @param string pattern
     *
     * @return string input pattern
     */
    public function getInputPattern($pattern)
    {
        $pattern = $this->getPattern($pattern);

        $pattern = strtr($pattern, ['yyyy' => 'Y', 'h' => 'H', 'z' => '', 'a' => '']);
        $pattern = strtr($pattern, ['yy' => 'yyyy', 'Y' => 'yyyy']);

        return trim($pattern);
    }

    /**
     * Tokenizes the pattern. The tokens are delimited by group of
     * similar characters, e.g. 'aabb' will form 2 tokens of 'aa' and 'bb'.
     * Any substrings, starting and ending with a single quote (')
     * will be treated as a single token.
     *
     * @param string pattern
     *
     * @return array string tokens in an array
     */
    protected function getTokens($pattern)
    {
        $char = null;
        $tokens = [];
        $token = null;

        $text = false;

        for ($i = 0, $max = strlen((string) $pattern); $i < $max; ++$i) {
            if ($char == null || $pattern[$i] == $char || $text) {
                $token .= $pattern[$i];
            } else {
                $tokens[] = str_replace("''", "'", $token);
                $token = $pattern[$i];
            }

            if ($pattern[$i] == "'" && $text == false) {
                $text = true;
            } elseif ($text && $pattern[$i] == "'" && $char == "'") {
                $text = true;
            } elseif ($text && $char != "'" && $pattern[$i] == "'") {
                $text = false;
            }

            $char = $pattern[$i];
        }
        $tokens[] = $token;

        return $tokens;
    }

    // makes a unix date from our incomplete $date array
    protected function getUnixDate($date)
    {
        return getdate(mktime($date['hours'], $date['minutes'], $date['seconds'], $date['mon'], $date['mday'], $date['year']));
    }

    /**
     * Gets the year.
     * "yy" will return the last two digits of year.
     * "yyyy" will return the full integer year.
     *
     * @param array getdate format
     * @param string a pattern
     *
     * @return string year
     */
    protected function getYear($date, $pattern = 'yyyy')
    {
        $year = $date['year'];

        return match ($pattern) {
            'yy' => substr((string) $year, 2),
            'yyyy' => $year,
            default => throw new sfException('The pattern for year is either "yy" or "yyyy".'),
        };
    }

    /**
     * Gets the month.
     * "M" will return integer 1 through 12
     * "MM" will return the narrow month name, e.g. "J"
     * "MMM" will return the abrreviated month name, e.g. "Jan"
     * "MMMM" will return the month name, e.g. "January".
     *
     * @param array getdate format
     * @param string a pattern
     *
     * @return string month name
     */
    protected function getMon($date, $pattern = 'M')
    {
        $month = $date['mon'];

        return match ($pattern) {
            'M' => $month,
            'MM' => str_pad((string) $month, 2, '0', STR_PAD_LEFT),
            'MMM' => $this->formatInfo->AbbreviatedMonthNames[$month - 1],
            'MMMM' => $this->formatInfo->MonthNames[$month - 1],
            default => throw new sfException('The pattern for month is "M", "MM", "MMM", or "MMMM".'),
        };
    }

    /**
     * Gets the day of the week.
     * "E" will return integer 0 (for Sunday) through 6 (for Saturday).
     * "EE" will return the narrow day of the week, e.g. "M"
     * "EEE" will return the abrreviated day of the week, e.g. "Mon"
     * "EEEE" will return the day of the week, e.g. "Monday".
     *
     * @param array getdate format
     * @param string a pattern
     *
     * @return string day of the week
     */
    protected function getWday($date, $pattern = 'EEEE')
    {
        // if the $date comes from our home-made get date
        if (!isset($date['wday'])) {
            $date = $this->getUnixDate($date);
        }
        $day = $date['wday'];

        return match ($pattern) {
            'E' => $day,
            'EE' => $this->formatInfo->NarrowDayNames[$day],
            'EEE' => $this->formatInfo->AbbreviatedDayNames[$day],
            'EEEE' => $this->formatInfo->DayNames[$day],
            default => throw new sfException('The pattern for day of the week is "E", "EE", "EEE", or "EEEE".'),
        };
    }

    /**
     * Gets the day of the month.
     * "d" for non-padding, "dd" will always return 2 characters.
     *
     * @param array getdate format
     * @param string a pattern
     *
     * @return string day of the month
     */
    protected function getMday($date, $pattern = 'd')
    {
        $day = $date['mday'];

        return match ($pattern) {
            'd' => $day,
            'dd' => str_pad((string) $day, 2, '0', STR_PAD_LEFT),
            'dddd' => $this->getWday($date),
            default => throw new sfException('The pattern for day of the month is "d", "dd" or "dddd".'),
        };
    }

    /**
     * Gets the era. i.e. in gregorian, year > 0 is AD, else BC.
     *
     * @todo How to support multiple Eras?, e.g. Japanese.
     *
     * @param array getdate format
     * @param string a pattern
     *
     * @return string era
     */
    protected function getEra($date, $pattern = 'G')
    {
        if ($pattern != 'G') {
            throw new sfException('The pattern for era is "G".');
        }

        return $this->formatInfo->getEra($date['year'] > 0 ? 1 : 0);
    }

    /**
     * Gets the hours in 24 hour format, i.e. [0-23].
     * "H" for non-padding, "HH" will always return 2 characters.
     *
     * @param array getdate format
     * @param string a pattern
     *
     * @return string hours in 24 hour format
     */
    protected function getHours($date, $pattern = 'H')
    {
        $hour = $date['hours'];

        return match ($pattern) {
            'H' => $hour,
            'HH' => str_pad((string) $hour, 2, '0', STR_PAD_LEFT),
            default => throw new sfException('The pattern for 24 hour format is "H" or "HH".'),
        };
    }

    /**
     * Get the AM/PM designator, 12 noon is PM, 12 midnight is AM.
     *
     * @param array getdate format
     * @param string a pattern
     *
     * @return string AM or PM designator
     */
    protected function getAMPM($date, $pattern = 'a')
    {
        if ($pattern != 'a') {
            throw new sfException('The pattern for AM/PM marker is "a".');
        }

        return $this->formatInfo->AMPMMarkers[intval($date['hours'] / 12)];
    }

    /**
     * Gets the hours in 12 hour format.
     * "h" for non-padding, "hh" will always return 2 characters.
     *
     * @param array getdate format
     * @param string a pattern
     *
     * @return string hours in 12 hour format
     */
    protected function getHour12($date, $pattern = 'h')
    {
        $hour = $date['hours'];
        $hour = ($hour == 12 | $hour == 0) ? 12 : $hour % 12;

        return match ($pattern) {
            'h' => $hour,
            'hh' => str_pad($hour, 2, '0', STR_PAD_LEFT),
            default => throw new sfException('The pattern for 24 hour format is "H" or "HH".'),
        };
    }

    /**
     * Gets the minutes.
     * "m" for non-padding, "mm" will always return 2 characters.
     *
     * @param array getdate format
     * @param string a pattern
     *
     * @return string minutes
     */
    protected function getMinutes($date, $pattern = 'm')
    {
        $minutes = $date['minutes'];

        return match ($pattern) {
            'm' => $minutes,
            'mm' => str_pad((string) $minutes, 2, '0', STR_PAD_LEFT),
            default => throw new sfException('The pattern for minutes is "m" or "mm".'),
        };
    }

    /**
     * Gets the seconds.
     * "s" for non-padding, "ss" will always return 2 characters.
     *
     * @param array getdate format
     * @param string a pattern
     *
     * @return string seconds
     */
    protected function getSeconds($date, $pattern = 's')
    {
        $seconds = $date['seconds'];

        return match ($pattern) {
            's' => $seconds,
            'ss' => str_pad((string) $seconds, 2, '0', STR_PAD_LEFT),
            default => throw new sfException('The pattern for seconds is "s" or "ss".'),
        };
    }

    /**
     * Gets the timezone from the server machine.
     *
     * @todo How to get the timezone for a different region?
     *
     * @param array getdate format
     * @param string a pattern
     *
     * @return string time zone
     */
    protected function getTimeZone($date, $pattern = 'z')
    {
        if ($pattern != 'z') {
            throw new sfException('The pattern for time zone is "z".');
        }

        return @date('T', @mktime($date['hours'], $date['minutes'], $date['seconds'], $date['mon'], $date['mday'], $date['year']));
    }

    /**
     * Gets the day in the year, e.g. [1-366].
     *
     * @param array getdate format
     * @param string a pattern
     *
     * @return int hours in AM/PM format
     */
    protected function getYday($date, $pattern = 'D')
    {
        if ($pattern != 'D') {
            throw new sfException('The pattern for day in year is "D".');
        }

        return $date['yday'];
    }

    /**
     * Gets day in the month.
     *
     * @param array getdate format
     * @param string a pattern
     *
     * @return int day in month
     */
    protected function getDayInMonth($date, $pattern = 'FF')
    {
        return match ($pattern) {
            'F' => @date('j', @mktime(0, 0, 0, $date['mon'], $date['mday'], $date['year'])),
            'FF' => @date('d', @mktime(0, 0, 0, $date['mon'], $date['mday'], $date['year'])),
            default => throw new sfException('The pattern for day in month is "F" or "FF".'),
        };
    }

    /**
     * Gets the week in the year.
     *
     * @param array getdate format
     * @param string a pattern
     *
     * @return int week in year
     */
    protected function getWeekInYear($date, $pattern = 'w')
    {
        if ($pattern != 'w') {
            throw new sfException('The pattern for week in year is "w".');
        }

        return @date('W', @mktime(0, 0, 0, $date['mon'], $date['mday'], $date['year']));
    }

    /**
     * Gets week in the month.
     *
     * @param array getdate format
     *
     * @return int week in month
     */
    protected function getWeekInMonth($date, $pattern = 'W')
    {
        if ($pattern != 'W') {
            throw new sfException('The pattern for week in month is "W".');
        }

        return @date('W', @mktime(0, 0, 0, $date['mon'], $date['mday'], $date['year'])) - date('W', mktime(0, 0, 0, $date['mon'], 1, $date['year']));
    }

    /**
     * Gets the hours [1-24].
     *
     * @param array getdate format
     * @param string a pattern
     *
     * @return int hours [1-24]
     */
    protected function getHourInDay($date, $pattern = 'k')
    {
        if ($pattern != 'k') {
            throw new sfException('The pattern for hour in day is "k".');
        }

        return $date['hours'] + 1;
    }

    /**
     * Gets the hours in AM/PM format, e.g [1-12].
     *
     * @param array getdate format
     * @param string a pattern
     *
     * @return int hours in AM/PM format
     */
    protected function getHourInAMPM($date, $pattern = 'K')
    {
        if ($pattern != 'K') {
            throw new sfException('The pattern for hour in AM/PM is "K".');
        }

        return ($date['hours'] + 1) % 12;
    }
}

<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version    SVN: $Id: sfI18N.class.php 10841 2008-08-13 12:51:18Z noel $
 */
class sfI18N
{
    protected $context;
    protected $globalMessageSource;
    protected $messageSource;
    protected $messageFormat;

    protected static $instance;

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            $class = self::class;
            self::$instance = new $class();
        }

        return self::$instance;
    }

    public function initialize($context)
    {
        $this->context = $context;

        $this->globalMessageSource = $this->createMessageSource(sfConfig::get('sf_app_i18n_dir'));
        $this->globalMessageFormat = $this->createMessageFormat($this->globalMessageSource);
    }

    public function setMessageSourceDir($dir, $culture)
    {
        $this->messageSource = $this->createMessageSource($dir);
        $this->messageSource->setCulture($culture);
        $this->messageFormat = $this->createMessageFormat($this->messageSource);

        $this->globalMessageSource->setCulture($culture);
        $this->globalMessageFormat = $this->createMessageFormat($this->globalMessageSource);
    }

    public function createMessageSource($dir)
    {
        if (in_array(sfConfig::get('sf_i18n_source'), ['Creole', 'MySQL', 'SQLite'])) {
            $messageSource = sfMessageSource::factory(sfConfig::get('sf_i18n_source'), sfConfig::get('sf_i18n_database', 'default'));
        } else {
            $messageSource = sfMessageSource::factory(sfConfig::get('sf_i18n_source'), $dir);
        }

        if (sfConfig::get('sf_i18n_cache')) {
            $subdir = str_replace(str_replace('/', DIRECTORY_SEPARATOR, sfConfig::get('sf_root_dir')), '', $dir);
            $cacheDir = str_replace('/', DIRECTORY_SEPARATOR, sfConfig::get('sf_i18n_cache_dir').$subdir);

            $cache = new sfMessageCache();
            $cache->initialize(['cacheDir' => $cacheDir, 'lifeTime' => 86400]);

            $messageSource->setCache($cache);
        }

        return $messageSource;
    }

    public function createMessageFormat($source)
    {
        $messageFormat = new sfMessageFormat($source, sfConfig::get('sf_charset'));

        if (sfConfig::get('sf_debug') && sfConfig::get('sf_i18n_debug')) {
            $messageFormat->setUntranslatedPS([sfConfig::get('sf_i18n_untranslated_prefix'), sfConfig::get('sf_i18n_untranslated_suffix')]);
        }

        return $messageFormat;
    }

    public function setCulture($culture)
    {
        if ($this->messageSource) {
            $this->messageSource->setCulture($culture);
            $this->messageFormat = $this->createMessageFormat($this->messageSource);
        }

        $this->globalMessageSource->setCulture($culture);
        $this->globalMessageFormat = $this->createMessageFormat($this->globalMessageSource);
    }

    public function getMessageSource()
    {
        return $this->messageSource;
    }

    public function getGlobalMessageSource()
    {
        return $this->globalMessageSource;
    }

    public function getMessageFormat()
    {
        return $this->messageFormat;
    }

    public function getGlobalMessageFormat()
    {
        return $this->globalMessageFormat;
    }

    public function __($string, $args = [], $catalogue = 'messages')
    {
        $retval = $this->messageFormat->formatExists($string, $args, $catalogue);

        if (!$retval) {
            $retval = $this->globalMessageFormat->format($string, $args, $catalogue);
        }

        return $retval;
    }

    public static function getCountry($iso, $culture)
    {
        $c = new sfCultureInfo($culture);
        $countries = $c->getCountries();

        return (array_key_exists($iso, $countries)) ? $countries[$iso] : '';
    }

    public static function getNativeName($culture)
    {
        $cult = new sfCultureInfo($culture);

        return $cult->getNativeName();
    }

    // Return timestamp from a date formatted with a given culture
    public static function getTimestampForCulture($date, $culture)
    {
        [$d, $m, $y] = self::getDateForCulture($date, $culture);
        [$hour, $minute] = self::getTimeForCulture($date, $culture);

        return mktime($hour, $minute, 0, $m, $d, $y);
    }

    // Return a d, m and y from a date formatted with a given culture
    public static function getDateForCulture($date, $culture)
    {
        if (!$date) {
            return 0;
        }

        $dateFormatInfo = @sfDateTimeFormatInfo::getInstance($culture);
        $dateFormat = $dateFormatInfo->getShortDatePattern();

        // We construct the regexp based on date format
        $dateRegexp = $dateFormat ? preg_replace('/[dmy]+/i', '(\d+)', $dateFormat) : $dateFormat;

        // We parse date format to see where things are (m, d, y)
        $a = ['d' => strpos($dateFormat, 'd'), 'm' => strpos($dateFormat, 'M'), 'y' => strpos($dateFormat, 'y')];
        $tmp = array_flip($a);
        ksort($tmp);
        $i = 0;
        $c = [];
        foreach ($tmp as $value) {
            $c[++$i] = $value;
        }
        $datePositions = array_flip($c);

        // We find all elements
        if (preg_match("~$dateRegexp~", (string) $date, $matches)) {
            // We get matching timestamp
            return [$matches[$datePositions['d']], $matches[$datePositions['m']], $matches[$datePositions['y']]];
        } else {
            return null;
        }
    }

    /**
     * Returns the hour, minute from a date formatted with a given culture.
     *
     * @param string $culture The culture
     *
     * @return array An array with the hour and minute
     */
    protected static function getTimeForCulture($time, $culture)
    {
        if (!$time) {
            return 0;
        }

        $timeFormatInfo = @sfDateTimeFormatInfo::getInstance($culture);
        $timeFormat = $timeFormatInfo->getShortTimePattern();

        // We construct the regexp based on time format
        $timeRegexp = $timeFormat ? preg_replace(['/[^hm:]+/i', '/[hm]+/i'], ['', '(\d+)'], $timeFormat) : $timeFormat;

        // We parse time format to see where things are (h, m)
        $a = ['h' => str_contains($timeFormat, 'H') ? strpos($timeFormat, 'H') : strpos($timeFormat, 'h'), 'm' => strpos($timeFormat, 'm')];
        $tmp = array_flip($a);
        ksort($tmp);
        $i = 0;
        $c = [];

        foreach ($tmp as $value) {
            $c[++$i] = $value;
        }

        $timePositions = array_flip($c);

        // We find all elements
        if (preg_match("~$timeRegexp~", (string) $time, $matches)) {
            // We get matching timestamp
            return [$matches[$timePositions['h']], $matches[$timePositions['m']]];
        } else {
            return null;
        }
    }
}

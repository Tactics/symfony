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
 * @version    SVN: $Id: sfFileLogger.class.php 10964 2008-08-19 18:33:50Z fabien $
 */
class sfFileLogger
{
    protected $fp;

    /**
     * Initializes the file logger.
     *
     * @param array Options for the logger
     */
    public function initialize($options = [])
    {
        if (!isset($options['file'])) {
            throw new sfConfigurationException('File option is mandatory for a file logger');
        }

        $dir = dirname((string) $options['file']);

        if (!is_dir($dir)) {
            mkdir($dir, 0777, 1);
        }

        $fileExists = file_exists($options['file']);
        if (!is_writable($dir) || ($fileExists && !is_writable($options['file']))) {
            throw new sfFileException(sprintf('Unable to open the log file "%s" for writing', $options['file']));
        }

        $this->fp = fopen($options['file'], 'a');
        if (!$fileExists) {
            chmod($options['file'], 0666);
        }
    }

    /**
     * Logs a message.
     *
     * @param string Message
     * @param string Message priority
     * @param string Message priority name
     */
    public function log($message, $priority, $priorityName)
    {
        $time = date('Y-m-d H:i:s', time());
        $line = sprintf('%s %s [%s] %s%s', $time, 'symfony', $priorityName, $message, DIRECTORY_SEPARATOR == '\\' ? "\r\n" : "\n");

        flock($this->fp, LOCK_EX);
        fwrite($this->fp, $line);
        flock($this->fp, LOCK_UN);
    }

    /**
     * Executes the shutdown method.
     */
    public function shutdown()
    {
        if (is_resource($this->fp)) {
            fclose($this->fp);
        }
    }
}

<?php

/**
 * @package    pake
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @copyright  2004-2005 Fabien Potencier <fabien.potencier@symfony-project.com>
 * @license    see the LICENSE file included in the distribution
 * @version    SVN: $Id: pakeYaml.class.php 2978 2006-12-08 19:15:44Z fabien $
 */

class pakeYaml
{
    public static function load($input)
    {
        // syck is prefered over spyc
        if (function_exists('syck_load')) {
            if (!empty($input) && is_readable($input)) {
                $input = file_get_contents($input);
            }

            return syck_load($input);
        } else {
            $spyc = new pakeSpyc();

            return $spyc->load($input);
        }
    }

    public static function dump($array)
    {
        $spyc = new pakeSpyc();

        return $spyc->dump($array);
    }
}

<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr <sean@code-box.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <sean@code-box.org>
 *
 * @version    SVN: $Id: sfHtmlValidator.class.php 7791 2008-03-09 21:57:09Z fabien $
 */
class sfHtmlValidator extends sfValidator
{
    /**
     * Executes this validator.
     *
     * @param mixed A file or parameter value/array
     * @param error An error message reference
     *
     * @return bool true, if this validator executes successfully, otherwise false
     */
    public function execute(&$value, &$error)
    {
        if (trim(strip_tags((string) $value)) == '') {
            // If page contains an object or an image, it's ok
            if (preg_match('/<img/i', (string) $value) || preg_match('/<object/i', (string) $value)) {
                return true;
            } else {
                $error = $this->getParameterHolder()->get('html_error');

                return false;
            }
        }

        return true;
    }

    /**
     * Initializes this validator.
     *
     * @param sfContext The current application context
     * @param array   An associative array of initialization parameters
     *
     * @return bool true, if initialization completes successfully, otherwise false
     */
    public function initialize($context, $parameters = null)
    {
        // initialize parent
        parent::initialize($context);

        // set defaults
        $this->getParameterHolder()->set('html_error', 'Invalid input');

        $this->getParameterHolder()->add($parameters);

        return true;
    }
}

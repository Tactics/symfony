<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Abstract output escaping decorator class for "getter" objects.
 *
 * @see        sfOutputEscaper
 *
 * @author     Mike Squire <mike@somosis.co.uk>
 *
 * @version    SVN: $Id: sfOutputEscaperGetterDecorator.class.php 3232 2007-01-11 20:51:54Z fabien $
 */
abstract class sfOutputEscaperGetterDecorator extends sfOutputEscaper
{
    /**
     * Returns the raw, unescaped value associated with the key supplied.
     *
     * The key might be an index into an array or a value to be passed to the
     * decorated object's get() method.
     *
     * @param string The key to retrieve
     *
     * @return mixed The value
     */
    abstract public function getRaw($key);

    /**
     * Returns the escaped value associated with the key supplied.
     *
     * Typically (using this implementation) the raw value is obtained using the
     * {@link getRaw()} method, escaped and the result returned.
     *
     * @param string The key to retieve
     * @param string The escaping method (a PHP function) to use
     *
     * @return mixed The escaped value
     */
    public function get($key, $escapingMethod = null)
    {
        if (!$escapingMethod) {
            $escapingMethod = $this->escapingMethod;
        }

        return sfOutputEscaper::escape($escapingMethod, $this->getRaw($key));
    }
}

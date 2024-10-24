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
 * @version    SVN: $Id: sfConsoleRequest.class.php 7791 2008-03-09 21:57:09Z fabien $
 */
class sfConsoleRequest extends sfRequest
{
    /**
     * Initializes this sfRequest.
     *
     * @param sfContext A sfContext instance
     * @param array   An associative array of initialization parameters
     * @param array   An associative array of initialization attributes
     *
     * @return bool true, if initialization completes successfully, otherwise false
     *
     * @throws <b>sfInitializationException</b> If an error occurs while initializing this Request
     */
    public function initialize($context, $parameters = [], $attributes = [])
    {
        parent::initialize($context, $parameters, $attributes);

        $this->getParameterHolder()->add($_SERVER['argv']);
    }

    /**
     * Executes the shutdown procedure.
     */
    public function shutdown()
    {
    }
}

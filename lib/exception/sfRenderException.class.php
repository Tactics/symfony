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
 * sfRenderException is thrown when a view's pre-render check fails.
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <sean@code-box.org>
 *
 * @version    SVN: $Id: sfRenderException.class.php 7791 2008-03-09 21:57:09Z fabien $
 */
class sfRenderException extends sfException
{
    /**
     * Class constructor.
     *
     * @param string The error message
     * @param int    The error code
     */
    public function __construct($message = null, $code = 0)
    {
        $this->setName('sfRenderException');
        parent::__construct($message, $code);
    }
}

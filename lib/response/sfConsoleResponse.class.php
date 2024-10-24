<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfConsoleResponse provides methods for manipulating client response in cli environment.
 *
 * @author     Tristan Rivoallan <trivoallan@clever-age.com>
 *
 * @version    SVN: $Id: sfConsoleResponse.class.php 3250 2007-01-12 20:09:11Z fabien $
 */
class sfConsoleResponse extends sfResponse
{
    /**
     * Executes the shutdown procedure.
     */
    public function shutdown()
    {
    }
}

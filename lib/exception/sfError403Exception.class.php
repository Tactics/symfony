<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfError404Exception is thrown when a 404 error occurs in an action.
 *
 * @package    symfony
 * @subpackage exception
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfError404Exception.class.php 3243 2007-01-12 14:22:50Z fabien $
 */
class sfError403Exception extends sfException
{
  /**
   * Class constructor.
   *
   * @param string The error message
   * @param int    The error code
   */
  public function __construct($message = null, $code = 0)
  {
    $this->setName('sfError403Exception');
    parent::__construct($message, $code);
  }

  /**
   * Forwards to the 403 action.
   *
   * @param Exception An Exception implementation instance
   */
  public function printStackTrace($exception = null)
  {
    $sfContext = sfContext::getInstance();
    $sfContext->getRequest()->setParameter('message', $this->getMessage());
    $sfContext->getController()->forward(sfConfig::get('sf_error_403_module'), sfConfig::get('sf_error_403_action'));
  }
}

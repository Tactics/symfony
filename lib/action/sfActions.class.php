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
 * sfActions executes all the logic for the current request.
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <sean@code-box.org>
 *
 * @version    SVN: $Id: sfActions.class.php 19911 2009-07-06 07:52:48Z FabianLange $
 */
abstract class sfActions extends sfAction
{
    /**
     * Dispatches to the action defined by the 'action' parameter of the sfRequest object.
     *
     * This method try to execute the executeXXX() method of the current object where XXX is the
     * defined action name.
     *
     * @return mixed A string containing the view name associated with this action
     *
     * @throws sfInitializationException
     *
     * @see sfAction
     */
    public function execute(): mixed
    {
        // dispatch action
        $actionToRun = 'execute'.ucfirst($this->getActionName());

        if ($actionToRun === 'execute') {
            // no action given
            $error = 'sfAction initialization failed for module "%s". There was no action given.';
            $error = sprintf($error, $this->getModuleName());
            throw new sfInitializationException($error);
        }

        if (!is_callable([$this, $actionToRun])) {
            // action not found
            $error = 'sfAction initialization failed for module "%s", action "%s". You must create a "%s" method.';
            $error = sprintf($error, $this->getModuleName(), $this->getActionName(), $actionToRun);
            throw new sfInitializationException($error);
        }

        if (sfConfig::get('sf_logging_enabled')) {
            $this->getContext()->getLogger()->info('{sfAction} call "'.static::class.'->'.$actionToRun.'()"');
        }

        // run action
        return $this->$actionToRun();
    }
}

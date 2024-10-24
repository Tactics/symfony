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
 * sfActionStackEntry represents information relating to a single sfAction request during a single HTTP request.
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <sean@code-box.org>
 *
 * @version    SVN: $Id: sfActionStackEntry.class.php 7791 2008-03-09 21:57:09Z fabien $
 */
class sfActionStackEntry
{
    protected $presentation;
    protected $viewInstance;

    /**
     * Class constructor.
     *
     * @param string A module name
     * @param string An action name
     * @param sfAction An sfAction implementation instance
     */
    public function __construct(protected $moduleName, protected $actionName, protected $actionInstance)
    {
    }

    /**
     * Retrieves this entry's action name.
     *
     * @return string An action name
     */
    public function getActionName()
    {
        return $this->actionName;
    }

    /**
     * Retrieves this entry's action instance.
     *
     * @return sfAction An sfAction implementation instance
     */
    public function getActionInstance()
    {
        return $this->actionInstance;
    }

    /**
     * Retrieves this entry's view instance.
     *
     * @return sfView a sfView implementation instance
     */
    public function getViewInstance()
    {
        return $this->viewInstance;
    }

    /**
     * Sets this entry's view instance.
     *
     * @param sfView a sfView implementation instance
     */
    public function setViewInstance($viewInstance)
    {
        $this->viewInstance = $viewInstance;
    }

    /**
     * Retrieves this entry's module name.
     *
     * @return string A module name
     */
    public function getModuleName()
    {
        return $this->moduleName;
    }

    /**
     * Retrieves this entry's rendered view presentation.
     *
     * This will only exist if the view has processed and the render mode is set to sfView::RENDER_VAR.
     *
     * @return string Rendered view presentation
     */
    public function &getPresentation()
    {
        return $this->presentation;
    }

    /**
     * Sets the rendered presentation for this action.
     *
     * @param string a rendered presentation
     */
    public function setPresentation(&$presentation)
    {
        $this->presentation = &$presentation;
    }
}

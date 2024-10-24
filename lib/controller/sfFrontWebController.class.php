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
 * sfFrontWebController allows you to centralize your entry point in your web
 * application, but at the same time allow for any module and action combination
 * to be requested.
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <sean@code-box.org>
 *
 * @version    SVN: $Id: sfFrontWebController.class.php 8319 2008-04-05 18:10:53Z FabianLange $
 */
class sfFrontWebController extends sfWebController
{
    /**
     * Dispatches a request.
     *
     * This will determine which module and action to use by request parameters specified by the user.
     */
    public function dispatch()
    {
        try {
            if (sfConfig::get('sf_logging_enabled')) {
                $this->getContext()->getLogger()->info('{sfController} dispatch request');
            }

            // reinitialize filters (needed for unit and functional tests)
            sfFilter::$filterCalled = [];

            // determine our module and action
            $request = $this->getContext()->getRequest();
            $moduleName = $request->getParameter('module');
            $actionName = $request->getParameter('action');

            // tideways
            if (@class_exists('Tideways\Profiler')) {
                $transactionName = $moduleName.'::'.$actionName;
                Tideways\Profiler::setTransactionName($transactionName);
            }

            // make the first request
            $this->forward($moduleName, $actionName);
        } catch (sfException $e) {
            if (sfConfig::get('sf_test')) {
                throw $e;
            }

            $e->printStackTrace();
        } catch (Exception $e) {
            if (sfConfig::get('sf_test')) {
                throw $e;
            }

            try {
                // wrap non symfony exceptions
                $sfException = new sfException($e->getMessage().' in '.$e->getFile().' line '.$e->getLine());
                $sfException->printStackTrace($e);
            } catch (Exception) {
                header('HTTP/1.0 500 Internal Server Error');
            }
        }
    }
}

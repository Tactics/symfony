<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfRenderingFilter is the last filter registered for each filter chain. This
 * filter does the rendering.
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version    SVN: $Id: sfRenderingFilter.class.php 6648 2007-12-20 20:24:19Z fabien $
 */
class sfRenderingFilter extends sfFilter
{
    /**
     * Executes this filter.
     *
     * @param sfFilterChain the filter chain
     *
     * @throws <b>sfInitializeException</b> If an error occurs during view initialization
     * @throws <b>sfViewException</b>       If an error occurs while executing the view
     */
    public function execute($filterChain)
    {
        // execute next filter
        $filterChain->execute();

        if (sfConfig::get('sf_logging_enabled')) {
            $this->getContext()->getLogger()->info('{sfFilter} render to client');
        }

        // get response object
        $response = $this->getContext()->getResponse();

        // send headers
        if (method_exists($response, 'sendHttpHeaders')) {
            $response->sendHttpHeaders();
        }

        // send content
        $response->sendContent();

        // log timers information
        if (sfConfig::get('sf_debug') && sfConfig::get('sf_logging_enabled')) {
            $logger = $this->getContext()->getLogger();
            foreach (sfTimerManager::getTimers() as $name => $timer) {
                $logger->info(sprintf('{sfTimerManager} %s %.2f ms (%d)', $name, $timer->getElapsedTime() * 1000, $timer->getCalls()));
            }
        }
    }
}

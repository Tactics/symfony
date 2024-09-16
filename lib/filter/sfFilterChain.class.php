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
 * sfFilterChain manages registered filters for a specific context.
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <sean@code-box.org>
 *
 * @version    SVN: $Id: sfFilterChain.class.php 7791 2008-03-09 21:57:09Z fabien $
 */
class sfFilterChain
{
    protected $chain = [];
    protected $index = -1;

    /**
     * Executes the next filter in this chain.
     */
    public function execute()
    {
        // skip to the next filter
        ++$this->index;

        if ($this->index < count($this->chain)) {
            if (sfConfig::get('sf_logging_enabled')) {
                sfContext::getInstance()->getLogger()->info(sprintf('{sfFilter} executing filter "%s"', $this->chain[$this->index]::class));
            }

            // execute the next filter
            $this->chain[$this->index]->execute($this);
        }
    }

    /**
     * Returns true if the filter chain contains a filter of a given class.
     *
     * @param string The class name of the filter
     *
     * @return bool true if the filter exists, false otherwise
     */
    public function hasFilter($class)
    {
        foreach ($this->chain as $filter) {
            if ($filter instanceof $class) {
                return true;
            }
        }

        return false;
    }

    /**
     * Registers a filter with this chain.
     *
     * @param sfFilter a sfFilter implementation instance
     */
    public function register($filter)
    {
        $this->chain[] = $filter;
    }
}

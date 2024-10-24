<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfFillInFormFilter fills in forms.
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version    SVN: $Id: sfFillInFormFilter.class.php 3244 2007-01-12 14:46:11Z fabien $
 */
class sfFillInFormFilter extends sfFilter
{
    /**
     * Executes this filter.
     *
     * @param sfFilterChain A sfFilterChain instance
     */
    public function execute($filterChain)
    {
        // execute next filter
        $filterChain->execute();

        $context = $this->getContext();
        $response = $context->getResponse();
        $request = $context->getRequest();

        $fillInForm = new sfFillInForm();

        // converters
        foreach ($this->getParameter('converters', []) as $functionName => $fields) {
            $fillInForm->addConverter($functionName, $fields);
        }

        // skip fields
        $fillInForm->setSkipFields((array) $this->getParameter('skip_fields', []));

        // types
        $excludeTypes = (array) $this->getParameter('exclude_types', ['hidden', 'password']);
        $checkTypes = (array) $this->getParameter('check_types', ['text', 'checkbox', 'radio', 'password', 'hidden']);
        $fillInForm->setTypes(array_diff($checkTypes, $excludeTypes));

        // fill in
        $method = 'fillIn'.ucfirst(strtolower($this->getParameter('content_type', 'Html')));

        try {
            $content = $fillInForm->$method($response->getContent(), $this->getParameter('name'), $this->getParameter('id'), $request->getParameterHolder()->getAll());
            $response->setContent($content);
        } catch (sfException $e) {
            if (sfConfig::get('sf_logging_enabled')) {
                $this->getContext()->getLogger()->err(sprintf('{sfFilter} %s', $e->getMessage()));
            }
        }
    }
}

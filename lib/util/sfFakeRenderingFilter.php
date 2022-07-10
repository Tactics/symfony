<?php

class sfFakeRenderingFilter extends sfFilter
{
    public function execute($filterChain)
    {
        $filterChain->execute();

        $this->getContext()->getResponse()->sendContent();
    }
}

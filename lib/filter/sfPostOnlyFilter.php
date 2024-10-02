<?php

class sfPostOnlyFilter extends sfFilter
{
    public function execute($filterchain)
    {
        $context = $this->getContext();
        $request = $context->getRequest();
        $controller = $context->getController();
        $actionEntry = $controller->getActionStack()->getLastEntry();
        /** @var sfAction $actionInstance */
        $actionInstance = $actionEntry->getActionInstance();

        $actionIsPostOnly = $this->actionIsPostOnly($actionInstance);

        if ($actionIsPostOnly && !$request->getMethod() !== sfRequest::POST) {
            $this->forward404PostOnly($request, $controller);
        }

        $filterchain->execute();
    }

    /**
     * @throws ReflectionException
     */
    private function actionIsPostOnly(sfAction $actionInstance): bool
    {
        $attributes = [];
        $validateAction = 'validate' . ucfirst($actionInstance->getActionName());
        if (method_exists($actionInstance, $validateAction)) {
            $refValidateMethod = new ReflectionMethod($actionInstance::class, $validateAction);
            $attributes = array_merge($attributes, $refValidateMethod->getAttributes(PostOnly::class));
        }

        $executeAction = 'execute' . ucfirst($actionInstance->getActionName());
        if (method_exists($actionInstance, $executeAction)) {
            $refExecuteMethod = new ReflectionMethod($actionInstance::class, $executeAction);
            $attributes = array_merge($attributes, $refExecuteMethod->getAttributes(PostOnly::class));
        }

        return !empty($attributes);
    }

    private function forward404PostOnly(sfRequest $request, sfController $controller)
    {
        $request->setParameter('message', 'This action is only accessible via POST method');
        $controller->forward(sfConfig::get('sf_error_404_module'), sfConfig::get('sf_error_404_action'));
        throw new sfStopException();
    }
}

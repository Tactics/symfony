<?php

class sfMethodFilter extends sfFilter
{
    private readonly bool $isStrict;

    public function initialize($context, $parameters = [])
    {
        parent::initialize($context, $parameters);

        $this->isStrict = $parameters['is_strict'] ?? true;
    }

    public function execute($filterchain)
    {
        $context = $this->getContext();
        $request = $context->getRequest();
        $controller = $context->getController();
        $actionEntry = $controller->getActionStack()->getLastEntry();
        /** @var sfAction $actionInstance */
        $actionInstance = $actionEntry->getActionInstance();

        if (!$this->isStrict && $request->isXmlHttpRequest()) {
            $filterchain->execute();
            return;
        }

        $requiredMethod = $this->getRequiredMethod($actionInstance);
        if ($requiredMethod && $requiredMethod->value === sfRequest::NONE) {
            $filterchain->execute();
            return;
        }

        if ($requiredMethod && $request->getMethod() !== $requiredMethod->value) {
            $this->forward404Method($request, $controller, $requiredMethod);
        }

        $filterchain->execute();
    }

    /**
     * @throws ReflectionException
     */
    private function getRequiredMethod(sfAction $actionInstance): ?RequestMethod
    {
        $validateAction = 'validate' . ucfirst($actionInstance->getActionName());
        if (method_exists($actionInstance, $validateAction)) {
            $refValidateMethod = new ReflectionMethod($actionInstance::class, $validateAction);
            $attribute = $refValidateMethod->getAttributes(RequiredMethod::class);

            if (!empty($attribute)) {
                /** @var RequiredMethod $attributeInstance */
                $attributeInstance = $attribute[0]->newInstance();
                return $attributeInstance->getMethod();
            }

        }

        $executeAction = 'execute' . ucfirst($actionInstance->getActionName());
        if (method_exists($actionInstance, $executeAction)) {
            $refExecuteMethod = new ReflectionMethod($actionInstance::class, $executeAction);
            $attribute = $refExecuteMethod->getAttributes(RequiredMethod::class);

            if (!empty($attribute)) {
                /** @var RequiredMethod $attributeInstance */
                $attributeInstance = $attribute[0]->newInstance();
                return $attributeInstance->getMethod();
            }
        }

        return null;
    }

    private function forward404Method(sfRequest $request, sfController $controller, RequestMethod $method)
    {
        $request->setParameter('message', sprintf('This action is only accessible via %s method', $method->toString()));
        $controller->forward(sfConfig::get('sf_error_404_module'), sfConfig::get('sf_error_404_action'));
        throw new sfStopException();
    }
}

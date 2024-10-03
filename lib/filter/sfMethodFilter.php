<?php

declare(strict_types=1);

final class sfMethodFilter extends sfFilter
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
        $actionName = ucfirst($actionInstance->getActionName());

        foreach(['validate', 'execute'] as $actionType) {
            $requestMethod = $this->getMethodAttribute($actionInstance, $actionType . $actionName);
            if ($requestMethod) {
                return $requestMethod;
            }
        }

        return null;
    }

    /**
     * @throws ReflectionException
     */
    private function getMethodAttribute(sfAction $actionInstance, string $methodName): ?RequestMethod
    {
        if (method_exists($actionInstance, $methodName)) {
            $refMethod = new ReflectionMethod($actionInstance::class, $methodName);
            $attributes = $refMethod->getAttributes(RequiredMethod::class);

            if (!empty($attributes)) {
                /** @var RequiredMethod $attributeInstance */
                $attributeInstance = $attributes[0]->newInstance();
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

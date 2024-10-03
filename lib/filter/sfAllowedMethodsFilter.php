<?php

declare(strict_types=1);

final class sfAllowedMethodsFilter extends sfFilter
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

        if ($allowedMethods = $this->getAllowedMethods($actionInstance)) {
            try {
                $method = RequestMethod::from($request->getMethod());
                if (!in_array($method, $allowedMethods)) {
                    $this->forward404Method($request, $controller, $allowedMethods);
                }
            } catch (ValueError $e) {
                $this->forward404Method($request, $controller, $allowedMethods);
            }
        }

        $filterchain->execute();
    }

    /**
     * @param sfAction $actionInstance
     * @return RequestMethod[]|null
     * @throws ReflectionException
     */
    private function getAllowedMethods(sfAction $actionInstance): ?array
    {
        $actionName = ucfirst($actionInstance->getActionName());

        foreach (['validate', 'execute'] as $actionType) {
            $allowedMethods = $this->getAllowedMethodsAttribute($actionInstance, $actionType . $actionName);
            if ($allowedMethods) {
                return $allowedMethods;
            }
        }

        return null;
    }

    /**
     * @param sfAction $actionInstance
     * @param string $methodName
     *
     * @return RequestMethod[]|null
     * @throws ReflectionException
     */
    private function getAllowedMethodsAttribute(sfAction $actionInstance, string $methodName): ?array
    {
        if (method_exists($actionInstance, $methodName)) {
            $refMethod = new ReflectionMethod($actionInstance::class, $methodName);
            $attributes = $refMethod->getAttributes(AllowedMethods::class);

            if (!empty($attributes)) {
                /** @var AllowedMethods $attributeInstance */
                $attributeInstance = $attributes[0]->newInstance();
                return $attributeInstance->getMethods();
            }
        }

        return null;
    }

    /**
     * @param sfRequest $request
     * @param sfController $controller
     * @param RequestMethod[] $methods
     * @return mixed
     */
    private function forward404Method(sfRequest $request, sfController $controller, array $methods)
    {
        $stringMethods = array_map(static function (RequestMethod $method) {
            return $method->toString();
        }, $methods);

        $request->setParameter('message', sprintf('This action is only accessible via methods: %s.', implode(', ', $stringMethods)));
        $controller->forward(sfConfig::get('sf_error_404_module'), sfConfig::get('sf_error_404_action'));
        throw new sfStopException();
    }
}

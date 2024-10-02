<?php

class sfCsrfProtectionFilter extends sfFilter
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

        $actionIsCsrfProtected = $this->actionIsCsrfProtected($actionInstance);
        if (!$actionIsCsrfProtected) {
            // no CsrfProtected attribute but _name is in payload
            if ($request->hasParameter(sfCsrfTokenManager::SESSION_KEY_FIELD_NAME)) {
                if (!$this->hasValidToken($request)) {
                    // prevent forwarding loop if only _name is present or _name with an invalid token
                    $request->getParameterHolder()->remove(sfCsrfTokenManager::SESSION_KEY_FIELD_NAME);
                    $this->forward404MissingOrInvalidCsrfToken($request, $controller);
                }
            }
            // we end this filter here
            $filterchain->execute();
            // we just return from the filter on the way back
            return;
        }

        // Action is CSRF protected so there MUST be a valid token
        if (!$request->hasParameter(sfCsrfTokenManager::SESSION_KEY_FIELD_NAME)
            || !$request->hasParameter(sfCsrfTokenManager::TOKEN_FIELD_NAME)
            || !$this->hasValidToken($request)) {
            $this->forward404MissingOrInvalidCsrfToken($request, $controller);
        }

        $filterchain->execute();
    }

    /**
     * @param sfAction $actionInstance
     * @return bool
     * @throws ReflectionException
     */
    private function actionIsCsrfProtected(sfAction $actionInstance): bool
    {
        $attributes = [];
        $validateAction = 'validate' . ucfirst($actionInstance->getActionName());
        if (method_exists($actionInstance, $validateAction)) {
            $refValidateMethod = new ReflectionMethod($actionInstance::class, $validateAction);
            $attributes = array_merge($attributes, $refValidateMethod->getAttributes(CsrfProtected::class));
        }

        $executeAction = 'execute' . ucfirst($actionInstance->getActionName());
        if (method_exists($actionInstance, $executeAction)) {
            $refExecuteMethod = new ReflectionMethod($actionInstance::class, $executeAction);
            $attributes = array_merge($attributes, $refExecuteMethod->getAttributes(CsrfProtected::class));
        }

        return !empty($attributes);
    }

    /**
     * @param sfRequest $request
     * @param sfController $controller
     * @return mixed
     * @throws sfConfigurationException
     * @throws sfForwardException
     * @throws sfInitializationException
     * @throws sfStopException
     */
    private function forward404MissingOrInvalidCsrfToken(sfRequest $request, sfController $controller)
    {
        $request->setParameter('message', 'Invalid or missing CSRF token!');
        $controller->forward(sfConfig::get('sf_error_404_module'), sfConfig::get('sf_error_404_action'));
        throw new sfStopException();
    }

    /**
     * @param sfRequest $request
     * @return bool
     */
    private function hasValidToken(sfRequest $request): bool
    {
        $formName = $request->getParameter(sfCsrfTokenManager::SESSION_KEY_FIELD_NAME);
        $csrfManager = new sfCsrfTokenManager($formName);
        $token = $request->getParameter(sfCsrfTokenManager::TOKEN_FIELD_NAME);

        return $csrfManager->validateToken(sfCsrfTokenManager::TOKEN_FIELD_NAME, $token);
    }
}

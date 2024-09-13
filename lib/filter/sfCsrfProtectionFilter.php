<?php

class sfCsrfProtectionFilter extends sfFilter
{
    public function execute($filterchain)
    {
        $context = $this->getContext();
        $request = $context->getRequest();
        $controller = $context->getController();
        $actionEntry = $controller->getActionStack()->getLastEntry();
        $actionInstance = $actionEntry->getActionInstance();

        if ($request->hasParameter(sfCsrfTokenManager::SESSION_KEY_FIELD_NAME)) {
            $formName = $request->getParameter(sfCsrfTokenManager::SESSION_KEY_FIELD_NAME);
            $csrfManager = new sfCsrfTokenManager($formName);
            $token = $request->getParameter(sfCsrfTokenManager::TOKEN_FIELD_NAME);
            if (!$csrfManager->validateToken(sfCsrfTokenManager::TOKEN_FIELD_NAME, $token)) {
                $request->setError('csrf', 'Ongeldige CSRF token. Probeer het opnieuw.');

                $actionName = $context->getActionName();
                $handleErrorToRun = 'handleError'.ucfirst($actionName);
                $viewName = method_exists($actionInstance, $handleErrorToRun) ? $actionInstance->$handleErrorToRun() : $actionInstance->handleError();
                if ($viewName === '')
                {
                    $viewName = sfView::ERROR;
                }
            }
        }
        $filterchain->execute();
    }
}

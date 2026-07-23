<?php

/**
 * sfCsrfFilter validates the "_csrf_token" on every POST request.
 *
 * The filter is app-agnostic and lives in the tactics/symfony package so any
 * SF1 app can register it in its filters.yml without extra dependencies.
 *
 * Token algorithm: md5($secret . session_id()) where $secret comes from the
 * app config key "app_csrf_secret". The same algorithm is used by the
 * form_tag_for_csrf() helper so tokens always match for legitimate browser
 * submissions.
 *
 * Opt-out: add "csrf_enabled: off" under the action name or under "all:" in
 * the module's security.yml. The default (no entry) is ON.
 *
 * Enforcement: controlled by "app_csrf_enforce" (default false). In
 * report-only mode violations are logged but the request continues. In dev
 * environment a "warning" flash is also set. Set app_csrf_enforce to true to
 * block violating requests with a 403.
 *
 * Violations are logged to {sf_log_dir}/{sf_app}_{sf_environment}_csrf.log
 * using sfFileLogger — no external dependencies required.
 */
class sfCsrfFilter extends sfFilter
{
    const TOKEN_FIELD = '_csrf_token';

    /** @var sfFileLogger|null */
    private $fileLogger = null;

    /**
     * @param sfFilterChain $filterChain
     */
    public function execute(sfFilterChain $filterChain)
    {
        if ($this->isCsrfEnabled() && $this->isPostRequest()) {
            $submitted = $this->getSubmittedToken();
            $expected  = $this->getExpectedToken();

            if (!$this->isTokenValid($submitted, $expected)) {
                $this->handleViolation($submitted);
            }
        }

        $filterChain->execute();
    }

    /**
     * Pure token comparison — constant-time to prevent timing attacks.
     *
     * @param mixed  $submitted
     * @param string $expected
     * @return bool
     */
    public function isTokenValid($submitted, $expected)
    {
        if (!is_string($submitted) || $submitted === '') {
            return false;
        }

        return hash_equals($expected, $submitted);
    }

    /**
     * Log the violation and, in dev, set a warning flash.
     * In report-only mode the request is not blocked.
     *
     * @param mixed $submitted
     */
    protected function handleViolation($submitted)
    {
        $module = $this->getModuleName();
        $action = $this->getActionName();

        $message = sprintf(
            '{sfCsrfFilter} CSRF token validation failed for %s/%s (submitted: %s)',
            $module,
            $action,
            is_string($submitted) ? $submitted : '(none)'
        );

        $this->getFileLogger()->log($message, SF_LOG_WARNING, 'warning');

        if ($this->isDevEnvironment()) {
            $this->setWarningFlash(sprintf('CSRF token validation failed for %s/%s', $module, $action));
        }

        if (sfConfig::get('app_csrf_enforce', false)) {
            $this->getContext()->getController()->forward(sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));
            throw new sfStopException();
        }
    }

    /**
     * Returns true unless the action's security.yml explicitly sets
     * "csrf_enabled: off". Default (no entry) is ON.
     *
     * @return bool
     */
    protected function isCsrfEnabled()
    {
        // Never check CSRF on the secure action — mirrors sfBasicSecurityFilter's
        // own guard and prevents an infinite forward loop when enforcement is on.
        $context = $this->getContext();
        if (
            sfConfig::get('sf_secure_module') === $context->getModuleName() &&
            sfConfig::get('sf_secure_action') === $context->getActionName()
        ) {
            return false;
        }

        $security   = $this->getSecurityConfiguration();
        $actionName = strtolower($this->getActionName());

        if (isset($security[$actionName]['csrf_enabled'])) {
            return (bool) $security[$actionName]['csrf_enabled'];
        }

        if (isset($security['all']['csrf_enabled'])) {
            return (bool) $security['all']['csrf_enabled'];
        }

        return true;
    }

    /**
     * @return array
     */
    protected function getSecurityConfiguration()
    {
        $actionInstance = $this->getContext()
            ->getController()
            ->getActionStack()
            ->getLastEntry()
            ->getActionInstance();

        return (array) $actionInstance->getSecurityConfiguration();
    }

    /**
     * @return bool
     */
    protected function isPostRequest()
    {
        return $this->getContext()->getRequest()->getMethod() === sfRequest::POST;
    }

    /**
     * @return mixed
     */
    protected function getSubmittedToken()
    {
        return $this->getContext()->getRequest()->getParameter(self::TOKEN_FIELD);
    }

    /**
     * @return string
     */
    protected function getExpectedToken()
    {
        return md5(sfConfig::get('app_csrf_secret', '') . session_id());
    }

    /**
     * @return string
     */
    protected function getModuleName()
    {
        return $this->getContext()->getModuleName();
    }

    /**
     * @return string
     */
    protected function getActionName()
    {
        return $this->getContext()->getActionName();
    }

    /**
     * @return bool
     */
    protected function isDevEnvironment()
    {
        return sfConfig::get('sf_environment') === 'dev';
    }

    /**
     * @param string $message
     */
    protected function setWarningFlash($message)
    {
        $user = $this->getContext()->getUser();

        // sfUser in SF1 1.0 does not have setFlash(). Store the message as a
        // session attribute so the dev toolbar / template can pick it up.
        if (method_exists($user, 'setFlash')) {
            $user->setFlash('warning', $message);
        } else {
            $user->setAttribute('warning', $message, 'symfony/flash');
        }
    }

    /**
     * Returns a sfFileLogger writing to {sf_log_dir}/{sf_app}_{sf_environment}_csrf.log,
     * following the same naming convention as SF1's own log files.
     *
     * @return sfFileLogger
     */
    protected function getFileLogger()
    {
        if ($this->fileLogger === null) {
            $file = sprintf(
                '%s/%s_%s_csrf.log',
                sfConfig::get('sf_log_dir'),
                sfConfig::get('sf_app'),
                sfConfig::get('sf_environment')
            );

            $this->fileLogger = new sfFileLogger();
            $this->fileLogger->initialize(array('file' => $file));
        }

        return $this->fileLogger;
    }
}

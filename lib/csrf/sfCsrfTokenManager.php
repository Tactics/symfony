<?php

class sfCsrfTokenManager
{
    public const TOKEN_FIELD_NAME = '_token';
    public const SESSION_KEY_FIELD_NAME = '_name';

    private string $sessionKey;
    private sfCsrfTokenCollection $tokenCollection;
    private int $tokenTTL;
    private string $tokenFieldName;

    public function __construct(string $sessionKey, string $tokenFieldName = self::TOKEN_FIELD_NAME, int $tokenTTL = 0)
    {
        $this->sessionKey = $sessionKey;
        $this->tokenTTL = $tokenTTL;
        $this->tokenFieldName = $tokenFieldName;
        $this->loadTokens();
    }

    private function loadTokens(): void
    {
        $this->tokenCollection = new sfCsrfTokenCollection();
        if (isset($_SESSION[$this->sessionKey])) {
            /** @var sfCsrfTokenCollection $sessionTokens */
            $sessionTokens = unserialize($_SESSION[$this->sessionKey], ['allowed_classes' => [sfCsrfTokenCollection::class, sfCsrfToken::class]]);
            for ($i = $sessionTokens->count() - 1; $i >= 0; $i--) {
                if ($sessionTokens->getByIndex($i)?->isExpired()) {
                    break;
                }
                $this->tokenCollection->prependToken($sessionTokens->getByIndex($i));
            }
            if ($sessionTokens->count() !== $this->tokenCollection->count()) {
                $this->saveTokens();
            }
        }
    }

    private function saveTokens(): void
    {
        $_SESSION[$this->sessionKey] = serialize($this->tokenCollection);
    }

    public function generateToken(string $context, int $tokenTTL = -1, int $maxTokens = 5): string
    {
        $token = $this->createToken($context, $tokenTTL, $maxTokens);
        return htmlspecialchars($token->get(), ENT_QUOTES, 'UTF-8');
    }

    private function createToken(string $context, int $tokenTTL = -1, int $maxTokens = 5): sfCsrfToken
    {
        if ($tokenTTL < 0) {
            $tokenTTL = $this->tokenTTL;
        }
        $token = new sfCsrfToken($context, $tokenTTL);
        $this->tokenCollection->addToken($token);
        if ($this->clearTokens($context, $maxTokens) === 0) {
            $this->saveTokens();
        }

        return $token;
    }

    public function clearTokens(string $context, int $maxTokens = 0): int
    {
        $ignore = $maxTokens;
        $deleted = 0;
        for ($i = $this->tokenCollection->count() - 1; $i >= 0; $i--) {
            if ($ignore-- <= 0 && $this->tokenCollection->getByIndex($i)?->matchesContext($context)) {
                $this->tokenCollection->removeTokenByIndex($i);
                $deleted++;
            }
        }
        if ($deleted > 0) {
            $this->saveTokens();
        }

        return $deleted;
    }

    public function validateToken(string $context, string $token = null)
    {
        $token = $token ?? $_POST[$this->tokenFieldName] ?? $_GET[$this->tokenFieldName] ?? null;
        if ($token === null) {
            return false;
        }
        for ($i = $this->tokenCollection->count() - 1; $i >= 0; $i--) {
            if ($this->tokenCollection->getByIndex($i)?->verify($token, $context)) {
                $this->tokenCollection->removeTokenByIndex($i);
                return true;
            }
        }

        return false;
    }

}

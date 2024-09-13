<?php

class sfCsrfToken
{
    private string $token;
    private string $formContext;
    private int $expirtyTimestamp;

    public function __construct(string $formContext, int $timeToLive = 0)
    {
        $this->formContext = $formContext;
        $this->token = $this->generateToken();

        if ($timeToLive > 0) {
            $this->expirtyTimestamp = time() + $timeToLive;
        } else {
            $this->expirtyTimestamp = 0; // never expires
        }
    }

    private function generateToken(): string
    {
        $bytes = random_bytes(32);
        return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
    }

    public function verify(string $token, string $formContext): bool
    {
        return hash_equals($this->token, $token) && $this->formContext === $formContext && !$this->isExpired();
    }

    public function isExpired(): bool
    {
        return !($this->expirtyTimestamp === 0 || $this->expirtyTimestamp > time());
    }

    public function matchesContext(string $context): bool
    {
        return $context === $this->formContext;
    }

    public function get(): string
    {
        return $this->token;
    }
}

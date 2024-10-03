<?php

declare(strict_types=1);

final class sfCsrfTokenCollection
{
    private array $tokens;

    public function __construct()
    {
        $this->tokens = [];
    }

    public function addToken(sfCsrfToken $token): void
    {
        $this->tokens[] = $token;
    }

    public function findByContext(string $context): ?sfCsrfToken
    {
        foreach ($this->tokens as $token) {
            if ($token->matchesContext($context)) {
                return $token;
            }
        }

        return null;
    }

    public function getByIndex(int $index): ?sfCsrfToken
    {
        return $this->tokens[$index] ?? null;
    }

    public function removeTokenByIndex(int $index): void
    {
        array_splice($this->tokens, $index, 1);
    }

    public function count(): int
    {
        return count($this->tokens);
    }

    public function prependToken(sfCsrfToken $token): void
    {
        array_unshift($this->tokens, $token);
    }

}

<?php

enum RequestMethod: int
{
    case GET = 2;
    case POST = 4;
    case PUT = 5;
    case DELETE = 6;
    case HEAD = 7;

    public function toString(): string
    {
        return match ($this) {
            self::GET => 'GET',
            self::POST => 'POST',
            self::PUT => 'PUT',
            self::DELETE => 'DELETE',
            self::HEAD => 'HEAD',
        };
    }
}

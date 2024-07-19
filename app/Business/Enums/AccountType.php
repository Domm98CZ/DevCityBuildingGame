<?php declare(strict_types=1);
namespace App\Business\Enums;

enum AccountType
{
    case BASIC;
    case ADMIN;

    public function getName(): string
    {
        return match($this) {
            self::BASIC => 'basic',
            self::ADMIN => 'admin'
        };
    }
}

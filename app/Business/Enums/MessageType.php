<?php declare(strict_types=1);
namespace App\Business\Enums;

enum MessageType
{
    case SUCCESS;
    case INFO;
    case ERROR;
    case WARNING;

    public function getClass(): string
    {
        return match($this) {
            self::SUCCESS   => 'success',
            self::INFO      => 'info',
            self::ERROR     => 'danger',
            self::WARNING   => 'warning',
        };
    }
}

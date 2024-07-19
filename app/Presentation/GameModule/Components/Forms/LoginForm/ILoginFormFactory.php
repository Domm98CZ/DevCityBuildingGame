<?php declare(strict_types=1);
namespace App\Presentation\GameModule\Components\Forms\LoginForm;

interface ILoginFormFactory
{
    public function create(): LoginFormControl;
}

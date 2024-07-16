<?php declare(strict_types=1);
namespace App\Presentation\Presenters;

use Nette\Application\BadRequestException;
use Nette\Application\Request;
use Nette\Application\UI\Template;

class Error4xxPresenter extends BasePresenter
{
    /**
     * @throws BadRequestException
     */
    public function startup(): void
    {
        parent::startup();
        if ($this->getRequest() === null || !$this->getRequest()->isMethod(Request::FORWARD)) {
            $this->error();
        }
    }

    /**
     * @param BadRequestException $exception
     * @return void
     */
    public function renderDefault(BadRequestException $exception): void
    {
        // load template 403.latte or 404.latte or ... 4xx.latte
        $file = __DIR__ . "/templates/Error/{$exception->getCode()}.latte";
        $file = is_file($file) ? $file : __DIR__ . '/templates/Error/4xx.latte';
        if ($this->template instanceof Template) {
            $this->template->setFile($file);
        }
    }
}

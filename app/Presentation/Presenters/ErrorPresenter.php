<?php declare(strict_types=1);

namespace App\Presentation\Presenters;

use Nette\Application\BadRequestException;
use Nette\Application\Helpers;
use Nette\Application\Request;
use Nette\Application\Response;
use Nette\Application\Responses\ForwardResponse;
use Nette\Application\UI\Presenter;
use Nette\SmartObject;
use Tracy\ILogger;

/**
 * Class ErrorPresenter
 * @package App\Presentation\Presenters
 */
class ErrorPresenter extends Presenter
{
    use SmartObject;

    public function __construct(private readonly ILogger $logger)
    {
        parent::__construct();
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function run(Request $request): Response
    {
        $e = $request->getParameter('exception');

        if ($e instanceof BadRequestException) {
            // $this->logger->log("HTTP code {$e->getCode()}: {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}", 'access');
            [$module, , $sep] = Helpers::splitName($request->getPresenterName());
            $errorPresenter = $module . $sep . 'Error4xx';
            return new ForwardResponse($request->setPresenterName($errorPresenter));
        }

        $this->logger->log($e, ILogger::EXCEPTION);
        return new Responses\CallbackResponse(function (Http\IRequest $httpRequest, Http\IResponse $httpResponse): void {
            if (preg_match('#^text/html(?:;|$)#', (string) $httpResponse->getHeader('Content-Type'))) {
                require __DIR__ . '/templates/Error/500.phtml';
            }
        });
    }
}

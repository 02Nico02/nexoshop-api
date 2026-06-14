<?php

namespace App\EventSubscriber;

use App\Exception\ApiException;
use App\Service\ApiResponder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly ApiResponder $apiResponder)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        if (!str_starts_with($request->getPathInfo(), '/api/')) {
            return;
        }

        $throwable = $event->getThrowable();

        if ($throwable instanceof ApiException) {
            $event->setResponse($this->apiResponder->error(
                $throwable->getErrorCode(),
                $throwable->getMessage(),
                $throwable->getStatusCode(),
                $throwable->getDetails()
            ));

            return;
        }

        if ($throwable instanceof HttpExceptionInterface) {
            $event->setResponse($this->apiResponder->error(
                'HTTP_ERROR',
                $throwable->getMessage() ?: 'Error de la API',
                $throwable->getStatusCode()
            ));

            return;
        }

        $event->setResponse(new JsonResponse([
            'error' => [
                'code' => 'INTERNAL_SERVER_ERROR',
                'message' => 'No se pudo procesar la solicitud',
            ],
        ], 500));
    }
}

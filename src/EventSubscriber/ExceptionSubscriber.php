<?php

namespace App\EventSubscriber;

use App\Service\CustomException;
use App\Service\CustomSerializerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly CustomSerializerInterface $serializer
    )
    {
        
    }
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof CustomException) {
            $statusCode = $exception->getStatusCode();
            $data = $exception->getMessage();
        }
        else {
            $data = $this->serializer->serializeErrors(['message' => $exception->getMessage()]);
            if ($exception instanceof HttpException) {
                $statusCode = $exception->getStatusCode();
            } else {
                $statusCode = 500;
            }
        }

        $event->setResponse(new JsonResponse($data, $statusCode, [], true));
   }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}
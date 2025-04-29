<?php

namespace App\EventSubscriber;

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

        if ($exception instanceof HttpException) {
            if ($exception->getStatusCode() == 404) {
                $event->setResponse(new JsonResponse($this->serializer->serializeErrors(['message' => 'Invalid url']), $exception->getStatusCode(), [], true));
            }
            else {
                $event->setResponse(new JsonResponse($exception->getMessage(), $exception->getStatusCode(), [], true));
            }
      } else {
            $event->setResponse(new JsonResponse($this->serializer->serializeErrors(['message' => $exception->getMessage()], 'json'), 500, [], true));
      }
   }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}
<?php
namespace App\EventListener;

//use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
//use Symfony\Component\HttpFoundation\Response;
//use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

class JsonUnicodePrintListener
{
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();
        if ($response instanceof JsonResponse) {
            $response->setEncodingOptions(
                $response->getEncodingOptions() | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            );
        }
    }
}
?>

<?php

namespace spec\Gamma\ApiLoggerBundle\Listener;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Gamma\ApiLoggerBundle\Service\LoggerStopwatch;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ParameterBag;

class TrackDurationListenerSpec extends ObjectBehavior
{
    const API_URI = '/api/test';
    const FORNTEND_URI = '/home';

    public function it_is_initializable()
    {
        $this->shouldHaveType('Gamma\ApiLoggerBundle\Listener\TrackDurationListener');
    }

    public function let(LoggerStopwatch $loggerStopwatch,
                 Request $request,
                 ParameterBag $bag
    ) {
        //$request->getWrappedObject()->request = $bag;
        $this->beConstructedWith($loggerStopwatch);
    }

    public function it_should_start_logger_on_kernel_request(
        LoggerStopwatch $loggerStopwatch,
        GetResponseEvent $event,
        Request $request,
        ParameterBag $bag
    ) {
        $request->getRequestUri()->willReturn(self::API_URI);
        $request->getMethod()->willReturn('GET');
        $request->getMethod()->shouldBeCalled();
        $request->getContent()->willReturn('RequestContentHere');
        $request->getContent()->ShouldBeCalled();
        $request->getRequestUri()->willReturn(self::API_URI);
        $bag->all()->willReturn(['formData' => 1111]);
        $request->getWrappedObject()->request = $bag->getWrappedObject();

        $event->isMasterRequest()->willReturn(true);
        $event->getRequest()->willReturn($request);

        $loggerStopwatch->start(Argument::any())->shouldBeCalled();
        $this->onKernelRequest($event);
    }

    public function it_should_not_start_logger_for_subrequests_on_kernel_request(
        LoggerStopwatch $loggerStopwatch,
        GetResponseEvent $event,
        Request $request
    ) {
        $event->isMasterRequest()->willReturn(false);
        $loggerStopwatch->start(Argument::any())->shouldNotBeCalled();
        $this->onKernelRequest($event);
    }

    public function it_should_not_start_logger_for_non_api_urls_on_kernel_request(
        LoggerStopwatch $loggerStopwatch,
        GetResponseEvent $event,
        Request $request
    ) {
        $request->getRequestUri()->willReturn(self::FORNTEND_URI);
        $request->getMethod()->willReturn('GET');

        $event->isMasterRequest()->willReturn(true);
        $event->getRequest()->willReturn($request);

        $loggerStopwatch->start(Argument::any())->shouldNotBeCalled();
        $this->onKernelRequest($event);
    }

    public function it_should_stop_logger_on_kernel_response(
        LoggerStopwatch $loggerStopwatch,
        FilterResponseEvent $event,
        Response $response
    ) {
        $response->getContent()->willReturn('TestResponseContent');
        $response->getContent()->shouldBeCalled();
        $response->getStatusCode()->willReturn(200);
        $response->getStatusCode()->shouldBeCalled();
        $event->isMasterRequest()->willReturn(true);
        $event->getResponse()->willReturn($response);

        $this->setUri(self::API_URI);

        $loggerStopwatch->stop(self::API_URI, Argument::any())->shouldBeCalled();
        $this->onKernelResponse($event);
    }
}

<?php

namespace App\Handler\Unsubscribe;

use App\UserService;
use Laminas\Diactoros\Response\XmlResponse;
use Laminas\InputFilter\InputFilterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Twilio\TwiML\MessagingResponse;

class UnsubscribeByMobileHandler
{
    public const RESPONSE_MESSAGE_SUCCESSFULLY_UNSUBSCRIBED = <<<EOF
You are now unsubscribed from the daily developer quotes service. 
To resubscribe, send another SMS to this number with the text: SUBSCRIBE.
EOF;

    public const RESPONSE_MESSAGE_INVALID_MOBILE_NUMBER = 'Mobile number must be in E.164 format. More information is available at https://www.twilio.com/docs/glossary/what-e164.';

    private InputFilterInterface $inputFilter;
    private UserService $userService;

    public function __construct(UserService $userService, InputFilterInterface $inputFilter)
    {
        $this->inputFilter = $inputFilter;
        $this->userService = $userService;
    }

    public function handle(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $params = $request->getParsedBody();
        $twiml = new MessagingResponse();

        $this->inputFilter->setData([
            'mobileNumber' => $params['From'],
        ]);
        if ($this->inputFilter->isValid()) {
            $this->userService->removeByMobileNumber($this->inputFilter->getValue('mobileNumber'));
            $twiml->message(self::RESPONSE_MESSAGE_SUCCESSFULLY_UNSUBSCRIBED);
            return new XmlResponse($twiml->asXML());
        }

        $twiml->message(self::RESPONSE_MESSAGE_INVALID_MOBILE_NUMBER);
        return new XmlResponse($twiml->asXML());
    }
}
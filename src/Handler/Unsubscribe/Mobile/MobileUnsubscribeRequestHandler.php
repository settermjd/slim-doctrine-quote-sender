<?php

namespace App\Handler\Unsubscribe\Mobile;

use App\InputFilter\MobileInputTrait;
use App\Service\UserService;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\XmlResponse;
use Laminas\InputFilter\InputFilterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Twilio\TwiML\MessagingResponse;

class MobileUnsubscribeRequestHandler
{
    use MobileInputTrait;

    /**
     * The list of the keywords that a user can use to unsubscribe
     *
     * @var array<int,string>
     */
    public const KEYWORDS = [
        'cancel',
        'end',
        'quit',
        'stop',
        'stopall',
        'unsubscribe',
    ];

    public function __construct(
        private readonly UserService $userService,
        private readonly InputFilterInterface $inputFilter
    ) {}

    public function handle(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $params = $request->getParsedBody();
        $twiml = new MessagingResponse();

        $this->inputFilter->setData([
            'mobileNumber' => $params['From'],
        ]);
        if ($this->inputFilter->isValid()) {
            $this->userService->removeByMobileNumber($this->inputFilter->getValue('mobileNumber'));
            return new EmptyResponse();
        }

        $twiml->message(self::RESPONSE_MESSAGE_INVALID_MOBILE_NUMBER);
        return new XmlResponse($twiml->asXML());
    }
}
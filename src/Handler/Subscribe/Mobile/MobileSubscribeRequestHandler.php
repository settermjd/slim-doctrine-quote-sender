<?php

declare(strict_types=1);

namespace App\Handler\Subscribe\Mobile;

use App\InputFilter\MobileInputTrait;
use App\Service\UserService;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\XmlResponse;
use Laminas\InputFilter\InputFilterInterface;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Twilio\TwiML\MessagingResponse;

/**
 * This class subscribes a user to the service using their mobile number
 */
class MobileSubscribeRequestHandler
{
    use MobileInputTrait;

    /**
     * The list of the keywords that a user can use to subscribe
     *
     * @var array<int,string>
     */
    public const KEYWORDS = [
        'subscribe',
        'unstop',
        'yes',
    ];

    public const REGEX_MOBILE_NUMBER = '^\+[1-9]\d{1,14}$';
    public const RESPONSE_MESSAGE_SUCCESSFULLY_SUBSCRIBED = <<<EOF
You are now subscribed to the daily developer quotes service. 
To unsubscribe, send another SMS to this number with the text: UNSUBSCRIBE
EOF;

    public function __construct(
        private readonly UserService $userService,
        private readonly InputFilterInterface $inputFilter
    ) {
    }

    public function handle(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $params = $request->getParsedBody();
        $twiml = new MessagingResponse();


        if (is_null($params['From'])
            || ! $this
                ->inputFilter
                ->setData(['mobileNumber' => $params['From']])
                ->isValid()
        ) {
            $twiml->message(self::RESPONSE_MESSAGE_INVALID_MOBILE_NUMBER);
            return new XmlResponse($twiml->asXML());
        }

        $this->userService->createWithMobileNumber($params['From']);
        return new EmptyResponse();
    }
}
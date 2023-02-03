<?php

namespace App\Handler;

trait EmailHandlerTrait
{
    public const RESPONSE_MESSAGE_FAIL_INVALID_EMAIL = 'The email address provided is not a valid email address.';
    public const RESPONSE_MESSAGE_SUBSCRIBE_SUCCESS = 'You were successfully subscribed.';
    public const RESPONSE_MESSAGE_UNSUBSCRIBE_SUCCESS = 'You were successfully unsubscribed.';
}
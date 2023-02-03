<?php

namespace App\Handler;

trait EmailHandlerTrait
{
    public const RESPONSE_MESSAGE_UNSUBSCRIBE_SUCCESS = 'You were successfully unsubscribed.';
    public const RESPONSE_MESSAGE_FAIL_INVALID_EMAIL = 'The email address provided is not a valid email address.';
}
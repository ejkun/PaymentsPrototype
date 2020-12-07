<?php

namespace App\Exceptions;

use Throwable;

class NotificationNotSent extends \Exception
{
    public function __construct()
    {
        parent::__construct("Notification not sent");
    }
}

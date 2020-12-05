<?php


namespace App\Exceptions;


use Illuminate\Http\Response;
use Throwable;

class InvalidPayerType extends \Exception
{
    public function __construct()
    {
        parent::__construct("Invalid Payer Type", Response::HTTP_BAD_REQUEST);
    }
}

<?php
namespace CakeQueue\Exception;

use Exception;

class FailedJobException extends Exception
{
    protected $message = 'Job run failed';
}

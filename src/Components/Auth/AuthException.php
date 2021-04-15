<?php
namespace Porabote\Components\Auth;

class AuthException extends \Exception
{

    public function toJSON()
    {
        header('Content-Type: application/json; charset=UTF-8');
        die(json_encode(['error' => $this->getMessage()]));
    }

}
?>
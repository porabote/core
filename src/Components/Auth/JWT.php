<?php
namespace Porabote\Components\Auth;

use Porabote\Http\Exception\UnauthorizedException;
use \Porabote\Components\Auth\AuthException;
use \Firebase\JWT\JWT as FirebaseJWT;

class JWT
{

    /**
     * Config.
     *
     * @var array|null
     */
    static protected $_config;

    /**
     * Private key for generating hash signature.
     *
     * @var string|null
     */
    static private $_privateKey = API_PRIVATE_KEY;

    /**
     * JWT access token.
     *
     * @var string|null
     */
    static protected $_accessToken;

    /**
     * JWT refresh token.
     *
     * @var string|null
     */
    static protected $_refreshToken;

    /**
     * User data.
     *
     * @var array|null
     */
    static protected $_user;

    /**
     * Header data.
     *
     * @var array|null
     */
    static protected $_header;

    /**
     * Payload data.
     *
     * @var object|null
     */
    static protected $_payload;

    /**
     * Exception.
     *
     * @var \Exception
     */
    static protected $_error;



    /**
     * Constructor.
     *
     * Settings for this object.
     *
     * - `cookie` - Cookie name to check. Defaults to `false`.
     * - `header` - Header name to check. Defaults to `'authorization'`.
     */
    public function __construct($config = [])//ComponentRegistry $registry, $config
    {

    }


    /**
     * Checking JWT.
     *
     * @param $user User record array.
     *
     * @return bool true or false on failure.
     */
    static public function checkToken($requestToken)
    {

        //todo check
        return($requestToken);

        http_response_code(403);

//        if(!$user) throw new AuthException('Payload is empty');
//        self::_setConfig($config);
//        self::_setUser($user);
//        self::_setHeader();
//        self::_setPayload($user);
//
//        $token = [
//            'header' => self::$_header,
//            'payload' => self::$_payload,
//            'signature' => FirebaseJWT::encode(self::$_payload, self::$_privateKey, self::$_config['alg'])
//        ];
//        debug($token);

    }

    /**
     * Generate JWT.
     *
     * @param $user User record array.
     *
     * @return bool|array Jwt token string or false on failure.
     */
    static public function setToken($user = [], $config = [])
    {
        self::_setConfig($config);
        self::_setUser($user);
        self::_setHeader();
        self::_setPayload($user);

        return [
            'access_token' => FirebaseJWT::encode(self::$_payload, self::$_privateKey, self::$_config['alg']),
            'refresh_token' => null
        ];
//        return [
//            'header' => self::$_header,
//            'payload' => self::$_payload,
//            'signature' => FirebaseJWT::encode(self::$_payload, self::$_privateKey, self::$_config['alg'])
//        ];

    }

    private static function _setConfig($config)
    {
        self::$_config = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];

        self::$_config = array_merge(self::$_config, $config);
    }

    private static function _setUser($user)
    {
        self::$_user = $user;
    }

    static private function _setHeader()
    {
        self::$_header = [
            'typ' => self::$_config['typ'],
            'alg' => self::$_config['alg'] ,
        ];
    }

    static private function _setPayload($user)
    {
        $issuedAt = time();
        $expirationTime = $issuedAt + 600; // jwt valid for 600 seconds from the issued time

        self::$_payload = [
            'id' => self::$_user['id'],
            'username' => self::$_user['username'],
            'name' => self::$_user['last_name'] . ' ' .self::$_user['name'],
            'account_alias' => self::$_user['account_alias'],
            'iat' => $issuedAt,
            'exp' => $expirationTime
        ];
    }
    /**
     * Generate JWT.
     *
     * @param $user User record array.
     *
     * @return bool|array Jwt token string or false on failure.
     */
    public function authenticate($user = [])
    {
        debug($user);
    }


    /*
     * Set two new tokens - acces and refresh (every 15 min)
     *
     */
    function refresh()
    {

    }

    /**
     * Decode JWT token.
     *
     * @param string $token JWT token to decode.
     *
     * @return object|null The JWT's payload as a PHP object, null on failure.
     */
    protected function _decode($token)
    {
        $config = $this->_config;
        try {
            $payload = JWT::decode(
                $token,
                $config['key'] ?: Security::getSalt(),
                $config['allowedAlgs']
            );

            return $payload;
        } catch (Exception $e) {
            if (Configure::read('debug')) {
                throw $e;
            }
            $this->_error = $e;
        }
    }

    /**
     * Handles an unauthenticated access attempt. Depending on value of config
     * `unauthenticatedException` either throws the specified exception or returns
     * null.
     *
     * @param \Cake\Http\ServerRequest $request A request object.
     * @param \Cake\Http\Response $response A response object.
     *
     * @throws \Cake\Http\Exception\UnauthorizedException Or any other
     *   configured exception.
     *
     * @return void
     */
    public function unauthenticated(ServerRequest $request, Response $response)
    {
        if (!$this->_config['unauthenticatedException']) {
            return;
        }

        $message = $this->_error
            ? $this->_error->getMessage()
            : $this->_registry->get('Auth')->getConfig('authError');

        $exception = new $this->_config['unauthenticatedException']($message);
        throw $exception;
    }

}

?>
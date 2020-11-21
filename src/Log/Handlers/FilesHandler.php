<?php
namespace Porabote\Log\Handlers;

use PDO;
use Log\Route;
use Porabote\Log\ChainResponsibility;

/**
 * Class DatabaseRoute
 *
 * Создание таблицы:
 *
 * CREATE TABLE default_log (
 *      id integer PRIMARY KEY,
 *      date date,
 *      level varchar(16),
 *      message text,
 *      context text
 * );
 */
class FilesHandler extends ChainResponsibility
{
    /**
     * @var string Data Source Name
     * @see http://php.net/manual/en/pdo.construct.php
     */
    public $dsn;
    /**
     * @var string Имя пользователя БД
     */
    public $username;
    /**
     * @var string Пароль пользователя БД
     */
    public $password;
    /**
     * @var string Имя таблицы
     */
    public $table;

    /**
     * @var PDO Подключение к БД
     */
    private $connection;

    /**
     * @inheritdoc
     */
    public function __construct()
    {
        //parent::__construct($attributes);
        //$this->connection = new PDO($this->dsn, $this->username, $this->password);
    }

    /**
     * @inheritdoc
     */
    public function log($level, $message, array $context = [])
    {
        if (parent::$_handlerAlias == 'Files') {
            echo __CLASS__ . "process this message";
        }
        else {
            if ($this->getNext()) {
                $this->getNext()->log($level, $message, $context);
            }
        }
/*
        $statement = $this->connection->prepare(
            'INSERT INTO ' . $this->table . ' (date, level, message, context) ' .
            'VALUES (:date, :level, :message, :context)'
        );
        $statement->bindParam(':date', $this->getDate());
        $statement->bindParam(':level', $level);
        $statement->bindParam(':message', $message);
        $statement->bindParam(':context', $this->contextStringify($context));
        $statement->execute();
*/
    }

    
    
    public static function write($level, $message, $context = [])
    {
/*
        static::_init();
        if (is_int($level) && in_array($level, static::$_levelMap)) {
            $level = array_search($level, static::$_levelMap);
        }

        if (!in_array($level, static::$_levels)) {
            throw new InvalidArgumentException(sprintf('Invalid log level "%s"', $level));
        }

        $logged = false;
        $context = (array)$context;
        if (isset($context[0])) {
            $context = ['scope' => $context];
        }
        $context += ['scope' => []];

        foreach (static::$_registry->loaded() as $streamName) {
            $logger = static::$_registry->{$streamName};
            $levels = $scopes = null;

            if ($logger instanceof BaseLog) {
                $levels = $logger->levels();
                $scopes = $logger->scopes();
            }
            if ($scopes === null) {
                $scopes = [];
            }

            $correctLevel = empty($levels) || in_array($level, $levels);
            $inScope = $scopes === false && empty($context['scope']) || $scopes === [] ||
                is_array($scopes) && array_intersect((array)$context['scope'], $scopes);

            if ($correctLevel && $inScope) {
                $logger->log($level, $message, $context);
                $logged = true;
            }
        }

        return $logged;
*/
    }

    /**
     * Convenience method to log emergency messages
     *
     * @param string $message log message
     * @param string|array $context Additional data to be used for logging the message.
     *  The special `scope` key can be passed to be used for further filtering of the
     *  log engines to be used. If a string or a numerically index array is passed, it
     *  will be treated as the `scope` key.
     *  See Cake\Log\Log::setConfig() for more information on logging scopes.
     * @return bool Success
     */
    public function emergency($message, $context = [])
    {
        return static::write(__FUNCTION__, $message, $context);
    }

    /**
     * Convenience method to log alert messages
     *
     * @param string $message log message
     * @param string|array $context Additional data to be used for logging the message.
     *  The special `scope` key can be passed to be used for further filtering of the
     *  log engines to be used. If a string or a numerically index array is passed, it
     *  will be treated as the `scope` key.
     *  See Cake\Log\Log::setConfig() for more information on logging scopes.
     * @return bool Success
     */
    public function alert($message, $context = [])
    {
        return static::write(__FUNCTION__, $message, $context);
    }

    /**
     * Convenience method to log critical messages
     *
     * @param string $message log message
     * @param string|array $context Additional data to be used for logging the message.
     *  The special `scope` key can be passed to be used for further filtering of the
     *  log engines to be used. If a string or a numerically index array is passed, it
     *  will be treated as the `scope` key.
     *  See Cake\Log\Log::setConfig() for more information on logging scopes.
     * @return bool Success
     */
    public function critical($message, $context = [])
    {
        return static::write(__FUNCTION__, $message, $context);
    }

    /**
     * Convenience method to log error messages
     *
     * @param string $message log message
     * @param string|array $context Additional data to be used for logging the message.
     *  The special `scope` key can be passed to be used for further filtering of the
     *  log engines to be used. If a string or a numerically index array is passed, it
     *  will be treated as the `scope` key.
     *  See Cake\Log\Log::setConfig() for more information on logging scopes.
     * @return bool Success
     */
    public function error($message, $context = [])
    {
        return static::write(__FUNCTION__, $message, $context);
    }

    /**
     * Convenience method to log warning messages
     *
     * @param string $message log message
     * @param string|array $context Additional data to be used for logging the message.
     *  The special `scope` key can be passed to be used for further filtering of the
     *  log engines to be used. If a string or a numerically index array is passed, it
     *  will be treated as the `scope` key.
     *  See Cake\Log\Log::setConfig() for more information on logging scopes.
     * @return bool Success
     */
    public function warning($message, $context = [])
    {
        return static::write(__FUNCTION__, $message, $context);
    }

    /**
     * Convenience method to log notice messages
     *
     * @param string $message log message
     * @param string|array $context Additional data to be used for logging the message.
     *  The special `scope` key can be passed to be used for further filtering of the
     *  log engines to be used. If a string or a numerically index array is passed, it
     *  will be treated as the `scope` key.
     *  See Cake\Log\Log::setConfig() for more information on logging scopes.
     * @return bool Success
     */
    public function notice($message, $context = [])
    {
        return static::write(__FUNCTION__, $message, $context);
    }

    /**
     * Convenience method to log debug messages
     *
     * @param string $message log message
     * @param string|array $context Additional data to be used for logging the message.
     *  The special `scope` key can be passed to be used for further filtering of the
     *  log engines to be used. If a string or a numerically index array is passed, it
     *  will be treated as the `scope` key.
     *  See Cake\Log\Log::setConfig() for more information on logging scopes.
     * @return bool Success
     */
    public function debug($message, $context = [])
    {
        return static::write(__FUNCTION__, $message, $context);
    }

    /**
     * Convenience method to log info messages
     *
     * @param string $message log message
     * @param string|array $context Additional data to be used for logging the message.
     *  The special `scope` key can be passed to be used for further filtering of the
     *  log engines to be used. If a string or a numerically index array is passed, it
     *  will be treated as the `scope` key.
     *  See Cake\Log\Log::setConfig() for more information on logging scopes.
     * @return bool Success
     */
    public function info($message, $context = [])
    {
        return static::write(__FUNCTION__, $message, $context);
    }    
    


}
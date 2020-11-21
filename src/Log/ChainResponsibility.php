<?php
namespace Porabote\Log;

use DateTime;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

/**
 * Class Route
 */
abstract class ChainResponsibility extends AbstractLogger implements LoggerInterface
{

    protected static $_handlerAlias;
    
    /**
     * @var string Формат даты логов
     */
    private $dateFormat = DateTime::RFC2822;

    /**
     * @var AbstractHandler
     */
    protected $_next;


    /**
     * Send request by
     *
     * @param mixed $message
     */
    abstract public function log($level, $message, array $context = []);
    /**
     * @param \AbstractHandler $next
     */
    public function setNext($next)
    {
        $this->_next = $next;
    }
    /**
     * @return \AbstractHandler
     */
    public function getNext()
    {
        return $this->_next;
    }


    /**
     * Текущая дата
     *
     * @return string
     */
    public function getDate()
    {
        return (new DateTime())->format($this->dateFormat);
    }

    /**
     * Преобразование $context в строку
     *
     * @param array $context
     * @return string
     */
    public function contextStringify(array $context = [])
    {
        return !empty($context) ? json_encode($context) : null;
    }
}

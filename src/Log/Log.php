<?php
namespace Porabote\Log;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Porabote\Log\ChainResponsibility;
use Porabote\Log\Handlers\DatabaseHandler;
use Porabote\Log\Handlers\FilesHandler;

/**
 * Class Logger
 */
class Log extends ChainResponsibility
{
    /**
     * @var SplObjectStorage Список роутов
     */
    public static $_handler;
    public static $_handlerAlias;

    private static $config = [
	    
    ];
    /**
     * Handled log levels
     *
     * @var array
     */
    protected static $_levels = [
        'emergency',
        'alert',
        'critical',
        'error',
        'warning',
        'notice',
        'info',
        'debug'
    ];

    /**
     * Log levels as detailed in RFC 5424
     * https://tools.ietf.org/html/rfc5424
     *
     * @var array
     */
    protected static $_levelMap = [
        'emergency' => 0,
        'alert' => 1,
        'critical' => 2,
        'error' => 3,
        'warning' => 4,
        'notice' => 5,
        'info' => 6,
        'debug' => 7,
    ];


    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::$_handlerAlias = 'Database';
        
        self::$_handler = new DatabaseHandler(self::$_handlerAlias);
        self::$_handler->setNext(new FilesHandler(self::$_handlerAlias));
    }

    /**
     * @inheritdoc
     */
    public function log($level, $message, array $context = [])
    {
	    $message = $this->interpolate($message, $context);//debug($message);
	    
        self::$_handler->log($level, $message, $context);
    }
    
    private function interpolate($message, $context)
    {
        $loader = new \Twig_Loader_Array( [
            '_defaultConfig.templates.recordPattern' => $message,
        ]);	
        $this->twig = new \Twig_Environment($loader, ['cache' => false]);

		return $this->twig->render('_defaultConfig.templates.recordPattern', $context);

    }

    public function setHandler($handlerAlias)
    {
	    parent::$_handlerAlias = $handlerAlias;
    }    
    
    
    
}

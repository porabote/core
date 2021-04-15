<?
namespace Porabote\Collection;

use Cake\ORM\TableRegistry;
use App\Component\MailComponent as Mail;


class BusinessSchemaHandler implements \IteratorAggregate
{
    public $schema;
    public $recordData = [];
    public $isFinish;
    public $props = [];
    public $currentKey = 0;
    private $collection;
    private $schemaType;
    private $currentStep;
    private $nextStep;
//    private $items;
    private $mail;
//    private $statuses;
//    public $status;
//    private $reverse = false;
    private $acceptor;
//    public $iterationEnded = false;

    function __construct($schema = null, $props = [])
    {
        $propsDefault = [
            'autoSend' => true
        ];
        $this->props = array_merge($propsDefault, $props);

        $this->schema = $schema;
        $this->mail = new \App\Component\MailComponent();
        if(isset($schema['steps'])) $this->setCollection($schema['steps'], 'accepted');
    }

    function getCurrentKey()
    {
        if(isset($this->schema['steps'])) {
            foreach ($this->schema['steps'] as $key => $item) {
                if (!$item['params']['accepted']) return $key;
            }
        }
        return null;
    }

    function setItem($item)
    {
        $this->collection[$this->currentKey] = array_merge($this->schema['step_pattern']['params'], $item);
        $this->currentKey++;
    }

    function setRecordData($recordData)
    {
        $this->recordData = array_merge($this->recordData, $recordData);
    }

    function doStep()
    {
        $this->isFinish = true;
        foreach($this->schema['steps'] as $key => &$item) {

            // Если это первый шаг или напоминание
            if(!$this->acceptor && !$item['params']['accepted']) {

                $this->currentStep = $item;
                $this->nextStep = $item;
                $this->_sendNotices();
                $this->isFinish = false;
                break;

            } else if($this->acceptor && !$item['params']['accepted']) {

                $this->isFinish = false;
                $this->currentStep = $item;
                $this->nextStep = $this->getNextStep($key);

                if($this->checkAccess($item['params'])) {
                    $item['params']['accepted'] = 1;
                    $item['params']['accepted_datetime'] = date("Y-m-d H:i:s");
                    if($this->nextStep) {
                        $this->schema['state'] = 'process';
                        $this->_sendNotices();
                    } else {
                        $this->schema['state'] = 'accepted';
                        $this->_sendNotices();
                        $this->isFinish = true;
                    }
                }
                break;
            }

        }


    }

    function _sendNotices()
    {
        if(!$this->props['autoSend']) return;
        $this->sendNotices();

    }

    function sendNotices()
    {
        foreach($this->schema['notices'][$this->schema['state']] as $event) {

            $recipients = $this->getRecipients($event);

            $mailPattern = TableRegistry::get('MailsPatterns')->get($event['mails_pattern_id']);

            $recordData = [];
            $session = new \Cake\Http\Session();
            $recordData['system']['alias'] = $session->read('account_alias');

            $this->mail->_setOptions([
                'setFrom' => 'noreply@' .DOMAIN,
                'setFrom_name' => SITE_NAME,
                'subject' => $this->__fillTwigPattern($mailPattern['mail_title'], $this->recordData),
                'recipients' => $recipients,
                'body' => $this->__fillTwigPattern($mailPattern['mail_body'], $this->recordData)
            ]);
            $this->mail->send();

        }
    }

    private function getRecipients($event)
    {
        $recipientsEmailsList = [];
        foreach($event['recipients'] as $key => $recipient) {

            if($key === "where") {

                $recipients = TableRegistry::get('Posts')->find('list', [
                    'keyField' => 'id',
                    'valueField' => 'email'
                ])
                ->where($recipient)
                ->toArray();
                $recipientsEmailsList = array_merge($recipientsEmailsList, $recipients);
                continue;
            }

            switch($recipient) {
                case 'NextAcceptor' :
                    $recipientsEmailsList[] = $this->nextStep['params']['email'];
                    break;
                case 'Initator' :
                    if(isset($this->schema['initator'])) {
                        $recipientsEmailsList[] = $this->schema['initator']['email'];
                    }
                    break;
                case 'Shiftworkers' :
                    if(isset($this->nextStep['params']['shiftworkers'])) {
                        foreach ($this->nextStep['params']['shiftworkers'] as $shiftworker) {
                            $recipientsEmailsList[] = $shiftworker['email'];
                        }
                    }
                    break;
            }
        }

        return $recipientsEmailsList;
    }
    
    function __fillTwigPattern($pattern, &$data)
    {
        if($pattern) {
            $loader = new \Twig_Loader_Array( [
                'part_alias' => $pattern,
            ]);
            $twig = new \Twig_Environment($loader);
            $twig->addExtension(new \Twig_Extensions_Extension_Intl());
            $twig->getExtension('Twig_Extension_Core')->setTimezone('Europe/Moscow');

            return $twig->render('part_alias', $data);
        }

        return null;
    }

    function getNextStep($currentKey)
    {
        $preBreak = false;
        foreach($this->schema['steps'] as $key => $item) {

            if($preBreak) return $item;
            if($key == $currentKey) $preBreak = true;
        }

        return null;
    }

    function checkAccess($step = null)
    {
        if(!$step && isset($this->schema['steps'])) {
            foreach($this->schema['steps'] as $key => $item) {
                if(!$item['params']['accepted']) {
                    $step = $item['params'];
                    break;
                }
            }
        }

        if(!$this->acceptor || !$step || !isset($step['post_id'])) return false;

        if($this->acceptor['id'] == $step['post_id']) {
            return $step['post_id'];
        }

        if(!$step['shiftworkers']) return false;

        foreach($step['shiftworkers'] as $worker) {
            if($this->acceptor['id'] == $worker['id']) {
                return $worker['id'];
            }
        }

        return false;
    }

    function buildSteps($steps, $schema)
    {
        $i = 0;
        $stepsList = [];
        if(!$steps) {
            $steps = [];
            // Если нет шагов, то схема считается акцептованной
            $this->schema['state'] = 'accepted';
        }

        foreach($steps as $step) {

            // Ищем и записываем сменщиков
            $post = TableRegistry::get('Posts')->get($step['post_id'], [
                'contain' => [
                    'Shiftworkers' => ['Departments'],
                    'Departments'
                ]
            ]);
            $step['post_name'] = $post['fio'] . ' (' . $post['name'] . ')';
            $step['email'] = $post['email'];
            $step['department_id'] = $post['department']['id'];
            $step['department_name'] = $post['department']['name'];

            $step['shiftworkers'] = [];
            $postIds = [];
            $postIds[$step['post_id']] = $step['post_id'];
            foreach($post['shiftworkers'] as $shiftworker) {

                $postIds[] = $shiftworker['id'];

                $step['shiftworkers'][$shiftworker['id']]['id'] = $shiftworker['id'];
                $step['shiftworkers'][$shiftworker['id']]['name'] = $shiftworker['fio'] . '( ' .$shiftworker['name']. ' )';
                $step['shiftworkers'][$shiftworker['id']]['email'] = $shiftworker['email'];
                $step['shiftworkers'][$shiftworker['id']]['department_id'] = $post['department']['id'];
                $step['shiftworkers'][$shiftworker['id']]['department_name'] = $post['department']['name'];
            }

            $stepsList[$i]['where']['Post.id IN'] = $postIds;
            $stepsList[$i]['params'] = array_merge($schema['step_pattern']['params'], $step);

            $i++;
        }
        $this->schema['steps'] = $stepsList;
        return $this->schema['steps'];
    }

    function moveToBegin()
    {
        $this->schema['steps'] = [];
        $this->schema['state'] = 'declined';
        $this->_sendNotices();
    }

    function checkAcceptAccess($acceptorId)
    {
        $access = false;

        foreach($this->schema['acceptors_collection'] as $step) {
            if(!$step['accepted'] && $step['post_id'] == $acceptorId) {
                $access = true;
                break;
            }
        }

        return $access;
    }

    function getStatus()
    {
        return $this->schema['statuses'][$this->schema['state']];
    }

    function getCurrentAcceptorId()
    {
        foreach($this->schema['steps'] as $step) {
            if(!$step['params']['accepted']) {
                return $step['params']['post_id'];
            }
        }
    }

    function setAcceptor($acceptor)
    {
        $this->acceptor = $acceptor;
    }

    function setStatuses($statuses)
    {
        $this->statuses = $statuses;
    }

    function getCurrentStep()
    {
        return $this->collection[$this->currentKey];
    }

    public function nextKey()
    {
        return $this->position = $this->position + ($this->reverse ? -1 : 1);
    }

    public function getCollection()
    {
        return $this->collection;
    }

    function setCollection($data = [], $schemaType)
    {
        $this->schemaType = $schemaType;

        foreach($data as $item) {
            $this->setItem($item);
        }
        $this->currentKey = 0;

        return $this->collection;
    }

    public function addItems($items)
    {
        return $this->items = $items;
    }

    public function addItem($item)
    {
        return $this->items[] = $item;
    }

    public function getIterator() : Iterartor
    {
        return new \Porabote\Collection\Collection($this);
    }

    public function getReverseIterator() : Iterartor
    {
        return new \Porabote\Collection\Collection($this, true);
    }

    private function setCurrentStep($item)
    {
        $this->currentStep = $item;
    }

}
?>
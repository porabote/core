<?
namespace Porabote\Collection;	
	
class ShemaStepsCollection implements \IteratorAggregate
{
	private $collection;
	private $items;
	private $user;
	private $statuses;
	public $status;
	public $currentItem;
    private $reverse = false;
    public $currentKey;
    public $iterationEnded = false;

    function __construct($collection, $reverse = false)
    {
	    $this->collection = $collection;
	    $this->reverse = $reverse;
    }

    function setUser($user)
    {
	    $this->user = $user;
    }

    function setStatuses($statuses)
    {
	    $this->statuses = $statuses;
    }


    public function setNextItemShemas()
    {
            
        foreach($this->collection as $key => &$item) {	        
	        
	        $this->currentKey = $key;
	        $break = false;
	        
	        //Если шаг уже обработан
	        if(current($this->collection)['state'] == 'complete') continue;
	        
	        //Если шаг ожидает текущего пользователя
	        if($item['post_id'] == $this->user['id']) {
                $item['date'] = date("Y-m-d H:i:s");
                $item['state'] = 'complete';
                $item['signed'] = 1;	
                
                $this->setCurrentItem($item);
                $break = true;                 
                      
	        } else { // Во всех остальных случаях
		        
		        $item['state'] = 'active';
                $this->setCurrentItem($item);
                $break = true; 
	        }

            //Если это конец массива
            if(!next($this->collection))	 {
	            $item['action'] = 'stop';
	            $this->iterationEnded = true;
            } 
            
            if($break) break;

        }

    }

    function getCurrentItem()
    {
	    return $this->collection[$this->currentKey];
    }

    private function setCurrentItem($item)
    {
	    $this->currentItem = $item;
    }


    public function nextKey()
    {
	    return $this->position = $this->position + ($this->reverse ? -1 : 1);
    }

	
	public function getCollection()
	{
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

}	
?>
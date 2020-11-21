<?
namespace Porabote\Collection;

use Iterator;

class Collection implements \Iterator {

    private $collection;
    private $position;
    private $reverse = false;

    function __construct($collection, $reverse = false)
    {
	    $this->collection = $collection;
	    $this->reverse = $reverse;
    }
    
    public function rewind()
    {
	    $this->position = $this->reverse ? count($this->getItems()) - 1 : 0;
    }
    
    public function current()
    {
	    return $this->collection->getItems()[$this->position];
    }
    
    public function key()
    {
	    return $this->position;
    }
    
    public function next()
    {
	    return $this->position = $this->position + ($this->reverse ? -1 : 1);
    }
    
    public function valid(){
	    return isset($this->collection->getItems()[$this->position]);
    }
	
}	
?>
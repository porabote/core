<?php
namespace Porabote\Validation;

use ArrayAccess;
use ArrayIterator;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use Porabote\Validation\RulesStorage;

class Validator //implements ArrayAccess, IteratorAggregate, Countable
{
    /**
     * Holds the ValidationSet objects array
     *
     * @var array
     */
    protected $_fields = [];
    public $errors = [];
    protected $entity;

    function __construct(&$entity)
    {
        $this->entity = &$entity;
    }

    function getValue($fieldName)
    {
        if(isset($this->entity[$fieldName])) return $this->entity[$fieldName];
        else return false;
    }

    /**
     * Checks that a string between max and min
     *
     * Returns true if number correct
     *
     * @param string $digital Value to check
     * @return bool Success
     */
    public function numberBetween($fieldName, $min = null, $max = null)
    {
        $digital = $this->getValue($fieldName);
        if($digital === false) return;

        if ($min && (float) $digital < $min) {
            $this->entity->setErrors([$fieldName => ['Значение может быть не менее ' . $min . ' и не более ' . $max]]);
        }
        if ($max && (float) $digital > $max) {
            $this->entity->setError('Значение может быть не менее ' . $min . ' и не более ' . $max);
        }
        return true;
    }
}
?>
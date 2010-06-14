<?php
/**
 * Model
 *
 *   Interface for Model objects.
 *
 * @author Daniel West <dwest at tux dot appstate dot edu>
 * @package testing
 */
PHPWS_Core::requireInc('testing', 'Exception.php');

interface Model extends ArrayAccess, Iterator, Countable {
    public static function getDb();
    public function getCanonicalName();
    public function getAbbrevName();
    public function save();
    public function load();
    public function represent(Representation $repr, $depth=0);
}

abstract class BaseModel implements Model {
    protected $position = 0;
    protected $fields = array();

    public function __construct(){
        $this->rewind();
    }

    public function getCanonicalName(){
        return get_class($this);
    }

    public function getAbbrevName(){
        return strtolower(get_class($this));
    }

    public function save()
    {
        $db = $this->getDb();
        if(isset($this->id) && is_numeric($this->id))
            $db->addWhere('id', $this->id);

        $result = $db->saveObject($this);

        if(PHPWS_Error::logIfError($result)){
            throw new DatabaseException($result->getMessage());
        }

        return true;
    }

    public function load()
    {
        $db = $this->getDb();
        if(!isset($this['id'])){
            throw new DatabaseException('load() called without an id!');
        }

        $db->addWhere('id', $this['id']);
        $result = $db->loadObject($this);

        if(PHPWS_Error::logIfError($result)){
            throw new DatabaseException($result->getMessage());
        }

        return true;
    }

    public function search()
    {
        $db = $this->getDb();
        
        if(isset($this['id']) && $this->load()){
            return $this;
        }

        foreach($this as $member=>$value){
            $db->addWhere($member, $value);
        }

        $results = $db->getObjects(get_class($this));

        if(PHPWS_Error::logIfError($results)){
            throw new DatabaseException($results->getMessage());
        }

        return $results;
    }

    public function offsetSet($offset, $value){
        $this->$offset = $value;
    }

    public function offsetExists($offset){
        return isset($this->$offset);
    }

    public function offsetUnset($offset){
        unset($this->$offset);
    }

    public function offsetGet($offset){
        return isset($this->$offset) ? $this->$offset : null;
    }

    public function rewind(){
        $this->position = 0;
        $this->fields = array_keys(array_diff_key(get_object_vars($this), array('position'=>0, 'fields'=>0)));
    }

    public function current(){
        return $this[$this->key()];
    }

    public function key(){
        return $this->fields[$this->position];
    }

    public function next(){
        ++$this->position;
    }

    public function valid(){
        return isset($this->fields[$this->position]);
    }

    public function count(){
        return sizeof($this->fields);
    }
}

abstract class ModelComponent implements Model {
    protected $default;
    protected $nullable;
    protected $position = 0;
    protected $fields   = array();
    /**
     *   Components are not stored in the db by themselves, but the interface
     * still requires that we implement save/load.
     */
    public function save(){return true;}
    public function load(){return true;}
    public static function getDb(){return new PHPWS_DB();}
    abstract function getType();

    public function __construct(){
        $this->rewind();
    }

    public function getCanonicalName(){
        return get_class($this);
    }

    public function getAbbrevName(){
        return strtolower(get_class($this));
    }

    public function represent(Representation $repr, $depth=0){
        $repr->represent($this, $depth);
    }

    public function getDefaultValue(){
        return $default;
    }

    /* Array Access methods */
    public function offsetExists($offset){
        return isset($this->$offset);
    }

    public function offsetSet($offset, $value){
        $this->$offset = $value;
    }

    public function offsetGet($offset){
        return $this->$offset;
    }

    public function offsetUnset($offset){
        unset($this->$offset);
    }

    /* Iterator methods */
    public function rewind(){
        $this->position = 0;
        $this->fields = array_keys(array_diff_key(get_object_vars($this), array('position'=>0, 'fields'=>0)));
    }

    public function current(){
        return $this[$this->key()];
    }

    public function key(){
        return $this->fields[$this->position];
    }

    public function next(){
        ++$this->position;
    }

    public function valid(){
        return isset($this->fields[$this->position]);
    }

    public function count(){
        return sizeof($this->fields);
    }
}

class NumberField extends ModelComponent {
    protected $precision;
    
    public function __construct($default, $precision=32, $nullable=true){
        $this->default   = $default;
        $this->precision = $precision;
        $this->nullable  = $nullable;

        parent::__construct();
    }

    public function getType(){
        return 'number';
    }

    public function represent(Representation $repr, $depth=0){
        return $repr->represent($this, $depth);
    }
}

class TextField extends ModelComponent {
    protected $length;

    public function __construct($default, $length=255, $nullable=true){
        $this->default = $default;
        $this->length = $length;
        $this->nullable = $nullable;

        parent::__construct();
    }

    public function getType(){
        return 'text';
    }

    public function represent(Representation $repr, $depth=0){
        return $repr->represent($this, $depth);
    }
}

class ForeignKey extends ModelComponent {
    protected $other;

    public function __construct(Model $other, $nullable=false){
        $this->other = $other;
        $this->nullable = $nullable;

        parent::__construct();
    }

    public function getType(){
        return 'fkey';
    }

    public function represent(Representation $repr, $depth=0){
        return $repr->represent($this, $depth);
    }
}
    
?>
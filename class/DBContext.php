<?php

/*
 * DBContext
 *
 * @author Daniel West <dwest at tux dot appstate dot edu>
 * @package testing
 */

PHPWS_Core::initModClass('testing', 'Context.php');
PHPWS_Core::initModClass('testing', 'Model.php');

class DBContext extends Context implements Model {
    public $id;

    public function __construct($id)
    {
        if(isset($this->id) && is_numeric($this->id)){
            $this->load($id);
        }
    }

    public static function getDb()
    {
        return new PHPWS_DB('db_context');
    }

    public function getCanonicalName()
    {
        return get_class($this);
    }

    public function getAbbrevName()
    {
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

        foreach($this as $component){
            $component->save();
        }
    }
}

/*
 * DBContextComponent
 *
 *   Helper class for storing and retreiving types from the database.  Should
 * never need to be referenced explicitly, all access should be through dbcontext.
 *
 * @author Daniel West <dwest at tux dot appstate dot edu>
 * @package testing
 */
class DBContextComponent extends BaseModel {
    public $id;
    public $context;
    public $key;
    public $type;
    public $val;

    public function __construct(Array $mapping)
    {
        if(isset($mapping['id']) && isset($mapping['context'])){
            $this['context'] = $mapping['context'];
            $this->load($mapping['id']);
            return;
        }

        if(empty($mapping['key'])){
            throw new BadParamException("Context key cannot be empty!");
        }

        $this['key']  = $mapping['key'];
        $this['type'] = $mapping['type'];
        $this['val']  = $mapping['val'];
    }

    public static function getDb()
    {
        return new PHPWS_DB('db_context_component');
    }

    public function save()
    {
        //parent context must be saved first
        if($this['context']['id'] < 1){
            return; //TODO: consider raising an exception
        }

        //need to translate the parent context to it's id temporarily
        $p = $this['context'];

        $this['context'] = $this['context']['id'];
        parent::save();

        //and set it back to the object
        $this['context'] = $p;
    }

    public function load()
    {
        //store the parent context temporarily
        $p = $this['context'];

        parent::load();

        //and set it back after load
        $this['context'] = $p;
    }
}

//?>
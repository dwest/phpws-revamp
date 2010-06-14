<?php
/*
 * Representation
 *
 *  Interface for objects which produce a string representation of
 * Model objects.
 *
 * @author Daniel West <dwest at tux dot appstate dot edu>
 * @package testing
 */
PHPWS_Core::initModClass('testing', 'Model.php');

interface Representation {
    public function represent(Model $object, $depth=0);
    function processHeader();
    function processElement($name, $value);
    function processFooter();
    function onException(Exception $e);
    function onCompletion();
    function hasNext();
}

abstract class ModelRepresentation implements Representation {
    protected $object;
    protected $representation;
    protected $depth;
    protected $progress;

    public function represent(Model $object, $depth=0){
        $this->object = $object;
        $this->representation = "";
        $this->depth = $depth;
        $this->progress = 1;

        $this->processHeader();
        
        foreach($this->object as $name=>$value){
            try{
                $this->processElement($name, $value);
                $this->progress++;
            } catch(AccessException $e){
                $this->onException($e, $name);
            }
        }

        $this->processFooter();
        $this->onCompletion();

        return $this->representation;
    }

    public function processHeader(){
        //pass;
    }

    public function processElement($name, $value){
        //pass;
    }

    public function processFooter(){
        //pass;
    }

    public function onException(Exception $e){
        //pass;
    }

    public function onCompletion(){
        //pass;
    }

    public function hasNext(){
        return $this->progress < sizeof($this->object);
    }
}
?>
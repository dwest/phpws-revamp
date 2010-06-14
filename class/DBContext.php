<?php

/*
 * DBContext
 *
 *   Abstract class which defines methods for storing a context in the database.
 * Requires that all objects stored in the database be either primative types or 
 * implement the "Model" interface in order to handle their own saving and loading.
 *
 * @author Daniel West <dwest at tux dot appstate dot edu>
 * @package testing
 */

PHPWS_Core::initModClass('testing', 'Context.php');
PHPWS_Core::initModClass('testing', 'Model.php');

abstract class DBContext extends Context implements Model {
    public $id;
    
    abstract public function checkType($val);
    abstract public function getMapping();

    public function __construct()
    {
    }
}

/*
 * DBContextComponent
 *
 *   Helper class for storing and retreiving types from the database.  Should
 * never need to be referenced explicitly.
 *
 * @author Daniel West <dwest at tux dot appstate dot edu>
 * @package testing
 */
class DBContextComponent {
    public $key;
    public $type;
    public $val;

    public function __construct(Array $mapping)
    {
    }
}
?>
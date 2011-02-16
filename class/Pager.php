<?php

/*
 * Pager
 *
 *    Provides tools that developers can use to separate content
 * for display across multiple pages.
 *
 * @author Daniel West <dwest at tux dot appstate dot edu>
 * @package testing
 */

class Pager {
    protected $heading;
    protected $data;
    protected $parseFunction;
    protected $linkFunction;

    public function getContent()
    {
        $content = "";
        $content .= "<h2>".$this->getHeading()."</h2>";

        foreach($this->getData() as $datum){
            $content .= $this->parseItem($datum);
            $content .= "<br />";
        }

        return $content;
    }

    public function setHeading($heading)
    {
        $this->heading = $heading;
    }

    public function getHeading()
    {
        return $this->heading;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    public function filterData($field, $operation, $param)
    {
    }

    public function setStart($index)
    {
        if(!is_numeric($index))
            $index = 1;

        $oldWin = $this->data->getWindow();

        $this->data->setWindow(new Window($index, $oldWin->getCount()));
    }

    public function setStride($length)
    {
        if(!is_numeric($length))
            $length = 25;

        $oldWin = $this->data->getWindow();

        $this->data->setWindow(new Window($oldWin->getStart(), $length));
    }

    public function setParser($function)
    {
        $this->parseFunction = $function;
    }

    protected function parseItem($item)
    {
        if(is_callable($this->parseFunction))
            return call_user_func($this->parseFunction, $item);
    }

    public function getPageCount()
    {
        return (int)($this->data->length() / $this->data->getWindow()->getCount());
    }

    public function setLinker($linker)
    {
        $this->linkFunction = $linker;
    }

    public function getPageLinks()
    {
        $links  = "";
        $window = $this->data->getWindow();

        if(is_callable($this->linkFunction)){
            for($i = 0; $i < $this->getPageCount(); $i++){
                $links .= call_user_func($this->linkFunction, $i, $window, $this->getPageCount());
            }
        }

        return $links;
    }
}

interface DataSet extends Iterator {
    public function getItem($index);
    public function setWindow(Window $window);
    public function getWindow();
    public function length();
}

abstract class BaseDataSet implements DataSet {
    protected $window;
    protected $filters;
    protected $position;

    public function __construct(Window $window = NULL)
    {
        if(is_null($window)){
            $this->window = new Window();
        } else {
            $this->window = $window;
        }
    }

    public function setWindow(Window $window)
    {
        $this->window = $window;
    }

    public function getWindow()
    {
        return $this->window;
    }

    /**
     * Iterator Functions
     */
    public function rewind()
    {
        $this->position = $this->window->getStart();
    }

    public function current()
    {
        return $this->getItem($this->position);
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        ++$this->position;
    }
}

class DbDataSet extends BaseDataSet {
    public $_db;
    protected $_queryChanged = true;
    protected $_cachedResult;

    public function getItem($index)
    {
        $this->runQuery();

        if(isset($this->_cachedResult[$index])){
            return $this->_cachedResult[$index];
        }
    }

    public function valid()
    {
        $this->runQuery();

        return isset($this->_cachedResult[$this->position]);
    }

    public function length()
    {
        return $this->_db->count();
    }

    private function runQuery()
    {
        if(!$this->_queryChanged)
            return;

        $result = $this->_db->select();

        if(PHPWS_Error::logIfError($result)){
            return false;
        }

        $this->_cachedResult = $result;
        $this->_queryChanged = false;
    }

}

class ArrayDataSet extends BaseDataSet {
    protected $data;

    public function getItem($index)
    {
        if(isset($this->data[$index])){
            return $this->data[$index];
        }
    }

    public function valid()
    {
        return isset($this->data[$this->position]) && $this->position-$this->window->getStart() < $this->window->getCount();
    }

    public function setData(Array $data)
    {
        $this->data = $data;
    }

    public function length()
    {
        return sizeof($this->data);
    }

}

class Window {
    private $start;
    private $count;

    public function __construct($start = NULL, $count = NULL)
    {
        $this->start = is_numeric($start) ? $start : 1;
        $this->count = is_numeric($count) ? $count : 10;
    }

    public function getStart()
    {
        return $this->start;
    }

    public function getCount()
    {
        return $this->count;
    }

}

//?>
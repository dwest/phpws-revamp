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

    public function __constuct()
    {
    }

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

    public function setParser($function)
    {
        $this->parseFunction = $function;
    }

    protected function parseItem($item)
    {
        if(is_callable($this->parseFunction))
            return call_user_func($this->parseFunction, $item);
    }
}

class DataSet implements Iterator {
    protected $data;
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

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getItem($index)
    {
        if(isset($this->data[$index])){
            return $this->data[$index];
        }
    }

    public function setWindow(Window $window)
    {
        $this->window = $window;
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

    public function valid()
    {
        if($this->position-$this->window->getStart() < $this->window->getCount()){
            return isset($this->data[$this->position]);
        }
        return false;
    }
}

class Window {
    private $start;
    private $count;

    public function __construct($start = NULL, $count = NULL)
    {
        $this->start = is_numeric($start) ? $start : 0;
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
<?php

if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../config/core/404.html';
    exit();
}

//PHPWS_Core::initModClass('testing', 'Note.php');
PHPWS_Core::initModClass('testing', 'Pager.php');
$p = new Pager();
$p->setHeading("My fancy pager!");

$d = new DbDataSet();
$d->_db = new PHPWS_DB('test');
$d->_db->setIndexBy('id');

$p->setData($d);
$p->setParser(function($item){
        return "item: ".$item['value'];
    });
$p->setLinker(function($index, $window, $count){
        return ($index*$window->getCount()+1).'-'.(($index+1)*$window->getCount()).($index < $count-1 ? ', ' : '');
    });

Layout::add($p->getContent());
Layout::add("<p>Pages: ".$p->getPageCount()."</p>");
Layout::add("<p>Links: ".$p->getPageLinks()."</p>");

?>
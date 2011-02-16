<?php

if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../config/core/404.html';
    exit();
}

//PHPWS_Core::initModClass('testing', 'Note.php');
PHPWS_Core::initModClass('testing', 'Pager.php');
$p = new Pager();
$p->setHeading("My fancy pager!");

$d = new DataSet();

$foo = array();
for($i = 0; $i < 10; $i++){
    $foo[$i] = $i;
}

$d->setData($foo);
$p->setData($d);
$p->setParser(function($item){
        return "item: ".$item;
    });

Layout::add($p->getContent());

/*
foreach($d as $key=>$val){
    Layout::add(sprintf("%d %d<br />", $key, $val));
}

Layout::add('Now restricting range<br />');

$d->setWindow(new Window(1, 4));

foreach($d as $key=>$val){
    Layout::add(sprintf("%d %d<br />", $key, $val));
}
*/

?>
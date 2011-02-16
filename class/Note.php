<?php

PHPWS_Core::initModClass('testing', 'Model.php');
PHPWS_Core::initModClass('testing', 'Representation.php');
PHPWS_Core::initModClass('testing', 'SqlRepresentations.php');

class Note extends BaseModel {
    public function __construct($title, $contents){
        $this['title']     = new TextField('default', 255);
        $this['contents']  = new TextField('', NULL);
        $this['edits']     = new NumberField(0,32,false);

        $this['title']    = $title; //set the title's value to $title
        $this['contents'] = $contents; //ditto

        //Need to set variables to iterate over
        parent::__construct();
    }

    public static function getDb(){
        return new PHPWS_DB('testing_note');
    }

    public function represent(Representation $repr, $depth=0){
        return $repr->represent($this, $depth);
    }
}

$note = new Note('foo', 'Making and taking note of "foo"');
$repr = new SqlDefinitionRepresentation;
test($note->represent($repr, 0),1);

//?>
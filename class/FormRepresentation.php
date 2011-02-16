<?php

class FormRepresentation extends ModelRepresentation {
    public function represent(Model $object, $depth=0){
        $representationClass = $this->resolveRepresentation($object);
        $repr = new $representationClass;
        return $object->represent($repr, $depth);
    }
    
    public function resolveRepresentation($object){
        $class = get_class($object);
        if(class_exists($class.'FormRepresentation')){
            $class .= 'FormRepresentation';
            return new $class;
        }
        
        $class = get_parent_class($object);
        $temp = new ReflectionClass($class);
        if($temp->isAbstract()){
            return new ModelSqlRepresentation();
        }
        return $this->resolveRepresentation(new $class);
    }
}

class ModelFormRepresentation extends ModelRepresentation {
    protected $object;
    protected $depth;

    public function represent(Model $object, $depth=0){
        $this->depth  = $depth;
        $this->object = $object;
        return parent::represent($object, $depth);
    }

    public function processHeader(){
        if($this->depth == 0)
            $this->representation .= '<form action="index.php" method="POST">'; //should probably make this more generic
        else
            $this->representation .= '<div type="'.$this->object->getAbbrevName().'">';

        $this->representation .= '<table>';
    }

    public function processElement($name, $value){
        $resolver = new FormRepresentation;
        $repr = $resolver->resolveRepresentation($value);
        $this->representation .= '<tr><th><label for="'.$this->object->getAbbrevName().'-'.$name.'">'.$name.'</label></th>';
        $this->representation .= '<td>'.$value->represent($repr, $this->depth+1).'</td>';
    }

    public function processFooter(){
        $this->representation .= '</table>';

        if($this->depth == 0)
            $this->representation .= '</form>';
        else
            $this->representation .= '</div>';
    }
}

class NumberFieldFormRepresentation extends ModelRepresentation {
    protected $object;
    protected $depth;

    public function represent(Model $object, $depth=0){
        $this->object = $object;
        $this->depth  = $depth;

        parent::represent($object, $depth);
    }

    public function processElement($object, $depth){
        
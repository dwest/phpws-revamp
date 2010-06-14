<?php

class SqlDefinitionRepresentation extends ModelRepresentation {
    public function represent(Model $object, $depth=0){
        $representationClass = $this->resolveRepresentation($object);
        $repr = new $representationClass;
        return $object->represent($repr, $depth);
    }

    public function resolveRepresentation($object){
        $class = get_class($object);
        if(class_exists($class.'SqlRepresentation')){
            $class .= 'SqlRepresentation';
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

class ModelSqlRepresentation extends ModelRepresentation {
    
    public function represent(Model $object, $depth=0){
        if($depth != 0){
            return "bug!"; //TODO: exception
        }

        return parent::represent($object, $depth);
    }

    public function processHeader(){
        $this->representation .= "CREATE TABLE {$this->object->getAbbrevName()} (\n";
    }

    public function processElement($name, $value){
        $resolver = new SqlDefinitionRepresentation;
        $repr = $resolver->resolveRepresentation($value);
        $this->representation .= "\t$name ".$value->represent($repr, $this->depth+1).($this->hasNext() ? "," : "")."\n";
    }

    public function processFooter(){
        $this->representation .= ");\n";
    }
}

class NumberFieldSqlRepresentation extends ModelRepresentation {
    protected $type;
    protected $nullable;
    protected $default;
    
    public function represent(Model $object, $depth=1){
        if($depth != 1){
            return "bug!"; //TODO: exception
        }

        return parent::represent($object, $depth);
    }

    public function processElement($name, $value){
        switch($name){
        case 'precision':
            if(is_null($value))
                $this->type = 'numeric';
            else
                $this->type = 'int('.$value.')';
            break;
        case 'nullable':
            $this->nullable = ''.($value ? '' : 'NOT NULL');
            break;
        case 'default':
            $this->default = ''.(is_null($value) ? '' : 'default '.$value);
        default:
            break;
        }
    }

    public function onCompletion(){
        $this->representation .= $this->type.' '.$this->nullable.' '.$this->default;
    }
}

class TextFieldSqlRepresentation extends ModelRepresentation {
    protected $type;
    protected $nullable;
    protected $default;
    
    public function represent(Model $object, $depth=1){
        if($depth != 1){
            return "bug!"; //TODO: exception
        }

        return parent::represent($object, $depth);
    }

    public function processElement($name, $value){
        switch($name){
        case 'length':
            if(is_null($value))
                $this->type = 'text';
            else
                $this->type = 'varchar('.$value.')';
            break;
        case 'nullable':
            $this->nullable = ''.($value ? '' : 'NOT NULL');
            break;
/*
        case 'default':
            $this->default = ''.(is_null($value) ? '' : 'default '.$value);
*/
        default:
            break;
        }
    }

    public function onCompletion(){
        $this->representation .= $this->type.' '.$this->nullable.' '.$this->default;
    }
}
?>
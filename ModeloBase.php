<?php

use Silex\Application;


abstract class ModeloBase {
  private $_app;
  private $_campos = array();
  private $_id;
  private $_labels = array();

  public function __construct(Application $app) {

    if (array_key_exists("_id", $this->setFields())) {
      throw new Exception("Error no puede haber una variable llamada _id", 1);
    }
    if (array_key_exists("_campos", $this->setFields())) {
      throw new Exception("Error no puede haber una variable llamada _campos", 1);
    }
    if (array_key_exists("_app", $this->setFields())) {
      throw new Exception("Error no puede haber una variable llamada _app", 1);
    }
    if (array_key_exists("_labels", $this->setFields())) {
      throw new Exception("Error no puede haber una variable llamada _labels", 1);
    }

    $this->_app = $app;
    $this->_id = $this->setIdField();
    $this->prepareVariables($this->setFields());
    
    return $this;
  }

  private function prepareVariables($vars){
    $filterFields = array_filter($vars);
    //1era parte
    $aux = array();
    foreach($filterFields as $v) {
      $aux[trim($v)]=null;
    }
    $this->_campos = $aux;
    //2da parte
    foreach ($this->_campos as $key => $value) {
      $this->$key = null;
    }
  }

  public function __set($name, $value) {
    if (array_key_exists($name, $this->_campos)) {
      return $this->$name = $value;
    } else {
      if(isset($this->$name)) {
        return $this->$name = $value;
      } else {
        //nada
        //throw new Exception("Cannot add new property \$$name to instance of " . __CLASS__);
      }
    }
  }

  abstract public function getTableName();
  abstract public function setFields();
  abstract public function setIdField();
  abstract public function rules();
  abstract public function setLabels();

  public function save() {
    $id = $this->_id;
    $tosave = $this->prepareFieldsToSave();
    $out = "";

    if (empty($this->$id)) {
      $out = $this->_app['db']->insert($this->getTableName(), $tosave);
      $this->$id = $this->_app['db']->lastInsertId();
      return $out; 
    } else {
      unset($tosave[$id]);
      return $this->_app['db']->update($this->getTableName(), $tosave, array($id=>$this->$id));
    }

    return false;
  }

  public function delete() {
    $id = $this->_id;
    $out = "";
    if (!empty($this->$id)) {
      return $this->_app['db']->delete($this->getTableName(), array($id=>$this->$id));
    }

    return false;
  }

  public function setAttributes($datos) {

    if (is_array($datos) && !empty($datos)) {
      foreach ($datos as $key => $value) {
        if (empty($value)) {
          $this->$key = null;
        } else {
          $this->$key = $value;
        }
      }
    } else {
      throw new Exception("Error datos deben estar en array()", 1);
    }
  }

  private function prepareFieldsToSave() {
    $campos_copia = $this->_campos;
    $out = array();
    foreach($campos_copia as $k=>$v) {
      $out[$k] = $this->$k;
    }

    return $out;
  }

  public function validate() {
    //required, numerical, maxlenght
    $arr_check = $this->rules();
    $out = array();

    //required
    foreach($arr_check as $v) {
      $aux = explode(',', $v[0]);

      switch($v[1]) {
        case 'required':
          foreach ($aux as $w) {
            $w = trim($w);
            if(empty($this->$w)) {
              $out[] = $this->getLabel($w).' no puede ser nulo';
            }
          }
          break;
        case 'numeric':
          foreach ($aux as $w) {
            $w = trim($w);
            if (!is_numeric($this->$w)){
              $out[] = $this->getLabel($w).' debe ser numerico';
            }
          }
          break;
        case 'maxlenght':
          foreach ($aux as $w) {
            $w = trim($w);
            if (strlen($this->$w) > $v[2]) {
              $out[] = $this->getLabel($w).' debe contener max. '.$v[2].' caracteres';
            }
          }
          break;
      }
    }
    return $out;
  }

  public function getLabel($campo) {
    if (empty($this->_labels)) { $this->_labels = $this->setLabels(); }

    if (array_key_exists($campo, $this->_labels)){
      return $this->_labels[$campo];
    } else {
      return $campo;
    }
  }


  public function queryOne($sql) {
    return $this->_app['db']->fetchAssoc($sql);
  }

}

?>
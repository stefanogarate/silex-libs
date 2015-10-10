<?php
namespace Libs;

use Silex\Application;


class Model {

  private $_app;
  private $_modelo;

  public function __construct(Application $app) {
    $this->_app = $app;
  }

  public function modelo($modelo) {
    $this->_modelo = $modelo;
    return $this;
  }

  public function blank() {
    $clase = $this->_modelo;
    $objOut = new $clase($this->_app);

    return $objOut;
  }

  public function count($condition) {
    if (empty($this->_modelo)) {
      throw new \Exception("Error no hay modelo seleccionado", 1);
    }

    $clase = $this->_modelo;
    $obj = new $clase($this->_app);

    $condition['select'] = 'count('.$obj->setIdField().') as total';
    $condition['limit'] = '';
    $sql = $this->prepareSelect($condition);
    $data = $this->_app['db']->fetchAssoc($sql);
    return $data['total'];
  }

  public function find($condition) {
    if (empty($this->_modelo)) {
      throw new \Exception("Error no hay modelo seleccionado", 1);
    }
    $sql = $this->prepareSelect($condition);
    $data = $this->_app['db']->fetchAssoc($sql);
    return $this->returnRow($data);
  }

  public function findAll($condition='') {
    if (empty($this->_modelo)) {
      throw new \Exception("Error no hay modelo seleccionado", 1);
    }
    $sql = $this->prepareSelect($condition);
    $data = $this->_app['db']->fetchAll($sql);
    return $this->returnRows($data);
  }

  public function deleteAll($condition) {
    if (empty($this->_modelo)) {
      throw new \Exception("Error no hay modelo seleccionado", 1);
    }
    $id = $this->_id;
    $modelo = $this->_modelo;
    $condition['select'] = $id;

    $sql = $this->prepareSelect($condition);
    $data = $this->_app['db']->fetchAll($sql);
    foreach ($data as $value) {
      $this->_app['db']->delete($modelo::getTableName(), array($id=>$value[$id]));
    }
  }

  private function prepareSelect($condition) {
    $auxClase = $this->_modelo;
    $cond = $this->prepareCondition($condition);
    $sql = "SELECT ".$cond['select'];
    $sql .= " FROM ". $auxClase::getTableName();
    $sql .= $cond['condition'].$cond['order'].$cond['limit'];
    return $sql;
  }

  private function prepareCondition($condition) {
    $_condicion = array(
      'select'=>'*',
      'condition'=>'',
      'order'=>'',
      'limit'=>'',
      'params'=>array(),
    );

    if (is_array($condition)) {
      foreach($condition as $k=>$v) {
        if (array_key_exists($k, $_condicion)) {
          $aux = '';
          switch($k) {
            case 'condition':
              $aux = (!empty($v))?' WHERE ':'';
              break;
            case 'order':
              $aux = (!empty($v))?' ORDER BY ':'';
              break;
            case 'limit':
              $aux = (!empty($v))?' LIMIT ':'';
              break;
          }
          $_condicion[$k] = $aux.str_replace('"', '\"', $v);
        }
      }

      if (!empty($_condicion['params'])) {
        $aux = str_replace(array_keys($_condicion['params']), array_values($_condicion['params']), $_condicion['condition']);
        $_condicion['condition'] = $aux;
      }

    } else {
      if (!empty($condition)) {
        $_condicion['condition'] = ' WHERE '.$condition.' ';
      }
    }

    return $_condicion;
  }

  private function returnRow($data) {
    $clase = $this->_modelo;
    $objOut = new $clase($this->_app);
    if ($data !== false) {
      foreach($data as $k=>$w) {
        $objOut->$k = $w;
      }

      return $objOut;
    } else {
      return null;
    }
  }

  private function returnRows($data) {
    $clase = $this->_modelo;
    $objOut = array();
    foreach ($data as $v) {
      $objItem = new $clase($this->_app);
      foreach($v as $k=>$w) {
        $objItem->$k = $w;
      }
      $objOut[] = $objItem;
    }


    return $objOut;
  }
}

?>

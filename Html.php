<?php
namespace Libs;

class Html {
  private $_modelo='';

  public function modelo($modelo) {
    if (empty($modelo)) { throw new Exception("Error faltan parametros", 1); }
    $this->_modelo = $modelo;
  }

  private function _value($name) {
    if (!empty($this->_modelo)) {
      $model = $this->_modelo;
      return $model->$name;
    }
    return '';
  }

  private function _id($name) {
    if (!empty($this->_modelo)) {
      return (new \ReflectionClass($this->_modelo))->getShortName()."_".$name;
    }
    return $name;
  }

  private function _name($name) {
    if (!empty($this->_modelo)) {
      return (new \ReflectionClass($this->_modelo))->getShortName()."[".$name."]";
    }
    return $name;
  }

  public function tag($tag, $htmlOptions=array(), $content=false, $close=true) {
    if (empty($tag)) { throw new Exception("Error faltan parametros", 1); }

    $opts = Html::procesaHtmlOptions($htmlOptions);
    $out = "<".$tag.$opts;

    if ($content == false) {
      $out .= ($close) ? "/>":">";
    } else {
      $out .= ($close) ? ">".$content."</".$tag.">":">";
    }

    return $out;
  }

  public function openTag($tag, $htmlOptions=array()) {
    if (empty($tag)) { throw new Exception("Error faltan parametros", 1); }

    $opts = Html::procesaHtmlOptions($htmlOptions);
    return "<".$tag.$opts.">";
  }

  public function closeTag($tag) {
    if (empty($tag)) { throw new Exception("Error faltan parametros", 1); }
    return "</".$tag.">";
  }

  private function procesaHtmlOptions($options) {
    $out = "";
    foreach($options as $k=>$v) {
      if ($k=='selected' && $v==1) {
        $out .= " ".$k;
      } else {
        $out .= " ".$k."=\"".$v."\"";
      }
    }

    return $out;
  }

  public function input($name, $tipo, $extra='') {
    if (empty($name)) { throw new Exception("Error faltan parametros", 1); }
    $out = '';
    $id = $this->_id($name);
    $value = $this->_value($name);
    //siempre al final
    $name = $this->_name($name);

    switch($tipo) {
      case 'textarea':
        $out .= Html::openTag("textarea", array('type'=>$tipo, 'class'=>'form-control', 'name'=>$name, 'id'=>$id));
        $out .= $value;
        $out .= Html::closeTag('textarea');
        break;
      case 'select':
        $out .= Html::openTag("select", array('type'=>$tipo, 'class'=>'form-control', 'name'=>$name, 'id'=>$id));
        foreach($extra as $k=>$v) {
          if ($k == $value) {
            $out .= "  ".Html::Tag('option', array('value'=>$k, 'selected'=>1), $v)."\n";
          } else {
            $out .= "  ".Html::Tag('option', array('value'=>$k), $v)."\n";
          }
        }
        $out .= Html::closeTag('select');
        break;
      default:
        $out .= Html::openTag("input", array('type'=>$tipo, 'class'=>'form-control', 'name'=>$name, 'id'=>$id, 'value'=>$value));
    }
    

    return $out;
  }

  public function inputText($name) {
    return $this->input($name, 'text')."\n";
  }

  public function inputTextArea($name) {
    return $this->input($name, 'textarea')."\n";
  }

  public function dropDownList($name, $data) {
    return $this->input($name, 'select', $data)."\n";
  }

}

?>

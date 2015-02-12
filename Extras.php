<?php
namespace Libs;

class Extras {

  public function Paginacion($show, $cant, $current, $showPaginacion=7){
    $out = array();
    $paginas = ceil($cant/$show);
    $mitad = ceil($showPaginacion/2);

    if ($paginas > $showPaginacion) {
      if ($current < ($mitad+1)) {
        $inicio = 1;
        $fin = $showPaginacion;
      } elseif ($current > ($paginas - $mitad)) {
        $inicio = $paginas - $showPaginacion + 1;
        $fin = $paginas;  
      } else {
        $inicio = $current - $mitad + 1;
        $inicio = ($inicio < 1)?1:$inicio;
        $fin = $current + $mitad - 1;
        $fin = ($fin > $paginas)?$paginas:$fin;
      }
    } else {
      $inicio = 1;
      $fin = $paginas;
    }


    for($i=$inicio; $i<=$fin; $i++) {
      $out[] = $i;
    }

    return $out;
  }

  public function UrlParametros($sacale) {
  $url = '';
  $aux = parse_url($_SERVER['REQUEST_URI']);

    if (!empty($aux['query'])){
      if(strpos($aux['query'], '&') !== false) {
        //multiples parametros
        $aux1 = explode("&", $aux['query']);
        for($i=0; $i<count($aux1); $i++) {
          if (strpos($aux1[$i], $sacale) !== false) {
            unset($aux1[$i]);
            break;
          }
        }

        $url = implode("&", $aux1)."&";
      }else{
        //1 solo parametro
        if(strpos($aux['query'], $sacale) === false) {
          $url = $aux['query'];
        }
      }
    }
    return $url;
  }

  public function dataToArrayAsoc($data, $indice, $valor){
    $out = array();
    foreach ($data as $value) {
      $out[$value[$indice]] = $value[$valor];
    }

    return $out;
  }

}

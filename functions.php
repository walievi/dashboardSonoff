<?php 
function montaGrid($colunas, $lista){
	echo '<table class="table table-striped table-sm">';
	echo '<thead><tr>';

	foreach ($colunas as $coluna) {
		echo '<th scope="col">' . $coluna . '</th>';
	}

	echo '</tr></thead>';
    echo '<tbody>';

    foreach ($lista as $linha) {
    	echo '<tr>';
    	foreach ($colunas as $coluna){
    		echo '<td>' . $linha->{$coluna} . '</td>';
    	}
    	echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';


}


function menus($menus, $current = ""){

	echo '
    <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
      <div class="position-sticky pt-3">
        <ul class="nav flex-column"> 
    ';


        foreach ($menus as $menu){
          echo '
	          <li class="nav-item">
	            <a class="nav-link '. (($current != $menu->action)?: 'active') .'" aria-current="page" href="index.php?action='. $menu->action .'">
	              <span data-feather="'. $menu->span .'"></span>
	              '. $menu->title .'
	            </a>
	          </li>
          ';

        }
    echo '
        </ul>
      </div>
    </nav>
    ';
}


function montaMatriz(){
  echo '<script src="dashboard.js"></script>';
}


    
    function setTasmota($equip_id, $acao, $origem){
        try {
            $equip = getTable('equipamentos', "*", array("id" => $equip_id))[0]; 

            tasmotaFaz($equip->ip, $acao);

            registraEvento($equip->id, $acao, $origem);

        } catch (Exception $e) {
            //LOG de ERRO   
            die("deu erro");
        }
    }

    function getTasmota($equip_id, $acao){

        $equip = getTable('equipamentos', "*", array("id" => $equip_id))[0]; 
        return tasmotaFaz($equip->ip, $acao);        

    }

    function tasmotaFaz($ip, $acao){
        $tasmotaAcoes = array(
          'getPowerStatus' => 'Power',
          'powerOn' => 'Power%20On',
          'powerOff' => 'Power%20off',
          'sensores' => 'Status%208'

        );

        if(!isset($tasmotaAcoes[$acao]))
            throw new Exception("Ação inválida", true);
        

        $url = "http://". $ip ."/cm?cmnd=". $tasmotaAcoes[$acao];

        return json_decode(requestUrl($url));
  }


  function requestUrl($url){
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL,$url);

      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

      $contents = curl_exec ($ch);

      curl_close ($ch);

      return $contents;
  }




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
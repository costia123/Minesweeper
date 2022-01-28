<?php

class grid_gen{
  public $table_html = ""; // je définie ma valeur 
  public $pre_table = "";
  public $post_table = "";
  public $color = array(
    1=>"blue",
    2=>"green",
    3=>"red",
    4=>"purple",
    5=>"brown",
    6=>"pink",
    7=>"yellow",
    8=>"red");
  
  function form_content(){ // donc la c'est toute la formation du html pour le jeux
    global $data;
    
    if (($data->mode == "game") || ($data->mode == "new") || ($data->mode=="start")){
    $this->pre_table.="<div class='mine_form'>";
    $this->pre_table.="<form action='index.php' method='post' id='minesweeper' >\n";
    }else{
    $this->post_table.="<a href='.'>Restart</a><br>";
    $this->table_html .= "</div>\n";
    }
    
    if ($data->mode == "new"){ // donc si il ya pas de game on mets les valuer pour choisir le mode de jeux
      $this->pre_table.="<div class='dificulty'>";
      $this->pre_table.="choisissez la dificulté : </br><select name = 'difficulty'>\n";
      $this->pre_table.=" <option value='easy'>Easy</option>\n";
      $this->pre_table.=" <option value='medium'>Medium</option>\n";
      $this->pre_table.=" <option value='hard'>Hard</option>\n";
      $this->pre_table.=" <option value='custom'>Custom</option>\n";
      $this->pre_table.="</select>";
      $this->pre_table.="</div>";
      $this->pre_table.="<div class='custom_settings'>";
      $this->pre_table.="<br>Paramètre personaliser :<br>";
      $this->pre_table.="Ligne : <select name = 'num_rows'>\n";
        $this->options_builder(50); //on call la loop pour les select de row col et mines 
      $this->pre_table.="</select>\n";
      $this->pre_table.="Colonne : <select name = 'num_cols'>\n";
        $this->options_builder(50);
      $this->pre_table.="</select>\n";
      $this->pre_table.="Mine : <select name = 'num_mines'>\n";
        $this->options_builder(50);
      $this->pre_table.="</select>\n";
      $this->pre_table.="</div>";
      $this->pre_table.="<br><input type='submit' name='mode' value='start'>";
      $this->pre_table.="</div>";
    }

    if ($data->mode == "start" || $data->mode == "game"){ // si c'est le debut on affiche le tableau non découvert 
      $this->pre_table.="<input type='hidden' name='grid_reference' value='" . htmlspecialchars(serialize($data->grid_reference)) . "'>\n";
      $this->pre_table.="<input type='hidden' name='num_cols' value='" . $data->num_cols . "'>\n"; // avec les valeur qu'on a générer dans input_handler
      $this->pre_table.="<input type='hidden' name='num_rows' value='" . $data->num_rows . "'>\n";
      $this->pre_table.="<input type='hidden' name='num_mines' value='" . $data->num_mines . "'>\n";
      $this->pre_table.="Mines:". ($data->num_mines-count($data->marked_cells))."<br>\n"; // la c'ets laffichage des mines restante 
    }

    
    if ($data->mode == "game"){
      $this->pre_table.="<input type='hidden' name='mode' value='game'>";
      $this->pre_table.="<input type='hidden' name='cell_values' value='" . htmlspecialchars(serialize($data->cell_values)) . "'>\n"; // la ont passe les valeur avec htmlspe pour empecher de cheat 
      $this->pre_table.="<input type='hidden' name='mine_cells' value='" . htmlspecialchars(serialize($data->mine_cells)) . "'>\n"; 
      $this->pre_table.="<input type='hidden' name='visible_cells' value='" . htmlspecialchars(serialize($data->visible_cells)) . "'>\n"; 
      $this->pre_table.="<input type='hidden' name='marked_cells' value='" . htmlspecialchars(serialize($data->marked_cells)) . "'>\n";
      $this->pre_table.="Toggle Marked <input class='test' type='checkbox' name='mark_toggle'"; // la c'est le truc pour les flag 
      if($data->mark_toggle == true){
        $this->pre_table.=" checked='checked'";
      }
      $this->pre_table.=">\n";
    }

    if ($data->mode == "start"){
      $this->pre_table.="<input class='m_cell' type='hidden' name='mode' value='game'>"; // la c'est l'affichage avant la revelation de la première case
      $this->pre_table.="Toggle Marked\n";
    }
  
    if ($data->mode != "game_won"){
    $this->post_table.="</form>\n"; //si la game n'est  pas gagné 
    }
    if ($data->mode == "game_over"){
      $this->pre_table.= "Perdu t'es nul OMG \n"; // si perdu
    }
    if ($data->mode == "game_won"){
      $this->pre_table.= "GG WP t'es pas trop con !\n"; //si gaggné
      
    }
  }
  
  function options_builder($number){
    for ($x=8;$x<=$number;$x++){
      $this->pre_table.=" <option value='$x'>$x</option>\n"; // le truc pour les options dans le select
    }
  }
  
  function create_table(){
    global $data;
    $this->table_html .= "<div class='mine_form'>\n"; // la c'est l'afficahge de si tu a perdu 
     $this->table_html .= "<table border='1'>\n";
      for ($x=10;$x<($data->num_rows+10);$x++){
        $this->table_html .= "<tr>\n";
        for ($y=10;$y<($data->num_cols+10);$y++){
          $block = $x.$y;
          if(($data->mode=="game_over") && ($data->submitted_block == $block)){
          $extra=" bgcolor='red'";
          }else{
          $extra="";
          }
          $this->table_html .= "<td class='mine_case_style' align='center'$extra>";
          $this->cell_content($block);
          $this->table_html .= "</td>\n";
        }
        $this->table_html .= "</tr>\n";
      }
      $this->table_html .= "</table>\n";
      
  }
  
  function cell_content($block){ // la on gère le contenue des cell donc en gros drapeau mine ou chiffre et la couleur du chiffe
  global $data;
    if ($data->mode == "start"){ // donc block c'est la valeur de la case
      $this->table_html .= "<input type='hidden' name='mode' value='game' class='m_cell' ><input type='submit' name='submitted_block' value='". $block ."' class='m_cell' />"; // on charge les donné dans les input mais toujours hidden
    }else{
      if (in_array($block,$data->visible_cells)){ // la si c'est une cell visible
        if (array_key_exists($block,$data->cell_values)){ // on filtre les mine et les flag
            $this->color_number($data->cell_values[$block]);  // on mets la bonne couleur
          }else{
            if (in_array($block,$data->mine_cells)){
              $this->table_html .= "<strong><img class='mine_img' src='./../../static/img/mine.png'/></strong>"; // si c'ets une mine ont mets l'image
            }else{
              $this->table_html .= "";
          }
        }
      }else{
        $this->table_html .= "<input type='submit' name='submitted_block' value='". $block ."' class='m_cell "; // et du coup plus qu'un truc c'est le flag
          if(in_array($block,$data->marked_cells)){
            $this->table_html .= "flag_img";
          }
        $this->table_html .= "'/>";
      }
    }
  }
  
  function color_number($number){ // la function des couleur
    $this->table_html .= "<font style='color:".$this->color[$number]."'>$number</font>";
  }
  
  
  function generate(){ // et du coup la function generate qui affiche les chose au bon moment (le truc pour choisir la dificulté le form avec les cell en version win ou loose et en version en game)
    global $data;
    $this->form_content();
    echo $this->pre_table;
    if((!isset($data->mode)) || ($data->mode != "new")){
    $this->create_table();
    echo $this->table_html;
    }
    echo $this->post_table;
  }
}
$grid_gen = new grid_gen();
?>
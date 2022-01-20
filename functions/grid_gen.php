<?php

class grid_gen{
  public $table_html = "";
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
  
  function form_content(){
    global $data;
    
    if (($data->mode == "game") || ($data->mode == "new") || ($data->mode=="start")){
    $this->pre_table.="<div class='mine_form'>";
    $this->pre_table.="<form action='index.php' method='post' id='minesweeper' >\n";
    }else{
    $this->post_table.="<a href='.'>Restart</a><br>";
    $this->table_html .= "</div>\n";
    }
    
    if ($data->mode == "new"){
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
        $this->options_builder(50);
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

    if ($data->mode == "start" || $data->mode == "game"){
      $this->pre_table.="<input type='hidden' name='grid_reference' value='" . htmlspecialchars(serialize($data->grid_reference)) . "'>\n";
      $this->pre_table.="<input type='hidden' name='num_cols' value='" . $data->num_cols . "'>\n";
      $this->pre_table.="<input type='hidden' name='num_rows' value='" . $data->num_rows . "'>\n";
      $this->pre_table.="<input type='hidden' name='num_mines' value='" . $data->num_mines . "'>\n";
      $this->pre_table.="Mines:". ($data->num_mines-count($data->marked_cells))."<br>\n";
    }

    
    if ($data->mode == "game"){
      $this->pre_table.="<input type='hidden' name='mode' value='game'>";
      $this->pre_table.="<input type='hidden' name='cell_values' value='" . htmlspecialchars(serialize($data->cell_values)) . "'>\n";
      $this->pre_table.="<input type='hidden' name='mine_cells' value='" . htmlspecialchars(serialize($data->mine_cells)) . "'>\n"; 
      $this->pre_table.="<input type='hidden' name='visible_cells' value='" . htmlspecialchars(serialize($data->visible_cells)) . "'>\n"; 
      $this->pre_table.="<input type='hidden' name='marked_cells' value='" . htmlspecialchars(serialize($data->marked_cells)) . "'>\n";
      $this->pre_table.="Toggle Marked <input class='test' type='checkbox' name='mark_toggle'";
      if($data->mark_toggle == true){
        $this->pre_table.=" checked='checked'";
      }
      $this->pre_table.=">\n";
    }

    if ($data->mode == "start"){
      $this->pre_table.="<input class='m_cell' type='hidden' name='mode' value='game'>";
      $this->pre_table.="Toggle Marked\n";
    }
  
    if ($data->mode != "game_won"){
    $this->post_table.="</form>\n";
    }
    if ($data->mode == "game_over"){
      $this->pre_table.= "Perdu t'es nul OMG \n";
    }
    if ($data->mode == "game_won"){
      $this->pre_table.= "GG WP t'es pas trop con !\n";
      
    }
  }
  
  function options_builder($number){
    for ($x=8;$x<=$number;$x++){
      $this->pre_table.=" <option value='$x'>$x</option>\n";
    }
  }
  
  function create_table(){
    global $data;
    $this->table_html .= "<div class='mine_form'>\n";
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
  
  function cell_content($block){
  global $data;
    if ($data->mode == "start"){
      $this->table_html .= "<input type='hidden' name='mode' value='game' class='m_cell' ><input type='submit' name='submitted_block' value='". $block ."' class='m_cell' />";
    }else{
      if (in_array($block,$data->visible_cells)){
        if (array_key_exists($block,$data->cell_values)){
            $this->color_number($data->cell_values[$block]);
          }else{
            if (in_array($block,$data->mine_cells)){
              $this->table_html .= "<strong><img class='mine_img' src='./../../static/img/mine.png'/></strong>";
            }else{
              $this->table_html .= "";
          }
        }
      }else{
        $this->table_html .= "<input type='submit' name='submitted_block' value='". $block ."' class='m_cell ";
          if(in_array($block,$data->marked_cells)){
            $this->table_html .= " flag_img";
          }
        $this->table_html .= "'/>";
      }
    }
  }
  
  function color_number($number){
    $this->table_html .= "<font style='color:".$this->color[$number]."'>$number</font>";
  }
  
  
  function generate(){
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
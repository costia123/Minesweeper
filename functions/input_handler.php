<?php

class data_handler{
  public $mode = "";          
  public $grid_reference = array();   
  public $cell_values = array();   
  public $mine_cells = array();    
  public $visible_cells = array();   
  public $marked_cells = array();    
  public $difficulty = "easy";      
  public $num_rows;          
  public $num_cols;         
  public $num_mines;          
  public $submitted_block;      
  public $mark_toggle;        
  
  function __construct(){    
    if(isset($_POST['mode'])){
      $this->mode=$_POST['mode']; 
      if($this->mode == "game"){
        $this->submitted_block = $_POST['submitted_block'];
        $this->num_rows = $_POST['num_rows'];
        $this->num_cols = $_POST['num_cols'];
                $this->num_mines = $_POST['num_mines'];
        
        if(!isset($_POST['cell_values'])){
        $this->grid_reference = unserialize($_POST['grid_reference']);
        $this->generate_values();
        $this->play_game();
        }else{
          $this->grid_reference = unserialize($_POST['grid_reference']);
          $this->cell_values = unserialize($_POST['cell_values']);
          $this->mine_cells = unserialize($_POST['mine_cells']);
          $this->visible_cells = unserialize($_POST['visible_cells']);
          $this->marked_cells = unserialize($_POST['marked_cells']);
          if(isset($_POST['mark_toggle'])){
            $this->mark_toggle = $_POST['mark_toggle'];
          }else{
            $this->mark_toggle = false;
          }
          $this->play_game();
        }
      }
      if($this->mode == "start"){
        $this->generate_grid();
      }
    }else{
      $this->mode="new";
    }
  }

  function generate_grid(){
    switch ($_POST['difficulty']){
      case "easy":
        $this->num_rows = "8";
        $this->num_cols = "8";
        $this->num_mines = "10";
      break;
      case "medium":
        $this->num_rows = "16";
        $this->num_cols = "16";
        $this->num_mines = "40";
      break;
      case "hard":
        $this->num_rows = "24";
        $this->num_cols = "24";
        $this->num_mines = "99";
      break;
      case "custom":
        $this->num_rows = $_POST['num_rows'];
        $this->num_cols = $_POST['num_cols'];
        $this->num_mines = $_POST['num_mines'];
      break;
    }
    for ($x=10;$x<($this->num_rows+10);$x++){
      for ($y=10;$y<($this->num_cols+10);$y++){
        array_push($this->grid_reference,$x.$y);
      }
    }
  }

  function generate_values(){
    $this->mine_cells = $this->grid_reference;
    $key = array_search($this->submitted_block, $this->mine_cells);
    unset($this->mine_cells[$key]);
    shuffle($this->mine_cells);
    $this->mine_cells = array_values(array_slice($this->mine_cells, 0, $this->num_mines));
    
    foreach($this->grid_reference as $cell){
      if (!in_array($cell,$this->mine_cells)){
        $cells_to_check = array();
        $cells_to_check = $this->get_surrounding_cells($cell);
        $number = count(array_intersect($cells_to_check,$this->mine_cells));
        if ($number>0){
        $this->cell_values[$cell]=$number;
        }
      }
    }
    $this->process_cell($this->submitted_block);  
  }
  
  function play_game(){
    
    if ($this->mark_toggle == true){
      $this->process_cell($this->submitted_block);
      $this->is_game_won();
      return;
    }else{
      if (!in_array($this->submitted_block,$this->marked_cells)){
        if (in_array($this->submitted_block,$this->mine_cells)){
          $this->click_mine();
          return;
        }elseif (isset($this->cell_values[$this->submitted_block])){
          $this->click_number();
          return;
        }else{
          $this->click_blank();
          return;
        }
      }
    }
  }

  function click_mine(){
    $this->game_over();
  }
  
  function click_number(){
    $this->process_cell($this->submitted_block);
  }

  function click_blank(){
    
    $cells_to_check = $this->get_surrounding_cells($this->submitted_block);  
    $cells_checked = array(); 
    $this->process_cell($this->submitted_block);
    $x=1;
    while ($x>0){
      $x=0;
      foreach ($cells_to_check as $cell){
        $this->process_cell($cell);
         if((!isset($this->cell_values[$cell])) && (!in_array($cell,$cells_checked))){
          array_push($cells_checked,$cell);
          $cells_to_check = array_merge($cells_to_check,$this->get_surrounding_cells($cell));
          array_diff($cells_to_check,array($cell));
          $x++;
        }
      }
    }
  }

  function game_over(){
    $this->visible_cells = $this->grid_reference;
    $this->mode = "game_over";
  }
  
  function get_surrounding_cells($cell){
    $cells_to_check = array();
    array_push($cells_to_check, substr($cell,0,2)-1 .substr($cell,2,2)-1);
    array_push($cells_to_check, substr($cell,0,2)-1 .substr($cell,2,2));
    array_push($cells_to_check, substr($cell,0,2)-1 .substr($cell,2,2)+1);
    array_push($cells_to_check, substr($cell,0,2) .substr($cell,2,2)-1);
    array_push($cells_to_check, substr($cell,0,2) .substr($cell,2,2)+1);
    array_push($cells_to_check, substr($cell,0,2)+1 .substr($cell,2,2)-1);
    array_push($cells_to_check, substr($cell,0,2)+1 .substr($cell,2,2));
    array_push($cells_to_check, substr($cell,0,2)+1 .substr($cell,2,2)+1);
    $cells_to_check = array_intersect($cells_to_check,$this->grid_reference);
    $cells_to_check = array_diff($cells_to_check,$this->marked_cells);    
    return $cells_to_check;
  }

  function process_cell($cell){
    if(($cell == $this->submitted_block) && ($this->mark_toggle == true) && (!in_array($this->submitted_block,$this->marked_cells))){
      array_push($this->marked_cells,$this->submitted_block);
      return;
    }elseif(($cell == $this->submitted_block) && ($this->mark_toggle == true) && (in_array($this->submitted_block,$this->marked_cells))){
      $key = array_search($this->submitted_block, $this->marked_cells);
      unset($this->marked_cells[$key]);
      return;
    }
    if (!in_array($cell,$this->marked_cells)){
      array_push($this->visible_cells,$cell);
      $this->visible_cells = array_unique($this->visible_cells);
      $this->is_game_won();
    }
  }
  
  function is_game_won(){
    if((isset($_POST)) && ((count($this->grid_reference) - count($this->visible_cells)) == count($this->mine_cells))){
    $this->mode="game_won";
    }
  }
  
}
$data = new data_handler();
?>
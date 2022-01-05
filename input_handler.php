<?php
// Minesweeper in PHP by Daniel Porter - thisismywww.com
// Version 1.0 - 28 Sept 2018

// class to handle input
class data_handler{
  public $mode = "";          // Specifies game mode - new/start/game/game_won/game_over
  public $grid_reference = array();   // Array of all grid references
  public $cell_values = array();    // Values in each cell, only for display
  public $mine_cells = array();    // Array of cells with mines in them
  public $visible_cells = array();  // Array of grid_references which are 'visible' 
  public $marked_cells = array();    // Array of grid_references which are 'marked'
  public $difficulty = "easy";      // Difficulty for grid: easy, medium, hard, custom
  public $num_rows;          // Number of rows in grid
  public $num_cols;          // Number of columns in grid
  public $num_mines;          // Number of mines in grid
  public $submitted_block;      // Block which is submitted each click
  public $mark_toggle;        // Variable if marked is checked
  
  function __construct(){
    // Mode = config  Config is submitted
    // Mode = game     Game is being played
      // If no cells are posted, generate grid and then play with submitted cell
      // If cells are posted, set variables and play game
    // Mode = new    Nothing is posted
      
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
    // Set the number of rows, columns and mines based on difficulty
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
    // Generate grid)reference array
    for ($x=10;$x<($this->num_rows+10);$x++){
      for ($y=10;$y<($this->num_cols+10);$y++){
        array_push($this->grid_reference,$x.$y);
      }
    }
  }

  function generate_values(){
    // Generate mine_cells and cell_values - need to know mine_cells before cell values
    // Mine Cells are created by using the grid references, minus clicked cell then trimmed
    $this->mine_cells = $this->grid_reference;
    $key = array_search($this->submitted_block, $this->mine_cells);
    unset($this->mine_cells[$key]);
    shuffle($this->mine_cells);
    $this->mine_cells = array_values(array_slice($this->mine_cells, 0, $this->num_mines));
    
    // Calculate how many mines are surrounding each cell if cell isn't a bomb
    // If none, don't set value
    
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
    // On first round, make the submitted block visible
    $this->process_cell($this->submitted_block);  
  }
  
  function play_game(){
    // If toggle marked is true, run add to visible function then check if game is won
    // otherwise, if the clicked cell 
    // It the submitted isn't marked, run click function based on if it's a mine, number of blank
    
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
    // If a mine is clicked, it's game over
    $this->game_over();
  }
  
  function click_number(){
    // If a number is clicked, only that cell is made visible
    $this->process_cell($this->submitted_block);
  }

  function click_blank(){
    // When a blank cell is clicked, each surrounding block is made visible.
    // Repeating outwards each time there are blank cells made visible.
    // To reduce number of checks, each time a cell is checked, its in an array
    // so that it's not checked again.
    
    $cells_to_check = $this->get_surrounding_cells($this->submitted_block);  
    $cells_checked = array(); 
    $this->process_cell($this->submitted_block);
    $x=1;
    while ($x>0){
      $x=0;
      foreach ($cells_to_check as $cell){
        $this->process_cell($cell);
        // If the cell is empty and it hasn't been checked we add it to checked cells
        // add the surrounding blank cells to the array to be checked. Increasing x so it will loop again.
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
    // Set all cells visible and set mode to game_over
    $this->visible_cells = $this->grid_reference;
    $this->mode = "game_over";
  }
  
  function get_surrounding_cells($cell){
    // Sloppy, but generates array of all cells surrounding the submitted cell, making sure
    // the values are only those one in the grid reference array.
    // also removes marked cells so it's not included in a cell which is checked.
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
    // general function to process mark a submitted cell.
    
    // If the cell needs to be marked, add it to marked, otherwise, if it is marked and mark
    // toggle is on, remove from marked cells
    if(($cell == $this->submitted_block) && ($this->mark_toggle == true) && (!in_array($this->submitted_block,$this->marked_cells))){
      array_push($this->marked_cells,$this->submitted_block);
      return;
    }elseif(($cell == $this->submitted_block) && ($this->mark_toggle == true) && (in_array($this->submitted_block,$this->marked_cells))){
      $key = array_search($this->submitted_block, $this->marked_cells);
      unset($this->marked_cells[$key]);
      return;
    }
    
    // if it's not in the marked cells, make it visible then check if the game is won.
    if (!in_array($cell,$this->marked_cells)){
      array_push($this->visible_cells,$cell);
      $this->visible_cells = array_unique($this->visible_cells);
      $this->is_game_won();
    }
  }
  
  function is_game_won(){
    // check to see if game is won. A simple calculation
    if((isset($_POST)) && ((count($this->grid_reference) - count($this->visible_cells)) == count($this->mine_cells))){
    $this->mode="game_won";
    }
  }
  
}
// instance input_handler class
$data = new data_handler();
?>
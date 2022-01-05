<html>
  <head>
    <title>CMine</title>
  </head>
  <body>
    <h1>Costia's Minesweeper</h1>
    <?php
      $output = "";
      include('input_handler.php');
      include('grid_gen.php');
      $grid_gen->generate();
    ?>
  </body>
</html>
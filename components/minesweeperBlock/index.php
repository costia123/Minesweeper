<?php
      include($_SERVER["DOCUMENT_ROOT"]."/minesweeper/config.php");
      $output = "";
      include($functionsPath."input_handler.php");
      include($functionsPath."grid_gen.php");
      $grid_gen->generate();
    ?>
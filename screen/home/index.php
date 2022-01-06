<?php 
include($_SERVER["DOCUMENT_ROOT"]."/minesweeper/config.php");

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../css/bootstrap.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../../css/style.css?v=<?php echo time(); ?>">
    <title>CMines</title>
</head>
<body>

<?php include($componentsPath."header/index.php"); ?> 

<?php include($componentsPath."minesweeperBlock/index.php"); ?> 
      
</body>
<script src="../../js/bootstrap.js"></script>
</html>
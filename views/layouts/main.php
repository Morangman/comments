<?php


?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Сообщения и комментарии</title>
        <link rel="stylesheet" type="text/css" href="css/main.css" />
    </head>
    <body>
        <?php
        echo $this->getViewContents($viewFile, $params);
        ?>
    </body>
</html>

<?php

require_once __DIR__ . '/../vendor/autoload.php';

ini_set('memory_limit', -1);
ini_set('max_execution_time', 900);

error_reporting(E_ALL);
set_error_handler('Core\Error::errorHandler');
set_exception_handler('Core\Error::exceptionHandler');


if($_SERVER['REQUEST_METHOD'] === 'POST'){


    $url = htmlspecialchars( strip_tags( trim( $_POST['url'] ) ) );

	$parser = new App\Parser($url);

	$parser->run();

}

?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Coursehunter Parser</title>
    <link href="https://fonts.googleapis.com/css?family=Jura&amp;subset=cyrillic" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://bootswatch.com/4/materia/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">   
</head>
<body>

<div class="container w-100 h-100">
    <h3 class="text-center mt-5 mb-3 text-uppercase">Coursehunter Parser</h3>
    <div class="row">
      <div class="col-6">
        <form class="mx-auto mt-5 p-4 w-100" autocomplete="off">
          <legend>
            <div class="float-left download_text"></div>
            <div class="float-right download_img">
              <img src="img/43.gif" alt="">
            </div>
            <div class="clearfix"></div>
          </legend>
           <div class="form-group">
             <label for="url"></label>
             <input type="url" name="url" id="url" class="form-control text-center" placeholder="" pattern="https://coursehunters.net/.+?" required>
           </div>
           <button type="submit" class="w-100"></button>
        </form>        
      </div>
      <div class="col-6">
          <div class="jumbotron mt-5 p-4">
            <h3 class=""></h3>          
            <hr class="my-4">
            <p class="lead font-weight-bold"></p>
            <p class="lead"></p>
            <p class="lead"></p>
            <p class="lead"></p>
            <ul>
            </ul>
          </div>
      </div>
    </div>

</div>


<script
  src="https://code.jquery.com/jquery-3.3.1.min.js"
  integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
  crossorigin="anonymous"></script>
<script src="js.js"></script>		
</body>
</html>
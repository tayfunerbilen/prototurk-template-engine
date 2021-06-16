<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    <title><?=$title?></title>
    <meta name="description" content="burası ana sayfa">
    <link rel="stylesheet" type="text/css" href="css/index.css" />

    
    <style>
        body {
            background: orangered;
        }
    </style>

</head>
<body>

<aside class="sidebar">
    
    <h3>Kategoriler</h3>

</aside>

<main class="content">
    <?php if (isset($_GET['page']) && $_GET['page'] == "contact"): ?>
        Şu an iletişim sayfasındasın!
    <?php endif; ?>
    

    <h3 class="title3">
        Hoşgeldin, <?=$name?>
    </h3>

    <ul>
        <?php foreach($todos as $todo): ?>
            <li class="todo-item23">
    <?=$todo?>
</li>
        <?php endforeach; ?>
    </ul>

    <?php 
    $test = "deneme";
     ?>

    <?=$test?>


</main>



</body>
</html>
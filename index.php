<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="styles/simple.css" />
    <title>Document</title>

    <style>

    </style>
</head>
<body>
    <header>
        <h1>AQI-</h1>
        <nav><a href="index.php">Overview</a></nav>
    </header>
    <main>

    <?php

    $citites = json_decode(file_get_contents(__DIR__. '/data/index.json'), true);

    

    ?>

    <?php foreach ($citites as $city): ?>

    <ul>
        <li><a href="city.php?<?php echo http_build_query(['city' => $city['city']]) ?>"><?php echo "{$city['city']}  {$city['country']}  ({$city['flag']})" ?></a></li>
    </ul>
        
    <?php endforeach; ?>

    </main>
    </body>
</html>



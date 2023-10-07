<?php
    session_start();
    include_once "connection.php";

    if(empty($_SESSION['username'])){
        header("Location: login.php");
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include "modules/nav.php"; ?>

    <?php 
        if(empty($_GET['id'])){
            include "modules/not_found.php";
        }
    ?>


</body>
</html>
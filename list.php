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
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include "modules/nav.php"; ?>

    <?php 
        $query = $datubaze->prepare('
            SELECT *
            FROM saraksts
            WHERE id = ?
        ');
        $query->bind_param('i',$_GET['id']);
        $query->execute();
        $result = $query->get_result();
        $saraksts = $result->fetch_object();

        $query2 = $datubaze->prepare('
            SELECT *
            FROM ieraksts
            WHERE saraksts_id = ?
        ');

        if( empty($_GET['id']) || $result->num_rows == 0):
            include "modules/not_found.php";
        elseif( $_SESSION['username'] != $saraksts->lietotajvards):
            include "modules/forbidden.php";
        else:  
    ?>
        <div class="container mt-5">
            <h1 class="text-center"><?php echo htmlspecialchars($saraksts->nosaukums) ?></h1>
            <div class="row">
                <div class="col-md-6 offset-md-3">
                    <div class="input-group mb-3">
                        <input type="text" id="task" class="form-control" placeholder="Add a new task">
                        <button id="addTask" class="btn btn-primary">Add</button>
                    </div>
                    <ul class="list-group" id="taskList">
                        <!-- Saraksta elementi tiks dinamiski ievietoti šeit -->

                        <!-- Piemērs vienam ierakstam  -->
                        <!-- <li class="list-group-item">Nopirkt ballītei balonus</li> -->
                        <?php 
                            $query2->bind_param('i',$saraksts->id);
                            $query2->execute();
                            $ieraksti = $query2->get_result();
                            while($ieraksts = $ieraksti->fetch_object()){
                                $klase = "text-decoration-line-through";
                                $klase = ($ieraksts->izsvitrots == 1) ? $klase : '';
                                echo "<li class=\"list-group-item $klase\">" . htmlspecialchars($ieraksts->teksts) . "</li>";
                            }
                        ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?>

</body>
</html>
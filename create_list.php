<?php
    session_start();
    require_once "db/connection.php";

    // ja lietotājs nav autorizējies, tad pārvirzām uz autorizācijas lapu
    if(empty($_SESSION['username'])){
        header("Location: login.php");
    }

    if(isset($_POST['create'])){
        // ievietojam jaunu rindu tabulā `saraksts`
        $query = $datubaze->prepare('
            INSERT INTO saraksts(nosaukums,lietotajvards)
            VALUES (?,?)
        ');
        $query->bind_param('ss',$_POST['listname'],$_SESSION['username']);
        $query->execute();

        // pārvirzām uz saraksta lapu.  Papildus nododot jaunizveidotā saraksta id kā GET parametru
        header('Location: list.php?id=' . $query->insert_id);
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
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Jauns saraksts</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <?php
                                if(isset($error)){
                                    echo "
                                    <div class='alert alert-warning' role='alert'>
                                        $error
                                    </div>";
                                }
                            ?>
                            <div class="mb-3">
                                <label for="listname" class="form-label">Saraksta nosaukums</label>
                                <input type="text" class="form-control" id="listname" name="listname" required>
                            </div>
                            <button type="submit" class="btn btn-primary" name="create">Izveidot</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
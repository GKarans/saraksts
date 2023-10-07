<?php 
    session_start();
    require_once "connection.php";
    if(!isset($_SESSION['username'])){
        header("Location: login.php");
    }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mani saraksti</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include "modules/nav.php"; ?>

    <div class="container mt-4">
        <div class="row">
            <?php 
                $query = $datubaze->prepare('
                    SELECT *
                    FROM saraksts
                    WHERE lietotajvards = ?
                ');
                $query->bind_param('s', $_SESSION['username']);
                $query->execute();
                $saraksti = $query->get_result();

                $query2 = $datubaze->prepare('
                    SELECT *
                    FROM ieraksts
                    WHERE saraksts_id = ? LIMIT 5
                ');

                while($saraksts = $saraksti->fetch_object()):
            ?>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($saraksts->nosaukums); ?></h5>
                    <p class="card-text">
                        <?php 
                            $query2->bind_param('i',$saraksts->id);
                            $query2->execute();
                            $ieraksti = $query2->get_result();
                            while($ieraksts = $ieraksti->fetch_object()){
                                $klase = "class=\"text-decoration-line-through\"";
                                $klase = ($ieraksts->izsvitrots == 1) ? $klase : '';
                                echo "<span " . $klase . ">" .  htmlspecialchars($ieraksts->teksts) . "</span><br>";
                            }
                        ?>
                    </p>
                    <a href="list.php?id=<?php echo  htmlspecialchars($saraksts->id) ?>" class="btn btn-primary">Apskatīt</a>
                </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        <div class="fixed-bottom m-3 d-grid gap-2 d-md-flex justify-content-md-end">
            <a class="btn btn-primary me-md-2" href="create_list.php" >+Jauns saraksts</a>
        </div>
    </div>
</body>
</html>
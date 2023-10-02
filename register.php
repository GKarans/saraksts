<?php
require_once "connection.php";
    if(isset($_POST['register'])){

        $query = $datubaze->prepare("
            SELECT lietotajvards
            FROM lietotajs
            WHERE lietotajvards = ?
        ");
        $query->bind_param('s', $_POST['username']);
        $query->execute();
        $result = $query->get_result();

        if($result->num_rows != 0){
            #lietotajs jau eksistē
            $error = "Lietotājvārds jau ir aizņemts";
        }elseif($_POST['password' !== $_POST['comfirm_password']] ){
            $error = "Paroles nesakrīt";
        }else{

            $password = password_hash($_POST['password'], PASSWORD_ARGON2I);

        $query = $datubaze->prepare("
            INSERT INTO lietotajs(lietotajvards, parole, epasts, tel_nr, loma)
            VALUES (?,?,?,?, 'lietotajs')
        ");
        $query->bind_param('ssss', $_POST['username'], $password, $_POST['email'], $_POST['phone']);
        $query->execute();
      }
    }
    

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reģistrācijas forma</title>
    <!-- Add Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Reģistrēties</h4>
                    </div>
                    <div class="card-body">
                        <form method = "POST">
                            <?php
                                 if(isset($error)){
                                    echo "
                                    <div></div>
                                    ";
                                 }
                            ?>
                            <div class="mb-3">
                                <label for="username" class="form-label">Lietotājvārds</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Ēpasts</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Telefona nr.</label>
                                <input type="tel" class="form-control" id="phone" name="phone">
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Parole</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Parole (atkārtoti)</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <button type="submit" class="btn btn-primary"
                            name="register">Register</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</body>
</html>

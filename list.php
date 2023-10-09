<?php
    session_start();
    include_once "db/connection.php";

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
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include "modules/nav.php"; ?>

    <?php 
        // Atrodam sarakstu, kuru lietotājs vēlas apskatīt
        $query = $datubaze->prepare('
            SELECT *
            FROM saraksts
            WHERE id = ?
        ');
        $query->bind_param('i',$_GET['id']);
        $query->execute();
        $result = $query->get_result();
        $saraksts = $result->fetch_object();

        // Atrodam saraksta ierakstus
        $query2 = $datubaze->prepare('
            SELECT *
            FROM ieraksts
            WHERE saraksts_id = ?
        ');

        // ja saraksts neeksistē vai lietotājs nav saraksta īpašnieks, tad izvadam atbilstošu ziņojumu
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
                        <input type="text" id="task" class="form-control" placeholder="Jauns ieraksts">
                        <button id="addTask" class="btn btn-primary">Pievienot</button>
                    </div>
                    <ul class="list-group" id="taskList">
                        <!-- Saraksta elementi tiks dinamiski ievietoti šeit -->

                        <!-- Piemērs vienam ierakstam  -->
                        <!-- <li class="list-group-item">Nopirkt ballītei balonus</li> -->
    <?php 
    $query2->bind_param('i',$saraksts->id);
    $query2->execute();
    $ieraksti = $query2->get_result();
    while($ieraksts = $ieraksti->fetch_object()):
        $klase = "text-decoration-line-through";
        $klase = ($ieraksts->izsvitrots == 1) ? $klase : '';
    ?>
    <li class="list-group-item d-flex justify-content-between align-items-center" data-id="<?php echo $ieraksts->id?>">
        <span class="me-auto <?php echo $klase; ?>"><?php echo htmlspecialchars($ieraksts->teksts); ?></span>
        <button class="btn me-2 btn-outline-primary" data-id="<?php echo $ieraksts->id?>">rediģēt</button>
        <button class="btn btn-outline-danger" data-id="<?php echo $ieraksts->id?>">X</button>
    </li>
    <?php endwhile; ?>
                    </ul>
                </div>
            </div>
        </div>
        <script>
            // saglabājam aktuālā saraksta ID kā javascript mainīgo. 
            // Tas palīdzēs ievietot jaunus ierakstus un noteikt saraksta autoru
            const saraksts_id = <?php echo $_GET['id']; ?>; 

            let teksts = document.getElementById("task");
            let poga = document.getElementById("addTask");
            let saraksts = document.getElementById("taskList");

            function ievietotSarakstā(){
                if(teksts.value.trim() != ""){
                    /** 
                     * AJAX – Asynchronous JavaScript And XML.
                     * https://www.w3schools.com/xml/ajax_intro.asp
                     * .ajax() izveido HTTP-request pieprasījumu, un gaida atbildi no servera 
                     */ 
                    $.ajax({ 
                        type:'POST',
                        url: 'db/list_item/insert.php',
                        data: {
                            teksts: teksts.value,
                            saraksts_id: saraksts_id,
                        },
                        dataType: 'json',
                        encode: true,
                    }).done(function (data) { // reaģējam uz servera atbildi
                        console.log(data);

                        if(data.response == '200'){
                            // konstruējam <li> elementu
                            let li = document.createElement("li");
                            li.classList.add(
                                "list-group-item",
                                "d-flex",
                                "justify-content-between",
                                "align-items-center"
                            );
                            li.innerHTML = '<span class="me-auto"> '+ teksts.value +' </span><button class="btn me-2 btn-outline-primary" data-id="' + data.id + '">rediģēt</button><button class="btn btn-outline-danger" data-id="' + data.id + '">X</button>';
                            li.setAttribute('data-id', data.id);
                            teksts.value = '';

                            saraksts.appendChild(li);
                        }
                    });
                }
            }

            poga.addEventListener("click", ievietotSarakstā );
            teksts.addEventListener("keypress", function(e){
                if(e.key == "Enter"){
                    ievietotSarakstā();
                }
            });
            
            // "Event delegation"
            // ne visi saraksta elementi ir pieejami, kad mēs ielādējam lapu, līdz ar to nepietiek ar to, ka uzstādam event listener dokumenta ielādēšanās brīdī
            // Izmantojot jQuery, deliģējam document objektu klausīties klikšķi uz kādu saraksta elementu
            // https://learn.jquery.com/events/event-delegation/
            $(document).on('click', '#taskList li span', function(){
                $(this).toggleClass("text-decoration-line-through");

                $.ajax({
                    type:'POST',
                    url: 'db/list_item/toggle.php',
                    data: {
                        ieraksts_id: $(this).attr('data-id'),
                        saraksts_id: saraksts_id,
                    },
                    dataType: 'json',
                    encode: true,
                }).done(function (data) {
                    console.log(data);
                });

            });

            /*
             * Ieraksta dzēšana. 
             */
            $(document).on('click', '#taskList li .btn-outline-danger', function(){

                const ieraksts = $(this);
                $.ajax({
                    type:'POST',
                    url: 'db/list_item/delete.php',
                    data: {
                        ieraksts_id: ieraksts.attr('data-id'),
                        saraksts_id: saraksts_id,
                    },
                    dataType: 'json',
                    encode: true,
                }).done(function (data) {
                    console.log(data);
                    ieraksts.parent().remove();
                });

            });

            /*
             * Ieraksta rediģēšana – ievades lauka parādīšana.
             * Ieraksta teksts tiek aizvietots ar input lauku, kurā var rediģēt tekstu
             */
            $(document).on('click','#taskList li .btn-outline-primary', function(){
                const teksts = $(this).siblings("span").text();
                const id = $(this).attr('data-id');
                $(this).attr('disabled',true);// neļaujam lietotājam klikšķināt uz "rediģēt" atkārtoti

                $(this).siblings("span").html('<div class="input-group"><input type="text" class="form-control" value="'+ teksts +'"><button class="btn btn-primary" data-id="'+ id +'">Saglabāt</button></div>');

            });

            /*
             * Izsvītrošanas funkcionalitātes atcelšana elementiem dziļāk par <span> elementu
             */
            $(document).on('click','#taskList li span * ', function( event ){
                event.stopPropagation();
            });

            /*
             * Rediģētā ieraksta saglabāšana.
             * Nododam serverim tekstu, kuru ievadīja lietotājs, ieraksta id un saraksta id
             * Sagaidot atbildi no servera, aizvietojam input lauku ar jauno tekstu
             */
            $(document).on('click','#taskList li span .btn-primary', function(){
                const text = $(this).siblings("input").val();
                const ievade = $(this);
                
                $.ajax({
                    type:'POST',
                    url: 'db/list_item/update.php',
                    data: {
                        ieraksts_id: ievade.attr('data-id'),
                        saraksts_id: saraksts_id,
                        text: text,
                    },
                    dataType: 'json',
                    encode: true,
                }).done(function (data) {
                    console.log(data);

                    // atgriežam pogai "rediģēt" iespēju tikt klikšķinātai
                    ievade.parents('li').children('.btn-outline-primary').attr('disabled', false);
                    
                    ievade.parents('span').text(data.text);
                });
            });

        </script>
    <?php endif; ?>

</body>
</html>
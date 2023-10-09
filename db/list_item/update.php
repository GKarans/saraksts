<?php
session_start();
include_once '../connection.php';

// Atgriežamā vērtība (asociatīvs masīvs)
$message = [];

// Atrodam sarakstu, kuram pieder ieraksts, lai salīdzinātu lietotājvārdus
$query = $datubaze->prepare('
SELECT *
FROM saraksts
WHERE id = ?
');
$query->bind_param('i',$_POST['saraksts_id']);
$query->execute();
$result = $query->get_result();
$saraksts = $result->fetch_object();

// HTTP-request var tikt izsaukts no jebkura URL, tāpēc ir svarīgi pārbaudīt, vai lietotājs ir autorizējies un vai viņš ir saraksta īpašnieks
if( empty($_POST['saraksts_id']) || empty($_POST['text']) || $result->num_rows == 0){
    $message['response'] = '404'; // not found
}elseif( $_SESSION['username'] != $saraksts->lietotajvards){
    $message['response'] = '403'; // forbidden
}else{
    // Atrodam ierakstu. Ja tas ir izsvītrots, tad atsvītrojam, ja nav, tad izsvītrojam
    $query = $datubaze->prepare('
        UPDATE ieraksts SET teksts = ?
        WHERE id = ? AND saraksts_id = ?
    ');
    $query->bind_param('sii', $_POST['text'], $_POST['ieraksts_id'], $_POST['saraksts_id']);
    $query->execute();
    $message['text'] = htmlspecialchars($_POST['text']);
    $message['response'] = '200'; // OK
}

// Atgriežam vērtību kā JSON tekstu, lai to varētu nolasīt ar JavaScript
echo json_encode($message);
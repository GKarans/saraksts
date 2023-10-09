<?php
session_start();
include_once '../connection.php';

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
if( empty($_POST['saraksts_id']) || $result->num_rows == 0){
    $message['response'] = '404'; // not found
}elseif( $_SESSION['username'] != $saraksts->lietotajvards){
    $message['response'] = '403'; // forbidden
}else{
    // ievietojam jaunu rindu tabulā `ieraksts`
    $query = $datubaze->prepare('
        INSERT INTO ieraksts(teksts,saraksts_id)
        VALUES (?,?)
    ');
    $query->bind_param('si', $_POST['teksts'], $_POST['saraksts_id']);
    $query->execute();
    $message['response'] = '200'; // OK
    $message['id'] = $query->insert_id;
}

// Atgriežam vērtību kā JSON tekstu, lai to varētu nolasīt ar JavaScript
echo json_encode($message);
<?php
require_once("config.php");
$insertMsg = "";
// Handle any inserts/updates/deletes before outputting any HTML
// INSERT 
if (isset($_POST["insert"]) && !empty($_POST["insert_data"])) {
    $dataToInsert = htmlspecialchars($_POST["insert_data"]);
    //echo "inserting $dataToInsert ...";

    $db = get_pdo_connection();
    $query = $db->prepare("insert into hello (data) values (?)");
    $query->bindParam(1, $dataToInsert, PDO::PARAM_STR);
    if (!$query->execute()) {    
        $insertMsg =  "Error executing insert query:<br>" . print_r($query->errorInfo(), true);
    }
    else {
        $insertMsg = "Inserted " . $query->rowCount() . " rows";
    }
    unset($_POST["insert"]);
}

?>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= $PROJECT_NAME ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1><?= $PROJECT_NAME ?></h1>
    <?php include("nav.php"); ?>
    <h2>SQL INSERT using input from form</h2>
<?php
if (!empty($insertMsg)) {
    echo "$insertMsg<br>";
    $insertMsg = "";
}
$insert_form = new PhpFormBuilder();
$insert_form->set_att("method", "POST");
$insert_form->add_input("data to insert", array(
    "type" => "text"
), "insert_data");
$insert_form->add_input("Insert", array(
    "type" => "submit",
    "value" => "Insert"
), "insert");
$insert_form->build_form();
?>
</body>
</html>
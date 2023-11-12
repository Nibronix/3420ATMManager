<?php
require_once("config.php");

$updateMsg = "";
// Handle any inserts/updates/deletes before outputting any HTML
// UPDATE
if (isset($_POST["update"]) 
    && !empty($_POST["update_data"])
    && !empty($_POST["update_id"])) {
    $dataToUpdate = htmlspecialchars($_POST["update_data"]);
    $idToUpdate = htmlspecialchars($_POST["update_id"]);
    // "updating $dataToUpdate ...";

    $db = get_pdo_connection();
    $query = $db->prepare("update hello set data= ? where id = ?");
    $query->bindParam(1, $dataToUpdate, PDO::PARAM_STR);
    $query->bindParam(2, $idToUpdate, PDO::PARAM_INT);
    if (!$query->execute()) {    
        //header( "Location: " . $_SERVER['PHP_SELF']);
        $updateMsg = "Error executing update query:<br>" . print_r($query->errorInfo(), true);
    }
    else {
        $updateMsg = "Updated " . $query->rowCount() . " rows";
    }
    unset($_POST["update"]);
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
    <h2>SQL UPDATE using input from form</h2>

<?php
if (!empty($updateMsg)) {
    echo "$updateMsg<br>";
    $updateMsg = "";
}
$update_form = new PhpFormBuilder();
$update_form->set_att("method", "POST");
$update_form->add_input("id to update data for", array(
    "type" => "number"
), "update_id");
$update_form->add_input("data to update", array(
    "type" => "text"
), "update_data");
$update_form->add_input("Update", array(
    "type" => "submit",
    "value" => "Update"
), "update");
$update_form->build_form();

?>
</body>
</html>
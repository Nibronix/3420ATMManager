<?php
require_once("config.php");

$deleteMsg = "";
// Handle any inserts/updates/deletes before outputting any HTML
// DELETE
if (isset($_POST["delete"])) {

    //echo "deleting...<br>";

    $db = get_pdo_connection();
    $query = false;

    if (!empty($_POST["delete_id"])) {
        // "deleting by id...";
        $query = $db->prepare("delete from hello where id = ?");
        $query->bindParam(1, $_POST["delete_id"], PDO::PARAM_INT);
    }
    else if (!empty($_POST["delete_data"])) {
        // "deleting by data...";
        $query = $db->prepare("delete from hello where data = ?");
        $query->bindParam(1, $_POST["delete_data"], PDO::PARAM_STR);
    }
    if ($query) {
        if ($query->execute()) {
            $deleteMsg = "Deleted " . $query->rowCount() . " rows";
        }
        else {
            $deleteMsg = print_r($query->errorInfo(), true);
        }
        
    }
    else{
        $deleteMsg = "Unable to delete: no id or data specified<br>";
    }
    unset($_POST["delete"]);
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
    <h2>SQL DELETE using input from form</h2>

<?php
if (!empty($deleteMsg)) {
    echo $deleteMsg;
    $deleteMsg = "";
}
$delete_form = new PhpFormBuilder();
$delete_form->set_att("method", "POST");
$delete_form->add_input("id to delete for", array(
    "type" => "number"
), "delete_id");
$delete_form->add_input("data to delete", array(
    "type" => "text"
), "delete_data");
$delete_form->add_input("Delete", array(
    "type" => "submit",
    "value" => "Delete"
), "delete");
$delete_form->build_form();
?>
</body>
</html>
<?php
require_once __DIR__ . '/../database.php';
$db = Database::getInstance()->getConnection();
$result = $db->query("SELECT count(id_association) as total FROM association");
$row = $result->fetch_assoc();
$countAssociation = $row['total'];
?>


<div class="rounded rounded-3 bg-primary col-2 text-white text-center">
    <h3><?php echo $countAssociation ?></h3>
    <p>Associations</p>
</div>
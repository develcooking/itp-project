<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/controllers/login.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/views/header.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/models/Job.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/models/User.php";

$job = new Job($conn);
$allBerufsbereiche = $job->getAll();
$user = new User($conn);


if (empty($allBerufsbereiche)) {
    echo "Keine Berufsbereiche angelegt";
}
?>

<table>
    <tr>
        <th>
            Name
        </th>
        <th>
            Erstellt von
        </th>
        <th>
            Modifiziert von
        </th>
    </tr>

    <?php foreach ($allBerufsbereiche as $berufsbereich) : ?>
    <tr>
        <td>
            <?= $berufsbereich['name'] ?>
        </td>
        <td>
            <?= $user->getUserNameByID($berufsbereich['createdBy']) ?>
        </td>
        <td>
            <?= $user->getUserNameByID($berufsbereich['modifiedBy']) ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<?php
include $_SERVER['DOCUMENT_ROOT'] . "/database/db.php";
include $homepath . "/views/header.php";
?>
<form id="createEvent" method="post" action="../controllers/createEvent.php">
    <p>Erstelle Termin</p>
    <input type="title" name="title" required placeholder="Name des Termins">
    <input type="date" name="date1" required placeholder="Erstes Datum">
    <input type="time" name="time1" required placeholder="erste Uhrzeit">
    <input type="date" name="date2" required placeholder="zweites Datum">
    <input type="time" name="time2" required placeholder="zweites Uhrzeit">
    <input type="text" name="description" placeholder="In der Hautstraße">
    <select name="jobselection" required>
        <option value="" disabled selected>Bitte Berufsbereich auswählen</option>
        <?php
            #TODO Add selection for every job by id sorted by name create sql query
            #Waiting for #48 to be merged
            #<option value="<<BERUFSBEREICH id>>">BERUFSBEREICH TITLE</option>
        ?>
        <!-- Beispiel: DAS SOLLTE ABER NUR zum testen da sein-->
        <option value="1">Informatik</option>

    </select>
    <button class="submitbtn" type="submit" name="createEvent">Create Event</button>
</form>
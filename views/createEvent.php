<form id="createEvent" method="post" action="../controllers/createEvent.php">
    <p>Erstelle Termin</p>
    <input type="title" name="title" required placeholder="Name des Termins">
    <input type="date" name="date1" required placeholder="Erstes Datum">
    <input type="time" name="time1" required placeholder="erste Uhrzeit">
    <input type="date" name="date2" required placeholder="zweites Datum">
    <input type="time" name="time2" required placeholder="zweites Uhrzeit">
    <select name="jobselection" required>
        <option value="" disabled selected>Bitte Berufsbereich auswählen</option>
        <?php
            #TODO Add selection for every job by id sorted by name create sql query 
            #<option value="<<BERUFSBEREICH id>>">BERUFSBEREICH TITLE</option>
        ?>
        <!-- Beispiel: DAS SOLLTE ABER NUR zum testen da sein-->
        <option value="1">Informatik</option>

    </select>
    <button class="submitbtn" type="submit" name="createAccount">Create Account</button>
</form>
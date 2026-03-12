function changeSubBtnStatus(status) {
    if (status) {
        const modal_submit_btn = document.getElementById('modal_submit_btn');
        if (status === true) {
            if (modal_submit_btn.classList.contains('disabled')) {
                modal_submit_btn.classList.remove('disabled');
            }
            modal_submit_btn.setAttribute("aria-disabled", "false");
        } else {
            if (!modal_submit_btn.classList.contains('disabled')) {
                modal_submit_btn.classList.add('disabled');
            }
            modal_submit_btn.setAttribute("aria-disabled", "true");
        }
    }
}

function validateDateTime() {
    const startdate = document.getElementById('startdate').value;
    const starttime = document.getElementById('starttime').value;
    const enddate = document.getElementById('enddate').value;
    const endtime = document.getElementById('endtime').value;
    const errorBox = document.getElementById("dateError");

    if (!startdate || !starttime || !enddate || !endtime) {
        errorBox.classList.add("d-none");
        changeSubBtnStatus(false);
        return;
    }

    const start = new Date(`${startdate}T${starttime}`);
    const end = new Date(`${enddate}T${endtime}`);

    if (start >= end) {
        errorBox.innerText = "Das Enddatum muss nach dem Startdatum liegen!";
        errorBox.classList.remove("d-none");
        changeSubBtnStatus(false);
    } else {
        errorBox.classList.add("d-none");
        changeSubBtnStatus(true);
    }
}
function splitdateForChangeModle(datetimestring) {
    const date = new Date(datetimestring);

// Formatierung der Date-Objekte
    const yyyyMmDd = date.toISOString().split('T')[0]; // '2014-01-12'
    const hhMm = date.toISOString().split('T')[1].substring(0, 5); // '05:00'
    return [yyyyMmDd, hhMm]
}

function updateDateLimits() {
    const startdate = document.getElementById('startdate');
    const enddate = document.getElementById('enddate');

    // Enddatum darf nicht vor Startdatum liegen
    enddate.min = startdate.value;

    // Startdatum darf beliebig bleiben
    // startdate.max entfernen oder ignorieren
    startdate.removeAttribute('max');
}

document.addEventListener('DOMContentLoaded', function () {

    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
        themeSystem: 'bootstrap5',
        initialView: 'dayGridMonth',
        locale: 'de',
        firstDay: 1,

        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
        },
        buttonText: {
            today: 'Heute',
            month: 'Monat',
            week: 'Woche',
            day: 'Tag',
            list: 'Liste'
        },
        
        // calling events from getEvents.php
        events: '../controllers/getEvents.php',



        dateClick: function (info) {

            let clickedDate = new Date(info.dateStr);
            let today = new Date();
            today.setHours(0,0,0,0);

            if (clickedDate < today) {
                return;
            }

            var myModal = new bootstrap.Modal(document.getElementById("calendermanagementModal"));
 
            // set the startdate and enddate to the user clicked date
            let startdate = document.getElementById('startdate');
            let enddate = document.getElementById('enddate');
            startdate.value = info.dateStr.substring(0,10);
            enddate.value = info.dateStr.substring(0,10);

            // Initial limits setzen
            updateDateLimits();

            // Submit-Button deaktivieren, bis validiert
            changeSubBtnStatus(false);

            myModal.show();
        },

        eventClick: function(info) {
            var mychangeModal = new bootstrap.Modal(document.getElementById("calenderChageModal"));
            let changestartdate = document.getElementById('changestartdate');
            let changestarttime = document.getElementById('changestarttime');
            let changeenddate = document.getElementById('changeenddate');
            let changeendtime = document.getElementById('changeendtime');
            let changetitle = document.getElementById('changetitle');
            let changedescription = document.getElementById('changedescription');
            let changejobselection = document.getElementById('changejobselection');
            let changeappointmentId = document.getElementById('changeappointmentId');

            // Split start and end date and time
            let starttimearray = splitdateForChangeModle(info.event.start);
            changestartdate.value = starttimearray[0];
            changestarttime.value = starttimearray[1];
            let endtimearray = splitdateForChangeModle(info.event.end);
            changeenddate.value = endtimearray[0];
            changeendtime.value = endtimearray[1];

            // Set the title and description
            changetitle.value = info.event.title;
            changedescription.value = info.event.extendedProps.description;

            // Set the job selection dropdown to the selected jobId
            changejobselection.value = info.event.extendedProps.jobId;
            changeappointmentId.value = info.event.id;
            console.log(info.event.id);

            mychangeModal.show();
        }
    });

    calendar.render();

    // Listener für Datumsänderungen
    const startdateEl = document.getElementById('startdate');
    const enddateEl = document.getElementById('enddate');
    const starttimeEl = document.getElementById('starttime');
    const endtimeEl = document.getElementById('endtime');

    startdateEl.addEventListener('change', function() {
        updateDateLimits();
        validateDateTime();
    });

    enddateEl.addEventListener('change', function() {
        updateDateLimits();
        validateDateTime();
    });

    starttimeEl.addEventListener('change', validateDateTime);
    endtimeEl.addEventListener('change', validateDateTime);

});
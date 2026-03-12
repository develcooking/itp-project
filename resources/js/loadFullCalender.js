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
function splitdateForChangeModle(datetimestring) {
    const date = new Date(datetimestring);

// Formatierung der Date-Objekte
    const yyyyMmDd = date.toISOString().split('T')[0]; // '2014-01-12'
    const hhMm = date.toISOString().split('T')[1].substring(0, 5); // '05:00'
    return [yyyyMmDd, hhMm]
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
            var myModal = new bootstrap.Modal(document.getElementById("calendermanagementModal"));

            // set the startdate and enddate to the user clicked date
            let startdate= document.getElementById('startdate');
            let enddate= document.getElementById('enddate');
            startdate.value = info.dateStr;
            enddate.value = info.dateStr;

            // check for right format
            const modal_submit_btn = document.getElementById('modal_submit_btn');
            startdate.addEventListener("change", (event) => {
                result.textContent = `You like ${event.target.value}`;
            });
            //alert('Termin\n Title: ' + info.event.title + '\n Beschreibung: ' + info.event.extendedProps.description + '\n Beginn: ' + info.event.start + '\n Ende: ' + info.event.end);
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
});
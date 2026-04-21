function changeSubBtnStatus(status) {
    const modal_submit_btn = document.getElementById('modal_submit_btn');
    if (!modal_submit_btn) return;

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
function validateXEntys() {
    const recurrence_interval = document.getElementById('recurrence_interval');
    if (recurrence_interval) {
        recurrence_interval.addEventListener("change", validateForm);
        recurrence_interval.addEventListener("input", validateForm);
    }
}

function validateDateTime() {
    validateForm();
}

function validateForm() {
    const startdateEl = document.getElementById('startdate');
    if (!startdateEl) return;

    const startdate = startdateEl.value;
    const starttime = document.getElementById('starttime').value;
    const enddate = document.getElementById('enddate').value;
    const endtime = document.getElementById('endtime').value;
    const recurrence_interval = document.getElementById('recurrence_interval');
    const errorBox = document.getElementById("dateError");

    if (errorBox) errorBox.classList.add("d-none");

    if (!startdate || !starttime || !enddate || !endtime) {
        changeSubBtnStatus(false);
        return;
    }

    const start = new Date(`${startdate}T${starttime}`);
    const end = new Date(`${enddate}T${endtime}`);

    if (start >= end) {
        if (errorBox) {
            errorBox.innerText = "Das Enddatum muss nach dem Startdatum liegen!";
            errorBox.classList.remove("d-none");
        }
        changeSubBtnStatus(false);
        return;
    }

    if (recurrence_interval) {
        const val = parseInt(recurrence_interval.value);
        if (val > 24) {
            if (errorBox) {
                errorBox.innerText = "Alle X Wochen/ Monate darf nicht höher als 24 sein!";
                errorBox.classList.remove("d-none");
            }
            changeSubBtnStatus(false);
            return;
        }
    }

    changeSubBtnStatus(true);
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

    if (startdate && enddate) {
        // Enddatum darf nicht vor Startdatum liegen
        enddate.min = startdate.value;

        // Startdatum darf beliebig bleiben
        // startdate.max entfernen oder ignorieren
        startdate.removeAttribute('max');
    }
}

function disableChangeModal(creatorName) {
    const form = document.querySelector('#calenderChageModal form');
    if (!form) return;
    const inputs = form.querySelectorAll('input, select, textarea');
    const changeModalTitle = document.getElementById('changeModalTitle');
    if (changeModalTitle) changeModalTitle.style.display = "none";
    const calenderChageModalLabel = document.getElementById('calenderChageModalLabel');
    if (calenderChageModalLabel) calenderChageModalLabel.innerText = 'Infos zum Termin';

    inputs.forEach(el => {
        el.disabled = true;
    });

    const footerButtons = document.getElementById("modalFooterChangeButtons");
    if (footerButtons) footerButtons.style.display = "none";
    const footerCreatedBy = document.getElementById("modalFooterCreatedBy");
    if (footerCreatedBy) footerCreatedBy.style.display = "block";
    const footerInput = document.getElementById("modalFooterCreatedByInput");
    if (footerInput) footerInput.value = "Erstellt von: " + (creatorName || "Unbekannt");
}

function enableChangeModal() {
    const form = document.querySelector('#calenderChageModal form');
    if (!form) return;
    const inputs = form.querySelectorAll('input, select, textarea');
    const changeModalTitle = document.getElementById('changeModalTitle');
    if (changeModalTitle) changeModalTitle.style.display = "block";
    const calenderChageModalLabel = document.getElementById('calenderChageModalLabel');
    if (calenderChageModalLabel) calenderChageModalLabel.innerText = 'Termin ändern';

    inputs.forEach(el => {
        el.disabled = false;
    });

    const footerButtons = document.getElementById("modalFooterChangeButtons");
    if (footerButtons) footerButtons.style.display = "block";
    const footerCreatedBy = document.getElementById("modalFooterCreatedBy");
    if (footerCreatedBy) footerCreatedBy.style.display = "none";
}

document.addEventListener('DOMContentLoaded', function () {
    const istausbilder = document.getElementById('calendermanagementModal') !== null;

    var calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    var calendarOptions = {
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
        events: {
            url: '../controllers/getEvents.php',
            extraParams: function() {
                const filterJob = document.getElementById('filterJob');
                const filterStartDate = document.getElementById('filterStartDate');
                const filterEndDate = document.getElementById('filterEndDate');
                return {
                    jobId: filterJob ? filterJob.value : '',
                    filterStart: filterStartDate ? filterStartDate.value : '',
                    filterEnd: filterEndDate ? filterEndDate.value : ''
                };
            }
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
            
            // Recurrence fields
            let changerecurrence_type = document.getElementById('changerecurrence_type');
            let changerecurrence_interval = document.getElementById('changerecurrence_interval');
            let changerecurrence_until = document.getElementById('changerecurrence_until');

            // Split start and end date and time
            let starttimearray = splitdateForChangeModle(info.event.start);
            changestartdate.value = starttimearray[0];
            changestarttime.value = starttimearray[1];
            
            if (info.event.end) {
                let endtimearray = splitdateForChangeModle(info.event.end);
                changeenddate.value = endtimearray[0];
                changeendtime.value = endtimearray[1];
            } else {
                // Fallback if no end date
                changeenddate.value = starttimearray[0];
                changeendtime.value = starttimearray[1];
            }

            // Set the title and description
            changetitle.value = info.event.title;
            changedescription.value = info.event.extendedProps.description || '';

            // Set the job selection dropdown to the selected jobId
            changejobselection.value = info.event.extendedProps.jobId;
            changeappointmentId.value = info.event.extendedProps.appointmentId;
            
            // Set recurrence fields
            if (changerecurrence_type) changerecurrence_type.value = info.event.extendedProps.recurrenceType || 'none';
            if (changerecurrence_interval) changerecurrence_interval.value = info.event.extendedProps.recurrenceInterval || 1;
            if (changerecurrence_until) changerecurrence_until.value = info.event.extendedProps.recurrenceUntil || '';

            let createdBy = info.event.extendedProps.createdBy;
            let creatorName = info.event.extendedProps.creatorName;

            if (createdBy != currentUserId && currentUserRole !== "admin") {
                disableChangeModal(creatorName);
            } else {
                enableChangeModal();
            }

            mychangeModal.show();
        }
    };

    if (istausbilder) {
        calendarOptions.dateClick = function (info) {
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
            if (startdate) startdate.value = info.dateStr.substring(0,10);
            if (enddate) enddate.value = info.dateStr.substring(0,10);

            // Initial limits setzen
            updateDateLimits();

            // Submit-Button deaktivieren, bis validiert
            changeSubBtnStatus(false);

            myModal.show();
        };
    }

    var calendar = new FullCalendar.Calendar(calendarEl, calendarOptions);
    calendar.render();
    validateXEntys();

    // Filter listeners
    const filterJob = document.getElementById('filterJob');
    const filterStartDate = document.getElementById('filterStartDate');
    const filterEndDate = document.getElementById('filterEndDate');
    const resetFilter = document.getElementById('resetFilter');

    if (filterJob) {
        filterJob.addEventListener('change', function() {
            calendar.refetchEvents();
        });
    }

    if (filterStartDate) {
        filterStartDate.addEventListener('change', function() {
            if (this.value) {
                calendar.gotoDate(this.value);
            }
            calendar.refetchEvents();
        });
    }

    if (filterEndDate) {
        filterEndDate.addEventListener('change', function() {
            calendar.refetchEvents();
        });
    }

    if (resetFilter) {
        resetFilter.addEventListener('click', function() {
            if (filterJob) filterJob.value = "";
            if (filterStartDate) filterStartDate.value = "";
            if (filterEndDate) filterEndDate.value = "";
            calendar.gotoDate(new Date());
            calendar.refetchEvents();
        });
    }

    // Listener für Datumsänderungen
    const startdateEl = document.getElementById('startdate');
    const enddateEl = document.getElementById('enddate');
    const starttimeEl = document.getElementById('starttime');
    const endtimeEl = document.getElementById('endtime');

    if (startdateEl) {
        startdateEl.addEventListener('change', function() {
            updateDateLimits();
            validateDateTime();
        });
    }

    if (enddateEl) {
        enddateEl.addEventListener('change', function() {
            updateDateLimits();
            validateDateTime();
        });
    }

    if (starttimeEl) starttimeEl.addEventListener('change', validateDateTime);
    if (endtimeEl) endtimeEl.addEventListener('change', validateDateTime);

});

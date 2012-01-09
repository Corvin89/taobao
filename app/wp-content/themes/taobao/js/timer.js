var date = new Date();
var hours = clientHoursMinusServerHours(date);
var minutes = clientMinutesMinusServerMinutes(date);
var interval = setInterval("getCurrentTime(date,hours,minutes)", 1000);

function clientHoursMinusServerHours(date) {
    var client = date;
    var clientHours = client.getHours();
    clientHours = parseInt(clientHours.toString());
    var differenceHours = serverHours - clientHours;
    return differenceHours;
}

function clientMinutesMinusServerMinutes(date) {
    var client = date;
    var clientMinutes = client.getMinutes();
    clientMinutes = parseInt(clientMinutes.toString());
    var differenceMinutes = serverMinutes - clientMinutes;
    return differenceMinutes;
}

function getCurrentTime(date,differenceHours, differenceMinutes) {
    var client = new Date();
    var clientHours = client.getHours();
    var clientMinutes = client.getMinutes();
    var clientHours = parseInt(clientHours.toString());
    var clientMinutes = parseInt(clientMinutes.toString());
    var newClientHours = clientHours + differenceHours;
    var newClientMinutes = clientMinutes + differenceMinutes;
    timer(newClientHours, newClientMinutes, 10, 19);
}

function appZero(i) {
    if (i < 10) {
        i = "0" + i;
    }
    return i;
}

function timer(newClientHours, newClientMinutes, startOfWork, endOfWork) {
    if ((newClientHours > startOfWork) && (newClientHours < endOfWork)) {
        timeLeft(newClientHours, newClientMinutes, endOfWork);
    } else {
        timeLeft(newClientHours, newClientMinutes, startOfWork);
    }
    function timeLeft(newClientHours, newClientMinutes, hoursTo) {
        if (newClientHours < hoursTo) {
            var hoursLeft = hoursTo - newClientHours - 1;
            var minutesLeft = 59 - newClientMinutes;
            var str = 'До конца рабочего дня осталоь: ';
            var cssClass = 'work';
        } else {
            var hoursLeft = 23 - newClientHours + hoursTo;
            var minutesLeft = 59 - newClientMinutes;
            var str = 'До начала рабочего дня осталоь: ';
            var cssClass = 'suspend';
        }
        minutesLeft = appZero(minutesLeft);
        hoursLeft = appZero(hoursLeft);
        var hours = hoursLeft + ":" + minutesLeft;
        $("#alarm").html(str + hours);
        $("#alarm").attr('class', cssClass);
    }
}
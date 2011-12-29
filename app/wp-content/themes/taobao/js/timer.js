var interval = setInterval("getCurrentTime()", 1000);

function getCurrentTime() {
    var client = new Date();
    var clientHours = parseInt((client.getHours()).toString());
    var clientMinutes = parseInt((client.getMinutes()).toString());
    var differenceHours = serverHours - clientHours;
    var differenceMinutes = serverMinutes - clientMinutes;
    var newClientHours = clientHours + differenceHours;
    var newClientMinutes = clientMinutes + differenceMinutes;
    timer(newClientHours, newClientMinutes, 10, 19);
}

function addZero(i) {
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
    function timeLeft(newClientHours, newClientMinutes, HoursTo) {
        if (newClientHours < HoursTo) {
            var hoursLeft =  HoursTo - newClientHours -1;
            var minutesLeft = 59 - newClientMinutes;
            var str ='До начала рабочего дня осталось: ';
            var clas ='work';
        } else {
            var hoursLeft = 23 - newClientHours + HoursTo;
            var minutesLeft = 59 - newClientMinutes;
            var str ='До конца рабочего дня осталоь: ' ;
            var clas ='suspend';
        }
        minutesLeft = addZero(minutesLeft);
        hoursLeft = addZero(hoursLeft);
        var hours = hoursLeft + ":" + minutesLeft;
        $("#alarm").html(str + hours);
        $("#alarm").addClass('suspend');
    }
}

var hours = ClientHoursMinusServerHours(GetTimeHoursClients());
var minutes = ClientMinutesMinusServerMinutes(GetTimeMinutesClients());
var int = self.setInterval("NewTime(hours,minutes)", 1000);

function GetTimeHoursClients() {
    var client = new Date();
    var clientHours = client.getHours();
    clientHours = parseInt(clientHours.toString());
    return clientHours;
}

function GetTimeMinutesClients() {
    var client = new Date();
    var clientMinutes = client.getMinutes();
    clientMinutes = parseInt(clientMinutes.toString());
    return clientMinutes;
}

function ClientHoursMinusServerHours(clientHours) {
    var differenceHours = difference(clientHours, serverHours);
    return differenceHours;
}

function ClientMinutesMinusServerMinutes(clientMinutes) {
    var differenceMinutes = Difference(clientMinutes, serverMinutes);
    return differenceMinutes;
}

function Difference(client,server) {
    return   server - client;
}

function NewTime(Hours, Minutes) {
    var client = new Date();
    var clientHours = client.getHours();
    var clientMinutes = client.getMinutes();
    var clientHours = parseInt(clientHours.toString());
    var clientMinutes = parseInt(clientMinutes.toString());
    var newClientHours = clientHours + Hours;
    var newClientMinutes = clientMinutes + Minutes;
    newClientHours = CheckTime(newClientHours);
    newClientMinutes = CheckTime(newClientMinutes);
    Timer(newClientHours, newClientMinutes, 10, 19);
}

function CheckTime(i) {
    if (i < 10) {
        i = "0" + i;
    }
    return i;
}

function Timer(newClientHours, newClientMinutes, startOfWork, endOfWork) {
    if ((newClientHours > startOfWork) & (newClientHours < endOfWork)) {
        TimeLeft(newClientHours, newClientMinutes, endOfWork);
    } else {
        TimeLeft(newClientHours, newClientMinutes, startOfWork);
    }
    function TimeLeft(newClientHours, newClientMinutes, HoursTo) {
        if (newClientHours < HoursTo) {
            var hoursLeft = newClientHours - HoursTo + 1;
            var minutesLeft = 59 - newClientMinutes;
            minutesLeft=checkTime(minutesLeft);
            hoursLeft=checkTime(hoursLeft);
            var hours = hoursLeft + ":" + minutesLeft;

            $("#alarm").html('До начала рабочего дня осталось: ' + hours);
            $("#alarm").addClass('work');
        } else {
            var hoursLeft = 23 - newClientHours + HoursTo;
            var minutesLeft = 59 - newClientMinutes;
            minutesLeft=checkTime(minutesLeft);
            hoursLeft=checkTime(hoursLeft);
            var hours = hoursLeft + ":" + minutesLeft;
            $("#alarm").html('До начала рабочего дня осталоь: ' + hours);
            $("#alarm").addClass('suspend');
        }
    }
}

var H = client_Hours_minus_server_Hours(get_time_Hours_clients());
var M = client_Minutes_minus_server_Minutes(get_time_Minutes_clients());
var int = self.setInterval("new_time(H,M)", 1000);

function get_time_Hours_clients() {
    var client = new Date();
    var client_Hours = client.getHours();
    client_Hours = parseInt(client_Hours.toString());
    return client_Hours;
}

function get_time_Minutes_clients() {
    var client = new Date();
    var client_Minutes = client.getMinutes();
    client_Minutes = parseInt(client_Minutes.toString());
    return client_Minutes;
}

function client_Hours_minus_server_Hours(client_Hours) {
    var difference_Hours = difference(client_Hours, server_Hours);
    return difference_Hours;
}

function client_Minutes_minus_server_Minutes(client_Minutes) {
    var difference_Minutes = difference(client_Minutes, server_Minutes);
    return difference_Minutes;
}

function difference(server, client) {
    return client - server;
}
function new_time(Hours, Minutes) {
    var client = new Date();
    var client_Hours = client.getHours();
    var client_Minutes = client.getMinutes();
    var client_Hours = parseInt(client_Hours.toString());
    var client_Minutes = parseInt(client_Minutes.toString());
    var new_client_Hours = client_Hours + Hours;
    var new_client_Minutes = client_Minutes + Minutes;
    new_client_Hours = checkTime(new_client_Hours);
    new_client_Minutes = checkTime(new_client_Minutes);
    timer(new_client_Hours, new_client_Minutes, 10, 19);
}

function checkTime(i) {
    if (i < 10) {
        i = "0" + i;
    }
    return i;
}

function timer(new_client_Hours, new_client_Minutes, start_of_work, end_of_work) {
    if ((new_client_Hours > start_of_work) & (new_client_Hours < end_of_work)) {
        time_left(new_client_Hours, new_client_Minutes, end_of_work);
    } else {
        time_left(new_client_Hours, new_client_Minutes, start_of_work);
    }
    function time_left(new_client_Hours, new_client_Minutes, Hours_to) {
        if (new_client_Hours < Hours_to) {
            var Hours_left = new_client_Hours - Hours_to + 1;
            var Minutes_left = 59 - new_client_Minutes;
            Minutes_left=checkTime(Minutes_left);
            Hours_left=checkTime(Hours_left);
            var hours = Hours_left + ":" + Minutes_left;

            $("#alarm").html('До начала рабочего дня осталось: ' + hours);
            $("#alarm").addClass('work');
        } else {
            var Hours_left = 23 - new_client_Hours + Hours_to;
            var Minutes_left = 59 - new_client_Minutes;
            Minutes_left=checkTime(Minutes_left);
            Hours_left=checkTime(Hours_left);
            var hours = Hours_left + ":" + Minutes_left;
            $("#alarm").html('До начала робочего дня осталоь: ' + hours);
            $("#alarm").addClass('suspend');
        }
    }
}

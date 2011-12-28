var i = 0;
var H = client_Hours_minus_server_Hours();
var M = client_Minutes_minus_server_Minutes();
console.log("H=" + H);
// client_time_minus_server_time0();
var int = self.setInterval("teimerR(H,M)", 1000);


function client_time_minus_server_time0() {
    var server_Hours = parseInt("<?php echo get_Hours(); ?>");
    var server_Minutes = parseInt("<?php echo get_Minutes(); ?>");
    console.log("server" + server_Hours + ":" + server_Minutes);
    var client = new Date();
    var client_Hours = client.getHours();
    var client_Minutes = client.getMinutes();
    console.log("client" + client_Hours + ":" + client_Minutes);
    client_Hours = parseInt(client_Hours.toString());
    client_Minutes = parseInt(client_Minutes.toString());
    var difference_Hours = difference(client_Hours, server_Hours);
    var difference_Minutes = difference(client_Minutes, server_Minutes);
    console.log("difference" + difference_Hours + ":" + difference_Minutes);

}
function client_Hours_minus_server_Hours() {
    var server_Hours = parseInt("<?php echo get_Hours(); ?>");
    var client = new Date();
    var client_Hours = client.getHours();
    client_Hours = parseInt(client_Hours.toString());
    var difference_Hours = difference(client_Hours, server_Hours);
    return difference_Hours;

}
function client_Minutes_minus_server_Minutes() {

    var server_Minutes = parseInt("<?php echo get_Minutes(); ?>");
    var client = new Date();
    var client_Minutes = client.getMinutes();
    client_Minutes = parseInt(client_Minutes.toString());
    var difference_Minutes = difference(client_Minutes, server_Minutes);
    return difference_Minutes;

}

function difference(server, client) {
    return client - server;
}
function teimerR(Hours, Minutes) {
    console.log("Hours,Minutes=" + Hours, Minutes);
    var client = new Date();
    var client_Hours = client.getHours();
    var client_Minutes = client.getMinutes();
    var client_Hours = parseInt(client_Hours.toString());
    var client_Minutes = parseInt(client_Minutes.toString());
    var new_client_Hours = client_Hours + Hours;
    var new_client_Minutes = client_Minutes + Minutes;
    new_client_Hours = checkTime(new_client_Hours);
    new_client_Minutes = checkTime(new_client_Minutes);

    console.log("client" + new_client_Hours + ":" + new_client_Minutes);

    i = i + 1;
    document.getElementById("clock").value = "--" + i + "--" + new_client_Hours + ":" + new_client_Minutes;
}
function checkTime(i) {
    if (i < 10) {
        i = "0" + i;
    }
    return i;
}


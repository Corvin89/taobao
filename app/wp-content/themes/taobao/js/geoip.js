$.get("http://ipgeobase.ru:7020/geo?ip=195.133.205.114", function(data) {
    var mycity = $(data).find("city").text();
    $("#city").text(mycity);
    return true;
}, "xml")


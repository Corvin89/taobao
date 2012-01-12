$.get("http://ipgeobase.ru:7020/geo?ip="+clientIP.toString(), function(data) {
    var mycity = $(data).find("city").text();
    $("#city").html(mycity);
    return true;
}, "xml")


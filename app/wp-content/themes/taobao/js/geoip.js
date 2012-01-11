$.get("http://ipgeobase.ru:7020/geo?ip=<?=$_SERVER['REMOTE_ADDR']?>", function(data) {
    var mycity = $(data).find("city").text();
    $("#city").text(mycity);
    return true;
}, "xml")


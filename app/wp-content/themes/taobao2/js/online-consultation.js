var liveTex = true,
    liveTexID = 12443,
    liveTex_object = true;
(function() {
    var lt = document.createElement('script');
    lt.type ='text/javascript';
    lt.async = true;
    lt.src = 'http://cs15.livetex.ru/js/client.js';
    var sc = document.getElementsByTagName('script')[0];
    if ( sc ) sc.parentNode.insertBefore(lt, sc);
    else  document.documentElement.firstChild.appendChild(lt);
})();
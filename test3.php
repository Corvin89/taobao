<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; Charset=UTF-8">
    <script>
        function showContent(link) {

            var cont = document.getElementById('contentBody');
            var loading = document.getElementById('loading');

            cont.innerHTML = loading.innerHTML;

            var http = createRequestObject();
            if( http )
            {
                http.open('get', link);
                http.onreadystatechange = function ()
                {
                    if(http.readyState == 4)
                    {
                        cont.innerHTML = http.responseText;
                    }
                }
                http.send(null);
            }
            else
            {
                document.location = link;
            }
        }

        // создание ajax объекта
        function createRequestObject()
        {
            try { return new XMLHttpRequest() }
            catch(e)
            {
                try { return new ActiveXObject('Msxml2.XMLHTTP') }
                catch(e)
                {
                    try { return new ActiveXObject('Microsoft.XMLHTTP') }
                    catch(e) { return null; }
                }
            }
        }
    </script>
</head>

<body>

<p>Какую страницу желаете открыть?</p>

<form>
    <input onclick="showContent('page1.html')" type="button" value="Страница 1">
    <input onclick="showContent('page2.html')" type="button" value="Страница 2">
</form>

<div id="contentBody">
</div>

<div id="loading" style="display: none">
    Идет загрузка...
</div>

</body>
</html>  
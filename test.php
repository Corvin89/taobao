<html>
<head>
    <script type="text/javascript" src="jquery.js"></script>
    <title>setInterval/clearInterval example</title>
    <script type="text/javascript">
        var intervalID;

        function changeColor()
        {
            intervalID = setInterval(flashText, 1000);
        }

        function flashText()
        {
            var elem = document.getElementById("my_box");
            if (elem.style.color == 'red')
            {
                elem.style.color = 'blue';
            }
            else
            {
                elem.style.color = 'red';
            }
        }

        function stopTextColor()
        {
            clearInterval(intervalID);
        }
    </script>
</head>

<body onload="changeColor();">
<div id="my_box">
    <p>Hello World</p>

</div>
<button onclick="stopTextColor();">Stop</button>
</body>
</html>
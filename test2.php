<?php
function plus($a, $b) {
    return $a + $b;
}

function minus($a, $b) {
    $result = $a - $b;
    if($result<0) $result = 0;
    return $result;
}

if(isset($_GET['operation'])) {
    $number = 0;
    switch($_GET['operation'])
    {
        case 'plus':
            $number = plus($_GET['a'], $_GET['b']);
            break;
        case 'minus':
            $number = minus($_GET['a'], $_GET['b']);
            break;
    }
    $result = array(
        'result' => $number
    );
    echo json_encode($result);
    die;
}
?>
<!doctype html>
<html>
<head>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js" type="text/javascript"></script>
    <script type="text/javascript">
        $(function(){
            console.log('loaded');
            var a = '';
            var b = '';

            var operation = 'plus';
            $('input[name=operation]').click(function(){
                operation = $(this).val();
                console.log('Choosed operation - ' + operation);
            });
            $('button').click(function(){
                $(this).addClass('clicked');
                var number = $(this).text();
                console.log('clicked ' + number);

                var text = $('#display').val();
                text = text + number;

                if(a.length < 3) {
                    a = text;
                } else if(b.length < 3) {
                    if(text.length > 3) {
                        text = number;
                    }
                    b = text;
                    if(b.length == 3) {
                        $.getJSON('<?php echo __FILE__; ?>', {operation:operation, a:a, b:b}, function(result){
                            $('#display').val(result.result);
                        })
                    }
                }

                $('#a span').html(a);
                $('#b span').html(b);
                $('#display').val(text);
            });
        });
        console.log('started');
    </script>
</head>
<body>
<input id="display"/>
<div style="overflow:hidden">
    <?php for($i=1;$i<=9;$i++): ?>
    <button><?php echo $i; ?></button>
    <?php endfor; ?>
    <button><?php echo 0; ?></button>
</div>
<br style="clear:both">
<div id="a">a = <span></span></div>
<div id="b">b = <span></span></div>
<br style="clear:both">
<div>
    <input name="operation" type="radio" value="plus"/> Сложение<br>
    <input name="operation" type="radio" value="minus"/> Вычитание<br>
</div>
</body>
</html>
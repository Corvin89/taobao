<?php
//<Vladislav Fedorischev`s function here>
function getViews($b){

//Function for counting number of viewers of site <Made by Vladislav Fedorischev><assist Alexandr Kuciy>
	$vis = StatPress_Print("%totalpageviews%");
	$visi=(int)$vis+616908;
	$visit="$visi";
	$a=array();
	$j=strlen($visit);
		for($i=0;$i<$j;$i++){
		$a[]=$visit{$i};
	} 	  
	$reverse=array_reverse($a,false);
	$count=count($reverse);
	for($count;$count<9;$count++){
		$reverse[]="0";
	}
	$normal=array_reverse($reverse,false);
	
	return $normal[$b];
}

function viewsRefresh(){
	if (isset( $_GET['timer'] ) ) :
?> 				    <div class="left-num">
                        <a href="#"><?php echo getViews(0);?></a>
                        <a href="#"><?php echo getViews(1);?></a>
                        <a href="#"><?php echo getViews(2);;?></a>
                    </div>
                    <div class="left-num">
                        <a href="#"><?php echo getViews(3);?></a>
                        <a href="#"><?php echo getViews(4);?></a>
                        <a href="#"><?php echo getViews(5);?></a>
                    </div>
                    <div class="left-num">
                        <a href="#"><?php echo getViews(6);?></a>
                        <a href="#"><?php echo getViews(7);?></a>
                        <a href="#"><?php echo getViews(8);?></a>
                    </div>
                    <div class="text">
                        <p>Столько человек уже воспользовались <br/> услугами нашего сервиса</p>
                    </div>
                    
                	
	<?php die(); endif;
}
add_action('init','viewsRefresh');
function ajaxCall(){?>
	<script>
		$(function(){
			function myFunction(){				
				
				$('.number').load('/?timer');
				
			}
			setInterval(myFunction, 1000);
		})
		
	</script>
<?php
	return true;
}
add_action('wp_enqueue_scripts','ajaxCall');
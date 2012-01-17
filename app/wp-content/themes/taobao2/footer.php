<footer id="footer">
    <div class="box">
        <div class="block">			 
            <ul>
				
			 <?php for($i=0;get_post_meta(2701,'_simple_fields_fieldGroupID_3_fieldID_1_numInSet_'.$i,true)!=0;$i++)
				 {?>
			 	<li><a href="<?=get_post_meta(2701,'_simple_fields_fieldGroupID_3_fieldID_2_numInSet_'.$i,true); ?>"><img src="<?=wp_get_attachment_url(get_post_meta(2701,'_simple_fields_fieldGroupID_3_fieldID_1_numInSet_'.$i,true)) ?>" alt="" title="" /></a></li>			 	
			  <?php } ?>
			  
                <!--<li><a href="#"><img src="<?php bloginfo('template_directory'); ?>/img/icon1.jpg" alt="" title="" /></a></li>
                <li><a href="#"><img src="<?php bloginfo('template_directory'); ?>/img/icon2.jpg" alt="" title="" /></a></li>
                <li><a href="#"><img src="<?php bloginfo('template_directory'); ?>/img/icon3.jpg" alt="" title="" /></a></li>
                <li><a href="#"><img src="<?php bloginfo('template_directory'); ?>/img/icon4.jpg" alt="" title="" /></a></li>
                <li><a href="#"><img src="<?php bloginfo('template_directory'); ?>/img/icon5.jpg" alt="" title="" /></a></li>
                <li><a href="#"><img src="<?php bloginfo('template_directory'); ?>/img/icon6.jpg" alt="" title="" /></a></li>-->
            </ul>
            <p><?php echo get_option('omr_tracking_code');?></p>
        </div>
        <div class="contact">
			<div class="left">
		         <div class="fone">
		         	<em>розничный отдел</em>
		         	<strong><span><?php echo get_post_meta(2701,'_simple_fields_fieldGroupID_1_fieldID_1_numInSet_0',true);?></span> <?php echo get_post_meta(2701,'_simple_fields_fieldGroupID_1_fieldID_2_numInSet_0',true);?></strong>
		         </div>
				 <ul class="contact">
					<li><img src="http://web.icq.com/whitepages/online?icq=<?php echo get_post_meta(2701,'_simple_fields_fieldGroupID_1_fieldID_3_numInSet_0',true); ?>&img=5" alt="Статус <?php echo get_post_meta(2701,'_simple_fields_fieldGroupID_1_fieldID_3_numInSet_0',true); ?>" /><?php echo get_post_meta(2701,'_simple_fields_fieldGroupID_1_fieldID_3_numInSet_0',true); ?></li>
					<li class="mail"><a href="<?php echo get_option('admin_email'); ?>"><?php echo get_option('admin_email'); ?></a></li>
					<li class="skype"><a href="skype:<?php echo get_post_meta(2701,'_simple_fields_fieldGroupID_1_fieldID_4_numInSet_0',true);?>"><?php echo get_post_meta(2701,'_simple_fields_fieldGroupID_1_fieldID_4_numInSet_0',true);?></a></li>					
				</ul>
		    </div>  
			<div class="left right">
		         <div class="fone">
		         	<em>оптовый отдел</em>
		         	<strong><span><?php echo get_post_meta(2701,'_simple_fields_fieldGroupID_2_fieldID_1_numInSet_0',true);?></span> <?php echo get_post_meta(2701,'_simple_fields_fieldGroupID_2_fieldID_2_numInSet_0',true);?></strong>
		         </div>
				 <ul class="contact">
					<li><img src="http://web.icq.com/whitepages/online?icq=<?php echo get_post_meta(2701,'_simple_fields_fieldGroupID_2_fieldID_3_numInSet_0',true); ?>&img=5" alt="Статус <?php echo get_post_meta(2701,'_simple_fields_fieldGroupID_2_fieldID_3_numInSet_0',true); ?>" /><?php echo get_post_meta(2701,'_simple_fields_fieldGroupID_2_fieldID_3_numInSet_0',true); ?></li>
					<li class="mail"><a href="<?php echo get_option('admin_email'); ?>"><?php echo get_option('admin_email'); ?></a></li>
					<li class="skype"><a href="skype:<?php echo get_post_meta(2701,'_simple_fields_fieldGroupID_2_fieldID_4_numInSet_0',true);?>"><?php echo get_post_meta(2701,'_simple_fields_fieldGroupID_2_fieldID_4_numInSet_0',true);?></a></li>					
				</ul>
		    </div>             
        </div>
    </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
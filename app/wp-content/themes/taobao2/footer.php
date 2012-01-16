<footer id="footer">
    <div class="box">
        <div class="block">			 
            <ul>
				
			 <?php for($i=0;get_post_meta(2704,'_simple_fields_fieldGroupID_1_fieldID_2_numInSet_'.$i,true)!=0;$i++)
				 {?>
			 	<li><a href="<?=get_post_meta(2704,'_simple_fields_fieldGroupID_1_fieldID_3_numInSet_'.$i,true); ?>"><img src="<?=wp_get_attachment_url(get_post_meta(2704,'_simple_fields_fieldGroupID_1_fieldID_2_numInSet_'.$i,true)) ?>" alt="" title="" /></a></li>			 	
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
                    <strong><span>8 (4162)</span> 218-718</strong>
                </div>
                <ul class="contact">
                    <li class="qip">611 250 763</li>
                    <li class="mail"><a href="mailto:zakaz@taobao.ru.com">zakaz@taobao.ru.com</a></li>
                    <li class="skype">taobao.ru.com</li>
                </ul>
            </div>
            <div class="left right">
                <div class="fone">
                    <em>розничный отдел</em>
                    <strong><span>8 (4162)</span> 218-718</strong>
                </div>
                <ul class="contact">
                    <li class="qip">611 250 763</li>
                    <li class="mail"><a href="mailto:zakaz@taobao.ru.com">zakaz@taobao.ru.com</a></li>
                    <li class="skype">taobao.ru.com</li>
                </ul>
            </div>
        </div>
    </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
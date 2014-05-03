<?php

echo '</div></div>

	<!-- content-wrap ends here -->
	</div>

	<!--footer starts here-->
	<div id="footer">';

echo '<a href="'.$config['home'].'">'.$config['copy'].'</a><br />';

	show_online();
	show_counter();
	navigation();
	perfomance();
echo '</div></div>';
echo '</body></html>';
?>

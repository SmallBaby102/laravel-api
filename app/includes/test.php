<?php

require_once "pap_helper.inc.php";
$pap_affiliate_login_obj = PapLogin("https://cryptowire.postaffiliatepro.com/scripts/server.php", "info@cryptowire.vip", "safasf", "merchant");
if ((!is_null($pap_affiliate_login_obj)) && (!empty($pap_affiliate_login_obj))) {
	echo "affiliate exist !";
}

?>
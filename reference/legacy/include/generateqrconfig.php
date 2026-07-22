<?php

	// Generate QR Lib
	use chillerlan\QRCode\{QRCode, QROptions};
	include 'vendor/autoload.php';

	function generateQRCode($data)
	{
		$qrcode = (new QRCode)->render($data);
		printf('<img src="%s" alt="QR Code" />', $qrcode);
	}

?>
<?
    /******************************************************************************
     *
     * dbi.php
     *
     * Configuration file
     *
     * Created : 2014
     *
     ******************************************************************************/
	//$my_db = new mysqli("10.3.0.9", "root", "m!nv#Rtisin9", "babience_vacance");
	$my_db = new mysqli("localhost", "root", "6alslqjxkdlwld@%*)", "belifcushion");
	if (mysqli_connect_error()) {
		exit('Connect Error (' . mysqli_connect_errno() . ') '. mysqli_connect_error());
	}
?>

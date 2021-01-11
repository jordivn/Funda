<?php

/**
 * @file 
 * 
 * @brief  Script for adding properties to WP from funda.
 * 
 * @details 
 * 
 * @author 		Jordi van Nistelrooij @ Webs en Systems
 * @email 		info@websensystems.nl
 * @website		https://websensystems.nl
 * @version 	1.0.0
 * @date 		2021-01-11
 * @copyright 	Non of these scripts maybe copied or modified without permission of the authors
 * 
 * @note
 * @todo
 * @bug
 */


if(isset($_REQUEST["FundaURL"])){
require_once("wp-config.php");

$arrayFunda = json_decode(file_get_contents("https://wes-server.nl/cdn/funda/fundaStripper.php?FundaURL=". $_REQUEST["FundaURL"]),true);
echo "<pre>";
print_r($arrayFunda);
echo "</pre>";


if(isset($_REQUEST["Status"])){ 
    $arrayFunda["Eigenschappen"]["Status"] = $_REQUEST["Status"];
}

file_put_contents("wp-content/uploads/Import/" . strtolower($arrayFunda["Woonplaats"] . "-" . $arrayFunda["Straat"] . "-" . $arrayFunda["Huisnummer"]) . "." . $arrayFunda["AfbeeldingType"] ,file_get_contents($arrayFunda["AfbeeldingLink"]));
$linkToImage = "https://www.leenaersmakelaardij.nl/wp-content/uploads/Import/" . strtolower($arrayFunda["Woonplaats"] . "-" . $arrayFunda["Straat"] . "-" . $arrayFunda["Huisnummer"]) . "." . $arrayFunda["AfbeeldingType"];
if ( ! function_exists( 'wp_crop_image' ) ) {
    include( ABSPATH . 'wp-admin/includes/image.php' );
}


$objMysqli = new mysqli(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
$stringQuery = "INSERT INTO `".$table_prefix."posts` (
                     `post_author`,
                     `post_title`,
                     `post_name`,
                     `guid`,
                     `post_type`,
                     `post_content`,
                     `post_excerpt`,
                     `to_ping`,`pinged`,`post_content_filtered`) VALUES(
                     1,
                     '".$arrayFunda["Woonplaats"] . " - " . $arrayFunda["Straat"] . " " . $arrayFunda["Huisnummer"] ."',
                     '". strtolower($arrayFunda["Woonplaats"] . "-" . $arrayFunda["Straat"] . "-" . $arrayFunda["Huisnummer"]) ."',
                     'https://leenaersmakelaardij.nl/woning/". str_replace(" ","-",strtolower($arrayFunda["Woonplaats"] . "-" . $arrayFunda["Straat"] . "-" . $arrayFunda["Huisnummer"])) ."/',
                     'realworks_wonen',
                     '".$objMysqli->real_escape_string($arrayFunda["Omschrijving"])."',
                     '',
                     '',
                     '','')";
$objMysqli->query($stringQuery);
$id = $objMysqli->insert_id;

$stringQuery = "INSERT INTO `".$table_prefix."posts` (
    `post_author`,
    `post_title`,
    `post_name`,
    `guid`,
    `post_type`,
    `post_content`,
    `post_excerpt`,
    `to_ping`,`pinged`,`post_content_filtered`,`post_mime_type`,`post_parent`,`post_status`) VALUES(
    1,
    'Foto ". strtolower($arrayFunda["Woonplaats"] . "-" . $arrayFunda["Straat"] . "-" . $arrayFunda["Huisnummer"]) ."',
    'foto-". strtolower($arrayFunda["Woonplaats"] . "-" . $arrayFunda["Straat"] . "-" . $arrayFunda["Huisnummer"]) ."',
    '".$linkToImage."',
    'attachment',
    '',
    '',
    '',
    '','','" . mime_content_type("wp-content/uploads/Import/" . strtolower($arrayFunda["Woonplaats"] . "-" . $arrayFunda["Straat"] . "-" . $arrayFunda["Huisnummer"]) . "." . $arrayFunda["AfbeeldingType"]) . "',
    ".$id.",'inherit')";

$objMysqli->query($stringQuery);
$idFoto = $objMysqli->insert_id;

$stringQuery = "INSERT INTO `".$table_prefix."postmeta` (
    `post_id`,
    `meta_key`,
    `meta_value`) VALUES(
        ".$idFoto.",
        '_wp_attached_file',
        'Import/" . strtolower($arrayFunda["Woonplaats"] . "-" . $arrayFunda["Straat"] . "-" . $arrayFunda["Huisnummer"]) . "." . $arrayFunda["AfbeeldingType"]."')";
$objMysqli->query($stringQuery);

wp_generate_attachment_metadata( $idFoto, "wp-content/uploads/Import/" . strtolower($arrayFunda["Woonplaats"] . "-" . $arrayFunda["Straat"] . "-" . $arrayFunda["Huisnummer"]) . "." . $arrayFunda["AfbeeldingType"] );

$stringQuery = $stringQuery = "INSERT INTO `".$table_prefix."postmeta` (
    `post_id`,
    `meta_key`,
    `meta_value`) VALUES(
        ".$id.",
        '_thumbnail_id',
        ".$idFoto.")";
$objMysqli->query($stringQuery);



//echo $stringQuery;


$stringQuery = "INSERT INTO `".$table_prefix."realworks_wonen` (
                                `wordpress_id`,
                                `adres`,
                                `plaats`,
                                `koopprijsVoorvoegsel`,
                                `koopprijs`,
                                `koopconditie`,
                                `datumInvoer`,
                                `status`,
                                `prijsTonen`) VALUES(
                                 ".$id.",
                                 '" . $arrayFunda["Straat"] . " " . $arrayFunda["Huisnummer"] ."',
                                 '". $arrayFunda["Woonplaats"]."',
                                 'vraagprijs',
                                 ".str_replace(".","",$arrayFunda["Prijs"]).",
                                 '".$arrayFunda["PrijsConditie"]."',
                                 NOW(),
                                 '".$arrayFunda["Eigenschappen"]["Status"]."',
                                 1)";
$objMysqli->query($stringQuery);    
$stringQuery = "INSERT INTO `".$table_prefix."realworks_wonen_adresNederlands` (
                                 `parent_id`,
                                 `straatnaam`,
                                 `huisnummer`,
                                 `postcode`,
                                 `plaats`,
                                 `land`) VALUES(
                                ".$id.",
                                '" . $arrayFunda["Straat"] . "',
                                " . $arrayFunda["Huisnummer"] . ",
                                '" . $arrayFunda["Postcode"] . "',
                                '" . $arrayFunda["Woonplaats"] . "',
                                'NL')";
$objMysqli->query($stringQuery);    
//echo $stringQuery;

echo "Imported";
}else{
    ?>
    <br/>
    <form >
            <input name=FundaURL placeholder=FundaURL>'
            <select name=Status>
                <option value=NULL>
                <option value=Verkocht>Verkocht
                <option value=Aangekocht>Aangekocht
            </select>
            <input type=submit>
        </form>
        <?PHP
    
    }
?>

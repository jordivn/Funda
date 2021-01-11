<?php
/**
 * @file 
 * 
 * @brief  Script for getting an json string with property data from funda.
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

$siteName = $_REQUEST["FundaURL"];


$ch = curl_init();


curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
curl_setopt($ch, CURLOPT_HEADER, false);
//curl_setopt($ch, CURLINFO_HEADER_OUT, true);
//curl_setopt($ch, CURLOPT_CERTINFO, true);
curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);


curl_setopt($ch, CURLOPT_URL, $siteName);
// Execute
$response = curl_exec($ch);
curl_close($ch);

$dom = new DOMDocument;
$dom->loadHTML($response);

/**
 * ELEMENT SEARCH
$divs = $dom->getElementsByTagName("div");
for($i = 0; $i < $divs->length; $i++){
    if($divs->item($i)->getAttribute("class") == "object-description-body"){
        echo $i . "<==========";
    }
}
 * 
 * 
 */


//BASICPARTS
$arrayFunda["Adres"] = strip_tags($dom->saveHTML($dom->getElementsByTagName('span')->item(39))) . " " . strip_tags($dom->saveHTML($dom->getElementsByTagName('span')->item(40)));
$arrayAddres = explode(" ",$arrayFunda["Adres"]);
$straat = $arrayAddres[0];
if(count($arrayAddres) > 5){  
    for($i=1;$i < count($arrayAddres)-4; $i++){
        $straat .= " " . $arrayAddres[$i];
    }
}


$arrayFunda["Straat"] = $straat;
$arrayFunda["Huisnummer"] = $arrayAddres[count($arrayAddres)-4];
$arrayFunda["Postcode"] = $arrayAddres[count($arrayAddres)-3] . " " . $arrayAddres[count($arrayAddres)-2];
$arrayFunda["Woonplaats"] = $arrayAddres[count($arrayAddres)-1];
$arrayPrijs = explode(" ",strip_tags($dom->saveHTML($dom->getElementsByTagName('strong')->item(0))));
$arrayFunda["Prijs"] = $arrayPrijs[1];
$arrayFunda["PrijsConditie"] = $arrayPrijs[2];

//HOOFDAFBEELDING
$arrayFunda["AfbeeldingLink"] = $dom->getElementsByTagName('img')->item(2)->getAttribute("src");
$image = $dom->getElementsByTagName('img')->item(2)->getAttribute("src");
$urlParts = explode(".",$image);
$arrayFunda["AfbeeldingType"] = $urlParts[count($urlParts)-1];
file_put_contents("temp." . $urlParts[count($urlParts)-1], file_get_contents($image));
$image = "temp." . $urlParts[count($urlParts)-1];
$imageData = base64_encode(file_get_contents($image));
// Format the image SRC:  data:{mime};base64,{data};
$src = 'data: '.mime_content_type($image).';base64,'.$imageData;
$arrayFunda["afbeeldingdata"] = $src;
unlink($image);

//Omschrijving
$arrayFunda["Omschrijving"] = preg_replace( "/\n|\r/", "", $dom->saveHTML($dom->getElementsByTagName('div')->item(85))) ;

//EIGENSCHAPPEN
$arrayEigenschappen = array("Status", "Bouwjaar", "Perceel", "Inhoud");
$dt = $dom->getElementsByTagName("dt");
for ($i = 0; $i < $dt->length; $i++) {
    if(in_array(preg_replace( "/<br>|\n|\r/", "", strip_tags($dom->saveHTML($dt->item($i)))), $arrayEigenschappen)){
        $arrayFunda["Eigenschappen"][preg_replace( "/<br>|\n|\r/", "", strip_tags($dom->saveHTML($dt->item($i))))] = preg_replace( "/<br>|\n|\r/", "", (strip_tags($dom->saveHTML($dom->getElementsByTagName("dd")->item($i)))));
    }
}




echo json_encode($arrayFunda);






}else{
?>
<br/>
<form >
        <input name=FundaURL placeholder=FundaURL>
        <input type=submit>
    </form>
    <?PHP

}
?>
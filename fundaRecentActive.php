<?php
/**
 * @file 
 * 
 * @brief  Script for getting an json string with latest properties.
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
for($i; $i < $divs->length; $i++){
    if($divs->item($i)->getAttribute("class") == "object-description-body"){
        echo $i . "<==========";
    }
}
 * 
 * 
 */

$arrayLinks = $dom->getElementsByTagName("a");
for($i = 0; $i < $arrayLinks->length; $i++){
    if($arrayLinks->item($i)->getAttribute("data-object-url-tracking") == "recenteactiviteit"){
        $title = $arrayLinks->item($i)->getAttribute("title");
        $addURL = "https://funda.nl" . $arrayLinks->item($i)->getAttribute("href");
        $status = explode(" ",$arrayLinks->item($i)->childNodes->item(9)->childNodes->item(1)->nodeValue)[0];
        $arrayFunda[] = array("Titel" => $title, "URL" => $addURL, "Status"=> $status);

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
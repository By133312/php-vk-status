<?php
//authorization string for your app
//https://oauth.vk.com/authorize?client_id=[APP_ID]&scope=status,offline&redirect_uri=https://oauth.vk.com/blank.html&display=page&v=5.4&response_type=token

header('Content-Type: text/html; charset=utf8');
date_default_timezone_set("Europe/Moscow");

//vk api method to set user status
$set_status_format = "https://api.vk.com/method/status.set?text=%s&access_token=%s";

//special symbols used in vk as smiles - global variables
$fir_tree = mb_convert_encoding('&#127876;', 'UTF-8', 'HTML-ENTITIES');
$digit = mb_convert_encoding('&#8419;', 'UTF-8', 'HTML-ENTITIES');

//put here access tokens obtained from users
$access_token_array = array(
    //should not be empty
);

set_status($set_status_format, urlencode(generate_status_text()), $access_token_array);

function set_status($set_status_format, $encoded_status_text, $access_token_array) {
    foreach($access_token_array as &$token) {
        $set_status_url = sprintf($set_status_format, $encoded_status_text, $token);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $set_status_url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($curl);
        curl_close($curl);
    }
}

//decorate numbers with special symbols used in vk
function decorate($num) {
    return implode($GLOBALS['digit'], str_split($num)).$GLOBALS['digit'];
}

function word($num, $type) {
    $num_array = str_split($num);
    $end_digit = end($num_array);
    if($end_digit == "1" && $num != 11) {
        switch($type) {
            case 0: return "минута";
            case 1: return "час";
            case 2: return "день";
        }
    } else if(($end_digit == "2" && $num != 12) ||
              ($end_digit == "3" && $num != 13) ||
              ($end_digit == "4" && $num != 14)) {
        switch($type) {
            case 0: return "минуты";
            case 1: return "часа";
            case 2: return "дня";
        }
    } else {
        switch($type) {
            case 0: return "минут";
            case 1: return "часов";
            case 2: return "дней";
        }
    }
}

function generate_status_text() {
    //calculate remaining days to certain date
    $current_date = new DateTime();
    $new_year_date = new DateTime("2014-01-01");
    $date_difference = $new_year_date->format('U') - $current_date->format('U');

    $days = intval($date_difference / (60*60*24));
    $hours = intval(($date_difference - ($days * (60*60*24))) / (60*60));
    $minutes = intval(($date_difference - ($days * (60*60*24) + $hours * (60*60))) / (60));

    $status_text = sprintf("До Нового Года осталось %s%s %s%s и %s%s! %s",
        decorate($days), word($days, 2), decorate($hours), word($hours, 1), decorate($minutes), word($minutes, 0), $GLOBALS['fir_tree']);

    return $status_text;
}

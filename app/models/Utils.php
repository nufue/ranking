<?php

class Utils {

    public static function convertDate($date) {
        $date = str_replace(". ", ".", $date);
        $dt = strtotime($date);
        return Date("Y-m-d", $dt);
    }

}
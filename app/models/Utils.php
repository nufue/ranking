<?php

namespace App\Model;

class Utils {

    public static function convertDate($date) {
        $date = str_replace(". ", ".", $date);
        $dt = strtotime($date);
        return new \DateTime(Date("Y-m-d", $dt));
    }

}
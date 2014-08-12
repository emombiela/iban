<?php
namespace Emombiela\Iban;

require_once('Data/Country.php');

class Iban
{
    static function countriesList() {
        $countries = Data\Country::countries();
        return $countries;
    }
}

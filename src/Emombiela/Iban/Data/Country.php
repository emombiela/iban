<?php namespace Emombiela\Iban\Data;

class Country 
{
    /**
     * Conventions for this DataBase:
     *
     * The following character representations are used in this database:
     *
     * n Digits (numeric characters 0 to 9 only)
     * a Upper case letters (alphabetic characters A-Z only)
     * c upper and lower case alphanumeric characters (A-Z, a-z and 0-9)
     * e blank space
     *
     * The following length indications are used in this database:
     *
     * nn! fixed length
     * nn maximum length
     */

    static $countries = array(
        'ES' => array(
            'countryName'                  => 'Spain',
            'bbanStructure'                => '4!n4!n1!n1!n10!n',
            'bbanLength'                   => 20,
            'bbanBankIdentifierPosition'   => array ('first' => 1, 'positions' => 4),
            'bbanBranchIdentifierPosition' => array ('first' => 5, 'positions' => 4),
            'ibanStructure'                => 'ES2!n4!n4!n1!n1!n10!n',
            'ibanLength'                   => 24,
            'sepaCountry'                  => true,
        ),
    );

    static function countries() {
        $countriesList = array();

        foreach (Country::$countries as $countryKey => $countryData) {
            $countriesList = array_add($countriesList, $countryKey, $countryData['countryName']);
        }

        return $countriesList;
    }
}

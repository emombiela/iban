<?php namespace Emombiela\Iban\Data;

class Country 
{
    /*
     * @author  Eduard Mombiela <mombiela.eduard@gmail.com>
     * @version GIT: $Id$
     */

    /**
     * Conventions for countries DataBase:
     * ===================================
     *
     * **The following character representations are used in this database:**
     *
     * n Digits (numeric characters 0 to 9 only)
     * a Upper case letters (alphabetic characters A-Z only)
     * c upper and lower case alphanumeric characters (A-Z, a-z and 0-9)
     * e blank space
     *
     * **The following length indications are used in this database:**
     *
     * nn! fixed length
     * nn  maximum length
     *
     * @see http://www.ecbs.org/iban.htm
     */
    static $countries = array(
        'NL' => array(
            'countryName'                  => 'The Netherlands',
            'countryCode'                  => 'NL',
            'bbanStructure'                => '4!a10!n',
            'bbanLength'                   => 14,
            'bbanBankIdentifierPosition'   => array ('first' => 1, 'positions' => 4),
            'bbanBranchIdentifierPosition' => null,
            'ibanStructure'                => 'NL2!n4!a10!n',
            'ibanLength'                   => 18,
            'sepaCountry'                  => true,
        ),
        'ES' => array(
            'countryName'                  => 'Spain',
            'countryCode'                  => 'ES',
            'bbanStructure'                => '4!n4!n1!n1!n10!n',
            'bbanLength'                   => 20,
            'bbanBankIdentifierPosition'   => array ('first' => 0, 'positions' => 4),
            'bbanBranchIdentifierPosition' => array ('first' => 4, 'positions' => 4),
            'ibanStructure'                => 'ES2!n4!n4!n1!n1!n10!n',
            'ibanLength'                   => 24,
            'sepaCountry'                  => true,
        ),
        'GB' => array(
            'countryName'                  => 'United Kingdom',
            'countryCode'                  => 'GB',
            'bbanStructure'                => '4!a6!n8!n',
            'bbanLength'                   => 18,
            'bbanBankIdentifierPosition'   => array ('first' => 0, 'positions' => 4),
            'bbanBranchIdentifierPosition' => array ('first' => 4, 'positions' => 6),
            'ibanStructure'                => 'GB2!n4!a6!n8!n',
            'ibanLength'                   => 22,
            'sepaCountry'                  => true,
        ),
    );

    /**
     * Returns an array with the names of the countries
     * where the key of each element is the country code defined in ISO 3166.
     *
     * @return array
     */
    static function countries()
    {
        $countriesList = array();

        foreach (Country::$countries as $countryKey => $countryData) {
            $countriesList = array_add($countriesList, $countryKey, $countryData['countryName']);
        }

        return $countriesList;
    }

    /**
     * Returns an array with the names of the SEPA countries
     * where the key of each element is the country code defined in ISO 3166.
     *
     * @return array
     */
    static function sepaCountries()
    {
        $countriesList = array();

        foreach (Country::$countries as $countryKey => $countryData) {
            if ($countryData['sepaCountry']) {
                $countriesList = array_add($countriesList, $countryKey, $countryData['countryName']);
            }
        }

        return $countriesList;
    }

    /**
     * Returns an array with the structure of a given country.
     *
     * @param  string $sountryCode
     * @return array
     */
    static function getCountry($countryCode)
    {
        return array_get(Country::$countries,$countryCode);
    }
}

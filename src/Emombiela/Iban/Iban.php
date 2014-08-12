<?php
namespace Emombiela\Iban;

require_once('Data/Country.php');

class Iban
{
    /**
     * Returns an array with the names of the countries
     * where the key of each element is the country code defined in ISO 3166.
     *
     * @return array
     */
    static function countriesList()
    {
        $countries = Data\Country::countries();
        return $countries;
    }

    /**
     * Returns an array with the names of the SEPA countries
     * where the key of each element is the country code defined in ISO 3166.
     *
     * @return array
     */
    static function sepaCountriesList()
    {
        $countries = Data\Country::sepaCountries();
        return $countries;
    }
    
    /**
     * Returns a string with the validation error.
     * If the string is null the IBAN code is correct.
     *
     * @param  string $iban
     * @return string
     */
    static function validate($iban)
    {
        $error = null;

        if (!is_string($iban)) {
            return $error = 'BAD_IBAN_CODE_TYPE';
        }

        $iban = strtoupper($iban);

        if (strlen($iban) < 2) {
            return $error = 'INCORRECT_IBAN_LENGTH';
        }

        if (
               ord(substr($iban,0,1)) < 65 
            || ord(substr($iban,0,1)) > 90
            || ord(substr($iban,1,1)) < 65
            || ord(substr($iban,1,1)) > 90
        ) {
            return $error = 'INCORRECT_IBAN_COUNTRY_CODE';
        }

        if (is_null($country = Data\Country::getCountry(substr($iban,0,2)))) {
            return $error = 'THERE_IS_NO_COUNTRY_CODE';
        }

        if (
               strlen($iban) < $country['ibanLength'] 
            || strlen($iban) > $country['ibanLength']
        ) {
            return $error = 'INCORRECT_IBAN_LENGTH';
        }

        return $error;
    }

    static function calculate($iban)
    {
        //
    }
}

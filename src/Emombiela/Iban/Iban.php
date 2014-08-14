<?php
namespace Emombiela\Iban;

require_once('Data/Country.php');

class Iban
{
    /**
     * IBAN code errors list.
     */
    private static $error = array(
        'THERE_IS_NO_COUNTRY_CODE',
        'BAD_IBAN_CODE_TYPE',
        'BAD_IBAN_LENGTH',
        'BAD_IBAN_COUNTRY_CODE',
        'BAD_IBAN_STRUCTURE',
    );

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
        /** Checks whether the function receives a string. */
        if (!is_string($iban)) {
            return Iban::$error[1];
        }

        /** Converts uppercase. */
        $iban = strtoupper($iban);

        /** Checks if the first two positions of the string are letters. */
        if (strlen($iban) < 2) {
            return Iban::$error[2];
        } else if (   ord(substr($iban,0,1)) < 65 
                   || ord(substr($iban,0,1)) > 90
                   || ord(substr($iban,1,1)) < 65
                   || ord(substr($iban,1,1)) > 90
        ) {
            return Iban::$error[3];
        }

        /** Checks if the country exists in the database. */
        if (is_null($country = Data\Country::getCountry(substr($iban,0,2)))) {
            return Iban::$error[0];
        }

        /** Checks if the length of the code provided corresponds to the country indicated. */
        if (strlen($iban) < $country['ibanLength'] || strlen($iban) > $country['ibanLength']) {
            return Iban::$error[2];
        }

        /** Check the structure of IBAN. */
        if (!Iban::validateStructure($country['ibanStructure'], $iban)) {
            return Iban::$error[4];
        }

        return null;
    }

    /**
     * Validate IBAN structure.
     *
     * @param  string  $structure
     * @param  string  $iban
     * @return boolean
     */
    static function validateStructure($structure, $iban)
    {
        /**
         * Each structure of an IBAN code consists in smaller substructures
         * that define the format of this and follow the conventions shown
         * in Country class.
         */
        $substructure = array(
            'length'      => 0,
            'fixedLength' => false,
            'kind'        => null,
            'isCompleted' => false,
        );

        $i = 2;
        $j = 2;

        while ($i < strlen($structure)):
            if (is_numeric($structure[$i])) {
                if ($substructure['length'] == 0) {
                    $substructure['length'] = $structure[$i];
                } else {
                    $substructure['length'] .= $structure[$i];
                }
            } else if ($structure[$i] == '!') {
                $substructure['fixedLength'] = true;
            } else {
                $substructure['kind'] = $structure[$i];
                $substructure['isCompleted'] = true;
            }

            if ($substructure['isCompleted']) {
                $substructureData = substr($iban,$j,$substructure['length']);

                if ($substructure['kind'] == 'n') {
                    if (!is_numeric($substructureData)) {
                        return false;
                    } else {
                        $j += strlen($substructureData);
                    }
                } else if ($substructure['kind'] == 'a') {
                    $k = 0;
                    while ($k < strlen($substructureData)):
                        if (   ord(substr($substructureData,$k,1)) < 65 
                            || ord(substr($substructureData,$k,1)) > 90
                        ) {
                            return false;
                        } else {
                            $k++;
                            $j++;
                        }
                    endwhile;
                } else {
                    $k = 0;
                    while ($k < strlen($substructureData)):
                        if (substr($substructureData,$k,1) != ' ') {
                            return false;
                        } else {
                            $k++;
                            $j++;
                        }
                    endwhile;
                }
                
                /** Reset $substructure */
                $substructure['length']      = 0;
                $substructure['fixedLength'] = false;
                $substructure['kind']        = null;
                $substructure['isCompleted'] = false;
            }

            $i++;
        endwhile;

        return true;
    }

    static function calculate($iban)
    {
        //
    }
}

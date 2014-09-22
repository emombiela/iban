<?php
namespace Emombiela\Iban;

require_once('Data/Country.php');

class Iban
{
    /**
     * Conversion table from letters to digits to calculate the IBAN code.
     */
    private static $lettersConversionTable = array(
        'A' => 10,
        'B' => 11,
        'C' => 12,
        'D' => 13,
        'E' => 14,
        'F' => 15,
        'G' => 16,
        'H' => 17,
        'I' => 18,
        'J' => 19,
        'K' => 20,
        'L' => 21,
        'M' => 22,
        'N' => 23,
        'O' => 24,
        'P' => 25,
        'Q' => 26,
        'R' => 27,
        'S' => 28,
        'T' => 29,
        'U' => 30,
        'V' => 31,
        'W' => 32,
        'X' => 33,
        'Y' => 34,
        'Z' => 35,
    );

    /**
     * IBAN code errors list.
     */
    private static $error = array(
        'THERE_IS_NO_COUNTRY_CODE',
        'BAD_IBAN_CODE_TYPE',
        'BAD_IBAN_LENGTH',
        'BAD_IBAN_COUNTRY_CODE',
        'BAD_IBAN_STRUCTURE',
        'BAD_IBAN_CHECK_DIGITS_TEST',
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

        /** Check IBAN structure. */
        if (!Iban::validateStructure($country['ibanStructure'], $iban)) {
            return Iban::$error[4];
        }

        /** IBAN check digits test*/
        if (Iban::checkDigits($iban) != 1) {
            return Iban::$error[5];
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

    /**
     * Calculate IBAN check digits.
     *
     * @param  string  $iban
     * @return integer $ibanControlCode
     */
    static function checkDigits($iban)
    {
        $ibanArray = str_split($iban);

        for ($i = 0; $i < 4; $i++) {
            array_push($ibanArray,array_shift($ibanArray));
        }

        $i = 0;
        foreach ($ibanArray as $ibanElement) {
            if (!is_numeric($ibanElement)) {
                $ibanArray[$i] = Iban::$lettersConversionTable[$ibanElement];
            }

            $i++;
        }

        $iban            = implode($ibanArray);
        $ibanSlice       = null;
        $ibanSliceIndex  = 0;
        $ibanControlCode = 0;
        $modulus         = 97;
        $ibanLength      = strlen($iban);
        $firstIbanSlice  = false;

        while ($ibanLength != 0):
            if (!$firstIbanSlice) {
                if ($ibanLength >= 9) {
                    $ibanSlice       = substr($iban, $ibanSliceIndex, 9);
                    $ibanControlCode = $ibanSlice % $modulus;
                    $ibanSliceIndex  = $ibanSliceIndex + 9;
                    $ibanLength      = $ibanLength - 9;
                } else {
                    $ibanSlice       = $iban;
                    $ibanControlCode = $ibanSlice % $modulus;
                    $ibanLength      = 0;
                }
                $firstIbanSlice = true;
            } else {
                if ($ibanLength >= 7) {
                    $ibanSlice       = $ibanControlCode.substr($iban, $ibanSliceIndex, 7);
                    $ibanControlCode = $ibanSlice % $modulus;
                    $ibanSliceIndex  = $ibanSliceIndex + 7;
                    $ibanLength      = $ibanLength - 7;
                } else {
                    $ibanSlice       = $ibanControlCode.substr($iban, $ibanSliceIndex, $ibanLength);
                    $ibanControlCode = $ibanSlice % $modulus;
                    $ibanLength      = 0;
                }
            }
        endwhile;

        return $ibanControlCode;
    }
}

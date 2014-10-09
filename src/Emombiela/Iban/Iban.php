<?php
/**
 * @author  Eduard Mombiela <mombiela.eduard@gmail.com>
 * @version GIT: $Id$
 * @see     http://en.wikipedia.org/wiki/International_Bank_Account_Number
 */

namespace Emombiela\Iban;

require_once('Data/DataHandler.php');
require_once('BbanCheckDigit.php');

class Iban
{
    /**
     * Conversion table from letters to digits to calculate the IBAN code.
     */
    private static $lettersConversionTable = array(
        'A' => 10, 'B' => 11, 'C' => 12, 'D' => 13,
        'E' => 14, 'F' => 15, 'G' => 16, 'H' => 17,
        'I' => 18, 'J' => 19, 'K' => 20, 'L' => 21,
        'M' => 22, 'N' => 23, 'O' => 24, 'P' => 25,
        'Q' => 26, 'R' => 27, 'S' => 28, 'T' => 29,
        'U' => 30, 'V' => 31, 'W' => 32, 'X' => 33,
        'Y' => 34, 'Z' => 35,
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
        'BAD_BBAN_COUNTRY_CODE_TYPE',
        'BAD_BBAN_CODE_TYPE',
        'BAD_BBAN_LENGTH',
        'BAD_BBAN_STRUCTURE',
        'BAD_BBAN_CHECK_DIGITS_TEST',
    );

    /**
     * Returns an array with the names of the countries
     * where the key of each element is the country code defined in ISO 3166.
     *
     * @return array
     */
    static function countriesList()
    {
        $countries = countries();
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
        $countries = sepaCountries();
        return $countries;
    }

    /**
     * Returns a string with the validation error.
     * If the string retruned is null the IBAN code is correct.
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
        if (is_null($country = getCountry(substr($iban,0,2)))) {
            return Iban::$error[0];
        }

        /** Checks if the length of the code provided corresponds to the country indicated. */
        if (strlen($iban) != $country['ibanLength']) {
            return Iban::$error[2];
        }

        /** Checks IBAN structure. */
        if (!Iban::validateStructure($country['ibanStructure'], $iban, 2)) {
            return Iban::$error[4];
        }

        /** IBAN check digits test*/
        if (Iban::checkDigits($iban) != 1) {
            return Iban::$error[5];
        }

        return null;
    }

    /**
     * Calculate IBAN code from BBAN code and country to which it belongs.
     * If the array.error returned is null returns the IBAN code else returns null.
     *
     * @param  string $country
     * @param  string $bban
     * @return array  (error, iban)
     */
    static function calculate ($country, $bban)
    {
        /** Checks if the function reveives a country string  */
        if (!is_string($country)) {
            return array(Iban::$error[6], null);
        }

        /** Checks if the country exists in the database. */
        if (is_null($countryData = getCountry($country))) {
            return array(Iban::$error[0], null);
        }

       /** Checks if the function receives a bban string. */
        if (!is_string($bban)) {
            return array(Iban::$error[7], null);
        }

        /** Converts uppercase. */
        $bban = strtoupper($bban);

        /** Checks if the length of the code provided corresponds to the country indicated. */
        if (strlen($bban) != $countryData['bbanLength']) {
            return array(Iban::$error[8], null);
        }

        /** Checks BBAN structure. */
        if (!Iban::validateStructure($countryData['bbanStructure'], $bban, 0)) {
            return array(Iban::$error[9], null);
        }

        /** BBAN check digits test. */
        $bbanCDTResult = bbanCheckDigitTest($country, $bban);
        if ($bbanCDTResult[0]) {

            if (!$bbanCDTResult[1]) {
                return array(Iban::$error[10], null);
            }
        }

        /** Calculate IBAN code. */
        $checkDigits = 98 - Iban::checkDigits($country.'00'.$bban);

        if ($checkDigits < 10) {
            return array(null, $country.'0'.$checkDigits.$bban);
        } else {
            return array(null, $country.$checkDigits.$bban);
        }
    }

    /**
     * Validate IBAN structure.
     *
     * @param  string  $structure pattern structure
     * @param  string  $code      code to parse
     * @param  integer $start     code position to start parsing
     * @return boolean
     */
    static function validateStructure($structure, $code, $start)
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

        $i = $start;
        $j = $start;

        while ($i < strlen($structure)):
            if (is_numeric($structure[$i])) {
                if ($substructure['length'] == 0) {
                    $substructure['length']  = $structure[$i];
                } else {
                    $substructure['length'] .= $structure[$i];
                }
            } else if ($structure[$i] == '!') {
                $substructure['fixedLength'] = true;
            } else {
                $substructure['kind']        = $structure[$i];
                $substructure['isCompleted'] = true;
            }

            if ($substructure['isCompleted']) {
                $substructureData = substr($code,$j,$substructure['length']);

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

        /** Move the four initial characters to the end of the string. */
        for ($i = 0; $i < 4; $i++) {
            array_push($ibanArray,array_shift($ibanArray));
        }

        /** Replace the letters in the string with digits. */
        $i = 0;
        foreach ($ibanArray as $ibanElement) {
            if (!is_numeric($ibanElement)) {
                $ibanArray[$i] = Iban::$lettersConversionTable[$ibanElement];
            }

            $i++;
        }

        $iban = implode($ibanArray);

        /** Calculate */
        $ibanSlice       = null;          /** Portion of the code to calculate the modulus.            */
        $ibanSliceIndex  = 0;             /** Position of the next portion to calculate.               */
        $ibanControlCode = 0;             /** Result of calculation.                                   */
        $modulus         = 97;            /** Modulus.                                                 */
        $ibanLength      = strlen($iban); /** Code length calculation pending.                         */
        $firstIbanSlice  = false;         /** True if modulus has been calculated on the first portion */

        /** Ignore leading zeroes. */
        $i = 0;
        while ($iban[$i] == 0):
            $ibanSliceIndex++;
            $ibanLength--;
            $i++;
        endwhile;

        while ($ibanLength != 0):
            if (!$firstIbanSlice) {
                if ($ibanLength >= 9) {
                    $ibanSlice       = substr($iban, $ibanSliceIndex, 9);
                    $ibanControlCode = $ibanSlice % $modulus;
                    $ibanSliceIndex += 9;
                    $ibanLength     -= 9;
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
                    $ibanSliceIndex += 7;
                    $ibanLength     -= 7;
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


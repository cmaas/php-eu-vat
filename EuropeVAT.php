<?php
/**
 * Helper classes for calculating EU VAT rates based on official tax regulations, which can be found here:
 * http://ec.europa.eu/taxation_customs/resources/documents/taxation/vat/how_vat_works/rates/vat_rates_en.pdf
 *
 * Current EU Tax Version: 1st January 2017
 * 
 * @author Christian Maas <christian@cmaas.de>
 * @copyright 2015, 2017 Christian Maas
 * @license MIT http://opensource.org/licenses/MIT
 */

/**
 * EuropeVAT is used to add or subtract tax (VAT) for a specific country and rate
 */
class EuropeVAT {

    /**
     * Add tax to a total $netAmount for a 2-letter $countryCode with a VATRate
     * @param float $netAmount
     * @param string $countryCode
     * @param string $rateType Default VATRates::RATE_STANDARD
     * @return array Keys taxRate, taxAmount, total
     */
    public static function addTax($netAmount, $countryCode, $rateType = VATRates::RATE_STANDARD) {
        $rate = static::getRateByCountryAndType($countryCode, $rateType);
        $ratePercent = $rate / 100;

        $taxAmount = $netAmount * $ratePercent;
        $total = $netAmount + $taxAmount;

        return [
            'taxRate' => $rate,
            'taxAmount' => $taxAmount,
            'total' => $total
        ];
    }

    /**
     * Subtract tax from a total $grossAmount for a 2-letter $countryCode with a VATRate
     * @param float $grossAmount
     * @param string $countryCode
     * @param string $rateType Default VATRates::RATE_STANDARD
     * @return array with keys taxRate, taxAmount, netAmount
     */
    public static function subtractTax($grossAmount, $countryCode, $rateType = VATRates::RATE_STANDARD) {
        $rate = static::getRateByCountryAndType($countryCode, $rateType);
        $ratePercent = $rate / 100;

        $netAmount = $grossAmount / (1 + $ratePercent);
        $taxAmount = $grossAmount - $netAmount;

        return [
            'taxRate' => $rate,
            'taxAmount' => $taxAmount,
            'netAmount' => $netAmount
        ];
    }

    /**
     * Return the rate as percent (0..100) for a $countryCode with $rateType
     * @throws \InvalidArgumentException if the provided 2-letter $countryCode has no such $rateType
     * @param string $countryCode
     * @param string $rateType
     * @return float Returns the rate as full percent (0..100), e. g. 13.5
     */
    protected static function getRateByCountryAndType($countryCode, $rateType) {
        $rate = null;
        $country = VATRates::getCountryRates($countryCode);

        switch ($rateType) {
            case VATRates::RATE_SUPER_REDUCED:
                $rate = $country['superReducedRate'];
                break;
            case VATRates::RATE_REDUCED:
                $reducedRate = $country['reducedRate'];
                if (is_array($reducedRate)) {
                    $rate = $reducedRate[0];
                }
                break;
            case VATRates::RATE_REDUCED2:
                $reducedRate = $country['reducedRate'];
                if (is_array($reducedRate) && count($reducedRate) > 1) {
                    $rate = $reducedRate[1];
                }
                break;
            case VATRates::RATE_PARKING:
                $rate = $country['parkingRate'];
                break;
            case VATRates::RATE_STANDARD:
            default:
                $rate = $country['standardRate'];
                break;
        }
        if ($rate === null) {
            throw new \InvalidArgumentException("Country '$countryCode' has no VAT rate for type '$rateType'.");
        }

        return $rate;
    }
}

/**
 * VATRates is a container for VAT rates of EU countries
 *
 * VATRates are based on the official tax document, which can be found here:
 * http://ec.europa.eu/taxation_customs/resources/documents/taxation/vat/how_vat_works/rates/vat_rates_en.pdf
 * There are 4 different kind of rates. Not all rates are supported in every country. If a rate is not supported,
 * it is null.
 *
 */
class VATRates {

    /**
     * Super Reduced Rate
     */
    const RATE_SUPER_REDUCED = 'RATE_SUPER_REDUCED';

    /**
     * Some countries have two reduced rates. This is the first.
     */
    const RATE_REDUCED = 'RATE_REDUCED';

    /**
     * Some countries have two reduced rates. This is the second.
     */
    const RATE_REDUCED2 = 'RATE_REDUCED2';

    /**
     * Standard VAT rate
     */
    const RATE_STANDARD = 'RATE_STANDARD';

    /**
     * Parking Rate
     */
    const RATE_PARKING = 'RATE_PARKING';

    /**
     * Returns all rates for a country
     * @throws \InvalidArgumentException if the 2-letter $countryCode is not a supported EU country
     * @param string $countryCode 2-letter EU country code
     * @return array like so: string name, string code, float superReducedRate, array reducedRate (1 or 2 elements of type float), float standardRate, float parkingRate
     */
    public static function getCountryRates($countryCode) {
        $countryCode = strtoupper($countryCode);
        if (!isset(static::$countries[$countryCode])) {
            throw new \InvalidArgumentException("Invalid country code '$countryCode'.");
        }
        $country = static::$countries[$countryCode];
        $country['code'] = $countryCode;
        return $country;
    }

    /**
     * Returns all standard rates for all available EU countries
     * @return array with the mapping: string countryCode => float rate (of type VATRate::RATE_STANDARD)
     */
    public static function getAllStandardRates() {
        $standardRates = [];
        foreach (static::$countries as $countryCode => $country) {
            $standardRates[$countryCode] = $country['standardRate'];
        }
        return $standardRates;
    }

    /**
     * Returns all countries with all rates
     * @return array with all data
     */
    public static function getAll() {
        return static::$countries;
    }

    /**
     * @var array Holds all countries and their rates
     */
    protected static $countries = [

        'BE' => [
            'name' => 'Belgium',
            'superReducedRate' => null,
            'reducedRate' => [6, 12],
            'standardRate' => 21,
            'parkingRate' => 12
        ],

        'BG' => [
            'name' => 'Bulgaria',
            'superReducedRate' => null,
            'reducedRate' => [9],
            'standardRate' => 20,
            'parkingRate' => null
        ],

        'CZ' => [
            'name' => 'Czech Republic',
            'superReducedRate' => null,
            'reducedRate' => [10, 15],
            'standardRate' => 21,
            'parkingRate' => null
        ],

        'DK' => [
            'name' => 'Denmark',
            'superReducedRate' => null,
            'reducedRate' => null,
            'standardRate' => 25,
            'parkingRate' => null
        ],

        'DE' => [
            'name' => 'Germany',
            'superReducedRate' => null,
            'reducedRate' => [7],
            'standardRate' => 19,
            'parkingRate' => null
        ],

        'EE' => [
            'name' => 'Estonia',
            'superReducedRate' => null,
            'reducedRate' => [9],
            'standardRate' => 20,
            'parkingRate' => null
        ],

        'IE' => [
            'name' => 'Ireland',
            'superReducedRate' => 4.8,
            'reducedRate' => [9, 13.5],
            'standardRate' => 23,
            'parkingRate' => 13.5
        ],

        'EL' => [
            'name' => 'Greece',
            'superReducedRate' => null,
            'reducedRate' => [6, 13],
            'standardRate' => 24,
            'parkingRate' => null
        ],

        'ES' => [
            'name' => 'Spain',
            'superReducedRate' => 4,
            'reducedRate' => [10],
            'standardRate' => 21,
            'parkingRate' => null
        ],

        'FR' => [
            'name' => 'France',
            'superReducedRate' => 2.1,
            'reducedRate' => [5.5, 10],
            'standardRate' => 20,
            'parkingRate' => null
        ],

        'HR' => [
            'name' => 'Croatia',
            'superReducedRate' => null,
            'reducedRate' => [5, 13],
            'standardRate' => 25,
            'parkingRate' => null
        ],

        'IT' => [
            'name' => 'Italy',
            'superReducedRate' => 4,
            'reducedRate' => [5, 10],
            'standardRate' => 22,
            'parkingRate' => null
        ],

        'CY' => [
            'name' => 'Cyprus',
            'superReducedRate' => null,
            'reducedRate' => [5, 9],
            'standardRate' => 19,
            'parkingRate' => null
        ],

        'LV' => [
            'name' => 'Latvia',
            'superReducedRate' => null,
            'reducedRate' => [12],
            'standardRate' => 21,
            'parkingRate' => null
        ],

        'LT' => [
            'name' => 'Lithuania',
            'superReducedRate' => null,
            'reducedRate' => [5, 9],
            'standardRate' => 21,
            'parkingRate' => null
        ],

        'LU' => [
            'name' => 'Luxembourg',
            'superReducedRate' => 3,
            'reducedRate' => [8],
            'standardRate' => 17,
            'parkingRate' => 14
        ],

        'HU' => [
            'name' => 'Hungary',
            'superReducedRate' => null,
            'reducedRate' => [5, 18],
            'standardRate' => 27,
            'parkingRate' => null
        ],

        'MT' => [
            'name' => 'Malta',
            'superReducedRate' => null,
            'reducedRate' => [5, 7],
            'standardRate' => 18,
            'parkingRate' => null
        ],

        'NL' => [
            'name' => 'Netherlands',
            'superReducedRate' => null,
            'reducedRate' => [6],
            'standardRate' => 21,
            'parkingRate' => null
        ],

        'AT' => [
            'name' => 'Austria',
            'superReducedRate' => null,
            'reducedRate' => [10, 13],
            'standardRate' => 20,
            'parkingRate' => 13
        ],

        'PL' => [
            'name' => 'Poland',
            'superReducedRate' => null,
            'reducedRate' => [5, 8],
            'standardRate' => 23,
            'parkingRate' => null
        ],

        'PT' => [
            'name' => 'Portugal',
            'superReducedRate' => null,
            'reducedRate' => [6, 13],
            'standardRate' => 23,
            'parkingRate' => 13
        ],

        'RO' => [
            'name' => 'Romania',
            'superReducedRate' => null,
            'reducedRate' => [5, 9],
            'standardRate' => 19,
            'parkingRate' => null
        ],

        'SI' => [
            'name' => 'Slovenia',
            'superReducedRate' => null,
            'reducedRate' => [9.5],
            'standardRate' => 22,
            'parkingRate' => null
        ],

        'SK' => [
            'name' => 'Slowakia',
            'superReducedRate' => null,
            'reducedRate' => [10],
            'standardRate' => 20,
            'parkingRate' => null
        ],

        'FI' => [
            'name' => 'Finland',
            'superReducedRate' => null,
            'reducedRate' => [10, 14],
            'standardRate' => 24,
            'parkingRate' => null
        ],

        'SE' => [
            'name' => 'Sweden',
            'superReducedRate' => null,
            'reducedRate' => [6, 12],
            'standardRate' => 25,
            'parkingRate' => null
        ],

        'UK' => [
            'name' => 'United Kingdom',
            'superReducedRate' => null,
            'reducedRate' => [5],
            'standardRate' => 20,
            'parkingRate' => null
        ],

    ];

}
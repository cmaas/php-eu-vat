# EU VAT Calculation Helper Classes

Businesses in the EU need to charge EU customers the VAT rates of the country the customer lives in.
This changed on January 1st, 2017. `EuropeVAT` has two helper classes to perform some tax calculations
and get the current rates.

All rates are listed here: [http://ec.europa.eu/taxation_customs/resources/documents/taxation/vat/how_vat_works/rates/vat_rates_en.pdf](http://ec.europa.eu/taxation_customs/resources/documents/taxation/vat/how_vat_works/rates/vat_rates_en.pdf)

## How to Use

Just grab a copy of `EuropeVAT.php`, include it in your PHP project and use it. You can either use the class directly to
add or subtract taxes like so:

    $results[] = EuropeVAT::addTax(100, 'DE');

    $results[] = EuropeVAT::subtractTax(49, 'ES', VATRates::RATE_REDUCED);
    // 2-letter country code can be any case; it will be converted to uppercase
    $results[] = EuropeVAT::addTax(36.95, 'hu');

Or you can work with the rates data like so:

    $results[] = VATRates::getCountryRates('LU');
    // more functions, if you want to populate a dropdown-list:
    // all standard rates as an array with this mapping: string countryCode => float rate
    $results[] = VATRates::getAllStandardRates();
    // get everything
    $results[] = VATRates::getAll();

## Example Output

    Array
    (
        [0] => Array
            (
                [taxRate] => 19
                [taxAmount] => 19
                [total] => 119
            )

        [1] => Array
            (
                [taxRate] => 10
                [taxAmount] => 4.4545454545455
                [netAmount] => 44.545454545455
            )

        [2] => Array
            (
                [taxRate] => 27
                [taxAmount] => 9.9765
                [total] => 46.9265
            )

        [3] => Array
            (
                [name] => Luxembourg
                [superReducedRate] => 3
                [reducedRate] => Array
                    (
                        [0] => 8
                    )

                [standardRate] => 17
                [parkingRate] => 14
                [code] => LU
            )

        [4] => Array
            (
                [BE] => 21
                [BG] => 20
                [CZ] => 21
    ...

See `examples.php`

## Supported Rates

Please see the link above for all rates that are supported by each countries. There are 4 different rates:

* Super Reduced Rate (`RATE_SUPER_REDUCED`)
* Reduced Rate (`RATE_REDUCED`). Please note that some countries have two different reduced rates. The reduced rate is returned as an array. You can address both rates by either `RATE_REDUCED` or `RATE_REDUCED2`
* Standard Rate (`RATE_STANDARD`). This is the most commonly used VAT rate
* Parking Rate (`RATE_PARKING`)

You need to know which type of rate applies to your product or business.

## License & Liability

MIT License.

Use at your own risk. The rates are compiled based on the document mentioned above. I take no responsibility for the correctness of any rates although I double-checked them as of June 23th, 2017.
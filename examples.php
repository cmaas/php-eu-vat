<?php

require 'EuropeVAT.php';

$results = [];

/*
 * Working with taxes, adding and subtracting
 */
$results[] = EuropeVAT::addTax(100, 'DE');

$results[] = EuropeVAT::subtractTax(49, 'ES', VATRates::RATE_REDUCED);
// 2-letter country code can be any case; it will be converted to uppercase
$results[] = EuropeVAT::addTax(36.95, 'hu');

/**
 * Working with countries and their rates
 */

// gets all rates for a country
$results[] = VATRates::getCountryRates('LU');

// more functions, if you want to populate a dropdown-list:
// all standard rates as an array with this mapping: string countryCode => float rate
$results[] = VATRates::getAllStandardRates();
// get everything
$results[] = VATRates::getAll();

print_r($results);

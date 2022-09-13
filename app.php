<?php
# Dependencies
require 'vendor/autoload.php';

require 'inc/Calendarioslaboral.class.php';


$scraper = new Calendarioslaboral();

/*******************
 * get list of years available for validation
 */
var_dump($scraper->getYearsAvailable()); // return array

/*******************
 * get list of provinces available for validation
 */
var_dump($scraper->getProvinces()); // return array

/*******************
 * get list of holidays of the selected province
 */
$holidays = $scraper->getHolidays('madrid'); // return array

/*******************
 * Check if it is a holiday
 */

var_dump($scraper->isHoliday('2022-01-01', $holidays)); // return bool;

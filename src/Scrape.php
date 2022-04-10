<?php

namespace App;

require 'vendor/autoload.php';


class Scrape
{
    private array $products = [];




    public function run(): void
    {

        //iterating through each page of the website
        for ($pag = 1; $pag <= 3; $pag++) {

            //creating a temporary array for each page 
            $temprod = [];

            $document = ScrapeHelper::fetchDocument('https://www.magpiehq.com/developer-challenge/smartphones?page=' . strval($pag));
            $baseUrl = "https://www.magpiehq.com/developer-challenge";


            //calculated number of phones based on the number of h3 tags, and by how many variants of colour there are for each phone 
            $noOfPhones = $document->filter('h3')->count();

            $differentColours = $noOfPhones;


            //iterating through each phone from the page
            for ($i = 0; $i <= $noOfPhones - 1; $i++) {

                $shipmentMsg = null;
                $isAvailable = false;
                $shipmentDate = null;

                //created the variable productPath so the code looks cleaner, I am using this variable to access the children of the current phone 
                $productPath = $document->filter('div')->eq(2)->children()->eq($i)->children();
                $noOfColors = $productPath->filter('span')->count() - 2;

                //gathering the title, capacity, color, image source, price and availability for each phone 
                $title = $productPath->filter('h3')->eq(0)->text();
                $capacity = $productPath->filter('span')->eq(1)->text();
                $color = $productPath->filter('span')->eq(2)->attr('data-colour');
                $imgSource = $productPath->filter('img')->attr('src');
                $price = $productPath->filter('[class="my-8 block text-center text-lg"]')->text();
                $availability = $productPath->filter('[class="my-4 text-sm block text-center"]')->text();


                //removing the pound symbol from the price variable 
                $price = substr($price, 2);

                //transforming the capacity variable into MB 
                if (strcmp("GB", substr($capacity, -2)) == 0) {
                    $capacity = substr($capacity, 0);
                    $capacity = trim($capacity);
                    $capacity_integer = (int)$capacity;
                    $capacity_integer = $capacity_integer * 1000;
                }

                //concatenating the string from the source with the base 
                $fullImgSource = $baseUrl . substr($imgSource, 2);

                //deciding if the product is available or not, based on the characters contained by $availability
                $outOfStock = "Availability: Out of Stock";
                if (strcmp($outOfStock, $availability) == 0) {
                    $isAvailable = false;
                }
                else {
                    $isAvailable = true;
                }


                //we have shimpentMsg initialised with null, and if there is a message about the delivery it will be assigned to it
                $ship = $document->filter('div')->eq(2)->children()->eq($i)->children()->filter('div')->last()->text();
                if ($ship != $availability) {

                    $shipmentMsg = $ship;

                    //getting the date of shippment by finding the index of the first integer in the string 
                    for ($k = 0; $k <= strlen($shipmentMsg) - 1; $k++) {
                        if (is_numeric($shipmentMsg[$k]) == 1) {
                            $shipmentDate = substr($shipmentMsg, $k);
                            $shipmentDate = strtotime($shipmentDate);
                            $shipmentDate = date('d-m-Y', $shipmentDate);
                            //formating the date 
                            break;
                        }
                    }

                }


                //creating a new product using the constructor 
                $temprod[$i] = new Product($title, $price, $fullImgSource, $capacity_integer, $color, $availability, $isAvailable, $shipmentMsg, $shipmentDate);

                //if there are more than 1 colors available for the phone, we create a phone for each color 
                if ($noOfColors > 1) {
                    for ($j = 1; $j <= $noOfColors - 1; $j++) {
                        $color = $productPath->filter('span')->eq(2 + $j)->attr('data-colour');
                        $temprod[$differentColours + $j] = new Product($title, $price, $fullImgSource, $capacity_integer, $color, $availability, $isAvailable, $shipmentMsg, $shipmentDate);
                    }

                     //this variable is memorising how many variants of colors are, excluding the default one 
                    $differentColours = $differentColours + $noOfColors;
                }

            }

            //merging every temporary array to the main products array 
            $this->products = array_merge($this->products, $temprod);
        }


        //making sure each object in the products array is unique 
        $this->products = array_unique($this->products , SORT_REGULAR);

        //added JSON_PRETTY_PRINT to make the print look better and JSON_UNSCAPED_UNICODE to make the json be able to prin the pound symbol
        file_put_contents('output.json', json_encode($this->products, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

    
}
}

$scrape = new Scrape();
$scrape->run();

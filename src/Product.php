<?php

namespace App;

class Product
{

    var $title;
    var $price;
    var $imageUrl;
    var $capacityMB;
    var $colour;
    var $availabilityText;
    var $isAvailable;
    var $shippingText;
    var $shippingDate;

    function __construct($title, $price, $imageUrl, $capacityMB, $colour, $availabilityText, $isAvailable, $shippingText, $shippingDate){

        $this->title=$title;
        $this->price=$price;
        $this->imageUrl=$imageUrl;
        $this->capacityMB=$capacityMB;
        $this->colour=$colour;
        $this->availabilityText=$availabilityText;
        $this->isAvailable=$isAvailable;
        $this->shippingText=$shippingText;
        $this->shippingDate=$shippingDate;
    }


}



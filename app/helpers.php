<?php

function PresentPrice($price)
{
    return '$'.number_format($price / 100 , 2);
}
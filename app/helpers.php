<?php

function PresentPrice($price)
{
    return '$'.number_format($price / 100 , 2);
}

function setActiveCategory($category, $output = 'active')
{
    return request()->category == $category ? $output : '';
}

function productImage($path)
{
    return  ($path != null) && file_exists('storage/'. $path) ? asset('storage/'. $path) : asset('img/not-found.png');

}
<?php

namespace App\Enums;

enum CmsPage: string
{
    case HOME = 'home';
    case ABOUT = 'about';
    case SERVICES = 'services';
    case ARTIST_SPOTLIGHT = 'artist_spotlight';
    case BUSINESS_SPOTLIGHT = 'business_spotlight';
    case SPOTLIGHT_LADDER = 'spotlight_ladder';
    case EVENTS = 'events';
    case SHOP = 'shop';
    case SPONSORSHIP = 'sponsorship';
}

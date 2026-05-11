<?php

namespace App\Enums;

enum CmsSection: string
{
    case HERO = 'hero';
    case SPOTLIGHT = 'spotlight';
    case PARTNERS = 'partners';
    case FEATURES = 'features';
    case WHY_CHOOSE = 'why_choose';
    case CORE_VALUES = 'core_values';
    case WHAT_YOU_GET = 'what_you_get';
    case BOSS_BEGINNINGS = 'boss_beginnings';
    case HIGHLIGHTS = 'highlights';
    case EVENTS = 'events';
    case SHOP = 'shop';
    case CTA = 'cta';
    case NEWSLETTER = 'newsletter';

    // About Page
    case ABOUT_HERO = 'about_hero';
    case ABOUT_SOCIETY = 'about_society';
    case ABOUT_ORIGIN = 'about_origin';
    case ABOUT_MISSION = 'about_mission';
    case ABOUT_WHAT_WE_DO = 'about_what_we_do';
    case ABOUT_HOW_IT_WORKS = 'about_how_it_works';
}

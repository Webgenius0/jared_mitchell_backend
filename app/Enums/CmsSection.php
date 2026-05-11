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
    case ABOUT_WHO_WE_SERVE = 'about_who_we_serve';
    case ABOUT_WHY_EXISTS = 'about_why_exists';
    case ABOUT_OUR_IMPACT = 'about_our_impact';
    case ABOUT_FOUNDER_MESSAGE = 'about_founder_message';
    case ABOUT_JOIN = 'about_join';
    case ABOUT_NEWSLETTER = 'about_newsletter';
    case ABOUT_SPONSORS = 'about_sponsors';
    
    // Services Page
    case SERVICES_HERO = 'services_hero';
    case SERVICES_OVERVIEW = 'services_overview';
    case SERVICES_GROW = 'services_grow';
    case SERVICES_PARTNERS = 'services_partners';
    case SERVICES_WHO_FOR = 'services_who_for';
    case SERVICES_ARTIST_SPOTLIGHT = 'services_artist_spotlight';
    case SERVICES_BUSINESS_SPOTLIGHT = 'services_business_spotlight';
    case SERVICES_RISK_FREE = 'services_risk_free';
    case SERVICES_NEWSLETTER = 'services_newsletter';

    // Artist Spotlight Page
    case ARTIST_SPOTLIGHT_HERO = 'artist_spotlight_hero';
    case ARTIST_SPOTLIGHT_VIDEO = 'artist_spotlight_video';
    case ARTIST_SPOTLIGHT_LIST = 'artist_spotlight_list';
    case ARTIST_SPOTLIGHT_HIGHLIGHTS = 'artist_spotlight_highlights';
    case ARTIST_SPOTLIGHT_LADDER = 'artist_spotlight_ladder';
    case ARTIST_SPOTLIGHT_JOIN = 'artist_spotlight_join';
    case ARTIST_SPOTLIGHT_INTERVIEW = 'artist_spotlight_interview';
    case ARTIST_SPOTLIGHT_WHY_EXISTS = 'artist_spotlight_why_exists';
    
    // Business Spotlight Page
    case BUSINESS_SPOTLIGHT_HERO = 'business_spotlight_hero';
    case BUSINESS_SPOTLIGHT_VIDEO = 'business_spotlight_video';
    case BUSINESS_SPOTLIGHT_LIST = 'business_spotlight_list';
    case BUSINESS_SPOTLIGHT_HIGHLIGHTS = 'business_spotlight_highlights';
    case BUSINESS_SPOTLIGHT_PICKS = 'business_spotlight_picks';
    case BUSINESS_SPOTLIGHT_LADDER = 'business_spotlight_ladder';
}

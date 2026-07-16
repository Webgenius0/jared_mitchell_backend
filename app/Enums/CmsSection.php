<?php

namespace App\Enums;

enum CmsSection: string
{
    case HERO = 'hero';
    case SPOTLIGHT = 'spotlight';
    case BOSS_BEGINNING_WINNERS = 'boss_beginning_winners';
    case NEXT_BOSS_BEGINNINGS_WESTSIDE_BEAUTY_LOUNGE = 'next_boss_beginnings_westside_beauty_lounge';
    case UPCOMING_EVENTS = 'upcoming_events';
    case PAST_EVENT_HIGHLIGHTS = 'past_event_highlights';
    case EVENT_SPONSORS = 'event_sponsors';
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
    case SERVICES_FAQ = 'services_faq';

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
    case BUSINESS_SPOTLIGHT_JOIN = 'business_spotlight_join';
    case BUSINESS_SPOTLIGHT_INTERVIEW = 'business_spotlight_interview';
    case BUSINESS_SPOTLIGHT_WHY_EXISTS = 'business_spotlight_why_exists';

    // Spotlight Ladder Page
    case SPOTLIGHT_LADDER_HERO = 'spotlight_ladder_hero';
    case SPOTLIGHT_LADDER_DETAILS = 'spotlight_ladder_details';

    // Event Page
    case EVENTS_PAGE_HERO = 'events_page_hero';
    case EVENTS_PAGE_VIDEO = 'events_page_video';
    case EVENTS_PAGE_HOST = 'events_page_host';
    case EVENTS_PAGE_VENDOR = 'events_page_vendor';
    case EVENTS_PAGE_BOOTH_FEATURES = 'events_page_booth_features';
    case EVENTS_PAGE_UPCOMING_EVENT1 = 'events_page_upcoming_event1';
    case EVENTS_PAGE_UPCOMING_EVENT2 = 'events_page_upcoming_event2';
    case EVENTS_PAGE_EVENT_GALLERY = 'events_page_event_gallery';
    case EVENTS_PAGE_PAST_EVENT_HIGHLIGHTS = 'events_page_past_event_highlights';

    // Shop Page
    case SHOP_PAGE_HERO = 'shop_page_hero';
    case SHOP_PAGE_FEATURES = 'shop_page_features';
    case SHOP_PAGE_SUPPORT = 'shop_page_support';
    case SHOP_PAGE_FOOTER_FEATURES = 'shop_page_footer_features';
    case SHOP_PAGE_FEATURED = 'shop_page_featured';
    case SHOP_PAGE_LIMITED_DROPS = 'shop_page_limited_drops';
    case SHOP_PAGE_FAQ = 'shop_page_faq';

    // Sponsorship Page
    case SPONSORSHIP_PAGE_HERO = 'sponsorship_page_hero';
    case SPONSORSHIP_PAGE_VIDEO = 'sponsorship_page_video';
    case SPONSORSHIP_PAGE_WHY = 'sponsorship_page_why';
    case SPONSORSHIP_PAGE_STEPS = 'sponsorship_page_steps';
    case SPONSORSHIP_PAGE_LEVELS_HEADER = 'sponsorship_page_levels_header';
    case SPONSORSHIP_PAGE_FOOTER = 'sponsorship_page_footer';

    // ==================== Boss Beginnings Sections ====================
    case BOSS_BEGINNINGS_HERO = 'boss_beginnings_hero';
    case BOSS_BEGINNINGS_FEATURES = 'boss_beginnings_features';
    case BOSS_BEGINNINGS_VIDEO_GALLERY = 'boss_beginnings_video_gallery';
    case BOSS_BEGINNINGS_STEPS = 'boss_beginnings_steps';
    case BOSS_BEGINNINGS_SECTION5 = 'boss_beginnings_section5';
    case BOSS_BEGINNINGS_DYNAMIC = 'boss_beginnings_dynamic';

    // Boss Beginnings Winner Chosen Sections
    case BOSS_BEGINNINGS_WINNER_CHOSEN_SECTION1 = 'boss_beginnings_winner_chosen_section1';
    case BOSS_BEGINNINGS_WINNER_CHOSEN_SECTION2 = 'boss_beginnings_winner_chosen_section2';
}

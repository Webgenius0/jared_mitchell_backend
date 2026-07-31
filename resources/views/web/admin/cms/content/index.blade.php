@extends('layout.master-layout')
@section('title', 'CMS Content Management')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Manage CMS Content ({{ ucfirst($currentPage) }})</h4>
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('show.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">CMS Content</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="row">
                {{-- Left Sidebar: Pages --}}
                <div class="col-lg-3">
                    @include('web.admin.cms.partials._sidebar')
                </div>

                {{-- Right Content: Sections --}}
                <div class="col-lg-9">
                    @if ($currentPage === 'home')
                        @include('web.admin.cms.content.partials._home')
                    @elseif($currentPage === 'about')
                        @include('web.admin.cms.content.partials._about')
                    @elseif($currentPage === 'services')
                        @include('web.admin.cms.content.partials._services')
                    @elseif($currentPage === 'artist_spotlight')
                        @include('web.admin.cms.content.partials._artist_spotlight')
                    @elseif($currentPage === 'business_spotlight')
                        @include('web.admin.cms.content.partials._business_spotlight')
                    @elseif($currentPage === 'spotlight_ladder')
                        @include('web.admin.cms.content.partials._spotlight_ladder')
                    @elseif($currentPage === 'events')
                        @include('web.admin.cms.content.partials._events')
                    @elseif($currentPage === 'shop')
                        @include('web.admin.cms.content.partials._shop')
                        {{-- @elseif($currentPage === 'sponsorship')
                        @include('web.admin.cms.content.partials._sponsorship') --}}
                    @elseif($currentPage === 'boss_beginnings')
                        @include('web.admin.cms.content.partials._boss_beginnings')
                    @elseif($currentPage === 'boss_beginnings_winner_chosen')
                        @include('web.admin.cms.content.partials._boss_beginnings_winner_chosen')
                    @elseif($currentPage === 'rounds')
                        @include('web.admin.cms.content.partials._rounds')
                    @else
                        <div class="alert alert-info">
                            Please select a page from the left sidebar to manage its content.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
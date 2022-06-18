@extends('frontend.layouts.app')

@section('title', __('My Account'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <x-frontend.card>
                <x-slot name="header">
                    @lang('My Account')
                </x-slot>

                <x-slot name="body">
                    <div class="row">
                        <div class="col-3">
                            <div class="nav flex-column nav-pills" id="my-profile-tabs" role="tablist" aria-orientation="vertical">
                                <x-utils.link
                                    :text="__('My Profile')"
                                    class="nav-link active"
                                    id="my-profile-tab"
                                    data-toggle="pill"
                                    href="#my-profile"
                                    role="tab"
                                    aria-controls="my-profile"
                                    aria-selected="true" />

                                <x-utils.link
                                    :text="__('Edit Information')"
                                    class="nav-link"
                                    id="information-tab"
                                    data-toggle="pill"
                                    href="#information"
                                    role="tab"
                                    aria-controls="information"
                                    aria-selected="false"/>

                                @if (! $logged_in_user->isSocial())
                                    <x-utils.link
                                        :text="__('Password')"
                                        class="nav-link"
                                        id="password-tab"
                                        data-toggle="pill"
                                        href="#password"
                                        role="tab"
                                        aria-controls="password"
                                        aria-selected="false" />
                                @endif

                                <x-utils.link
                                    :text="__('Two Factor Authentication')"
                                    class="nav-link"
                                    id="two-factor-authentication-tab"
                                    data-toggle="pill"
                                    href="#two-factor-authentication"
                                    role="tab"
                                    aria-controls="two-factor-authentication"
                                    aria-selected="false"/>
                            </div><!--nav-->
                        </div><!--col-3-->

                        <div class="col-9">
                            <div class="tab-content" id="my-profile-tabsContent">
                                <div class="tab-pane fade show active" id="my-profile" role="tabpanel" aria-labelledby="my-profile-tab">
                                    @include('frontend.user.account.tabs.profile')
                                </div><!--tab-profile-->

                                <div class="tab-pane fade" id="information" role="tabpanel" aria-labelledby="information-tab">
                                    @include('frontend.user.account.tabs.information')
                                </div><!--tab-information-->

                                @if (! $logged_in_user->isSocial())
                                    <div class="tab-pane fade" id="password" role="tabpanel" aria-labelledby="password-tab">
                                        @include('frontend.user.account.tabs.password')
                                    </div><!--tab-password-->
                                @endif

                                <div class="tab-pane fade" id="two-factor-authentication" role="tabpanel" aria-labelledby="two-factor-authentication-tab">
                                    @include('frontend.user.account.tabs.two-factor-authentication')
                                </div><!--tab-information-->
                            </div><!--tab-content-->
                        </div><!--col-9-->
                    </div><!--row-->
                </x-slot>
            </x-frontend.card>
        </div><!--col-md-12-->
    </div><!--row-->
@endsection

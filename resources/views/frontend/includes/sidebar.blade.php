<div class="c-sidebar c-sidebar-dark c-sidebar-fixed c-sidebar-lg-show" id="sidebar">
    <div class="c-sidebar-brand d-lg-down-none">
        <img src="{{ asset('img/icons/Itsrapid_logo.png') }}" width="90%" height="100%">
    </div><!--c-sidebar-brand-->

    <ul class="c-sidebar-nav">
        @if ( $logged_in_user->isMember() && !$logged_in_user->can_upload_image )
            <li class="c-sidebar-nav-item">
                <x-utils.link
                    class="c-sidebar-nav-link"
                    :href="route('frontend.file.index')"
                    icon="c-sidebar-nav-icon cil-speedometer"
                    :text="__('File Browser')" />
            </li>
        @else
            <li class="c-sidebar-nav-dropdown {{ activeClass(Route::is('frontend.file.*'), 'c-open c-show') }}">
                <x-utils.link
                    :href="route('frontend.file.index')"
                    icon="c-sidebar-nav-icon cil-speedometer"
                    class="c-sidebar-nav-dropdown-toggle"
                    :text="__('Files')" />

                <ul class="c-sidebar-nav-dropdown-items">
                    <li class="c-sidebar-nav-item">
                        <x-utils.link
                            class="c-sidebar-nav-link"
                            :href="route('frontend.file.index')"
                            :text="__('File Browser')" />
                    </li>
                    <li class="c-sidebar-nav-item">
                        <x-utils.link
                            :href="route('frontend.file.uploadimg.index')"
                            class="c-sidebar-nav-link"
                            :text="__('Upload Images')" />
                    </li>
                </ul>
            </li>
        @endif
        <li class="c-sidebar-nav-dropdown {{ activeClass(Route::is('frontend.file.*'), 'c-open c-show') }}">
                <x-utils.link
                    icon="c-sidebar-nav-icon cil-speedometer"
                    class="c-sidebar-nav-dropdown-toggle"
                    :text="__('Create')" />

                <ul class="c-sidebar-nav-dropdown-items">
                <li class="c-sidebar-nav-item">
                        <x-utils.link
                        class="c-sidebar-nav-link"
                        :active="activeClass(Route::is('frontend.banner.index') || Route::is('frontend.index'), 'c-active')"
                        :href="route('frontend.banner.index')"
                        :text="__('Banner')" />
                    </li>
                    <li class="c-sidebar-nav-item">
                        <x-utils.link
                         class="c-sidebar-nav-link"
                         :href="route('frontend.banner.index')"
                        :text="__('Infographic')" />
                    </li>
                    <li class="c-sidebar-nav-item">
                        <x-utils.link
                        class="c-sidebar-nav-link" 
                        :active="activeClass(Route::is('frontend.create.product') , 'c-active')"
                        :href="route('frontend.create.product')"
                        :text="__('Product')" />
                    </li>
                    <li class="c-sidebar-nav-item">
                        <x-utils.link
                        class="c-sidebar-nav-link"
                        :active="activeClass(Route::is('frontend.create.nft') , 'c-active')"
                        :href="route('frontend.create.nft')"
                        :text="__('NFT')" />
                    </li>                    
                    <li class="c-sidebar-nav-item">
                        <x-utils.link
                            class="c-sidebar-nav-link"
                            :href="route('frontend.video.index')"
                            :text="__('Video')" />
                    </li>
                </ul>
        </li>

        <li class="c-sidebar-nav-item">
            <x-utils.link
                class="c-sidebar-nav-link"
                :href="route('frontend.history.index')"
                icon="c-sidebar-nav-icon cil-speedometer"
                :text="__('Drafts')" />
        </li>
        <li class="c-sidebar-nav-item">
            <x-utils.link
                class="c-sidebar-nav-link"
                :href="route('frontend.projects.index')"
                icon="c-sidebar-nav-icon cil-speedometer"
                :text="__('Projects')" />
        </li>
    </ul>

    <button class="c-sidebar-minimizer c-class-toggler" type="button" data-target="_parent" data-class="c-sidebar-minimized"></button>
</div><!--sidebar-->

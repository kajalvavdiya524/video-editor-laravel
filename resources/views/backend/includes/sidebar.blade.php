<div class="c-sidebar c-sidebar-dark c-sidebar-fixed c-sidebar-lg-show" id="sidebar">
    <div class="c-sidebar-brand d-lg-down-none">
        <img src="{{ asset('img/icons/Itsrapid_logo.png') }}" width="90%" height="100%">
    </div><!--c-sidebar-brand-->

    <ul class="c-sidebar-nav">
        <li class="c-sidebar-nav-item">
            <x-utils.link
                class="c-sidebar-nav-link"
                :href="route('admin.dashboard')"
                :active="activeClass(Route::is('admin.dashboard'), 'c-active')"
                icon="c-sidebar-nav-icon cil-speedometer"
                :text="__('Dashboard')" />
        </li>

        <li class="c-sidebar-nav-dropdown {{ activeClass(Route::is('admin.auth.company.*') || Route::is('admin.auth.team.*') || Route::is('admin.auth.user.*'), 'c-open c-show') }}">
            <x-utils.link
                href="#"
                icon="c-sidebar-nav-icon cil-settings"
                class="c-sidebar-nav-dropdown-toggle"
                :text="__('Administration')" />

            <ul class="c-sidebar-nav-dropdown-items">
                @if ( $logged_in_user->isMasterAdmin() )
                    <li class="c-sidebar-nav-item">
                        <x-utils.link
                            :href="route('admin.auth.company.index')"
                            class="c-sidebar-nav-link"
                            :text="__('Companies')"
                            :active="activeClass(Route::is('admin.auth.company.*'), 'c-active')" />
                    </li>
                @else
                    <li class="c-sidebar-nav-item">
                        <x-utils.link
                            :href="route('admin.auth.company.edit', $logged_in_user->company_id)"
                            class="c-sidebar-nav-link"
                            :text="__('Companies')"
                            :active="activeClass(Route::is('admin.auth.company.edit'), 'c-active')" />
                    </li>
                @endif
                <li class="c-sidebar-nav-item">
                    <x-utils.link
                        :href="route('admin.auth.team.index')"
                        class="c-sidebar-nav-link"
                        :text="__('Teams')"
                        :active="activeClass(Route::is('admin.auth.team.*'), 'c-active')" />
                </li>
                <li class="c-sidebar-nav-item">
                    <x-utils.link
                        :href="route('admin.auth.user.index')"
                        class="c-sidebar-nav-link"
                        :text="__('Users')"
                        :active="activeClass(Route::is('admin.auth.user.*'), 'c-active')" />
                </li>
                @if ( $logged_in_user->isMasterAdmin() )
                <li class="c-sidebar-nav-item">
                    <x-utils.link
                        :href="route('admin.auth.apikeys.index')"
                        class="c-sidebar-nav-link"
                        :text="__('API Keys')"
                        :active="activeClass(Route::is('admin.auth.apikeys.*'), 'c-active')" />
                </li>

                <li class="c-sidebar-nav-item">
                    <x-utils.link
                        :href="route('admin.auth.job.index')"
                        class="c-sidebar-nav-link"
                        :text="__('Jobs')"
                        :active="activeClass(Route::is('admin.auth.job.*'), 'c-active')" />
                </li>
                @endif

            </ul>
        </li>

        <li class="c-sidebar-nav-dropdown {{ activeClass(Route::is('admin.auth.template.*') || Route::is('admin.auth.settings.template.*'), 'c-open c-show') }}">
            <x-utils.link
                href="#"
                icon="c-sidebar-nav-icon cil-settings"
                class="c-sidebar-nav-dropdown-toggle"
                :text="__('Design')" />
            <ul class="c-sidebar-nav-dropdown-items">
                @if ( $logged_in_user->isMasterAdmin() )
                    <li class="c-sidebar-nav-item">
                        <x-utils.link
                            :href="route('admin.auth.customer.index')"
                            class="c-sidebar-nav-link"
                            :text="__('Customers')"
                            :active="activeClass(Route::is('admin.auth.customer.*'), 'c-active')" />
                    </li>
                @endif
                <li class="c-sidebar-nav-item">
                    <x-utils.link
                        :href="route('admin.auth.template.index')"
                        class="c-sidebar-nav-link"
                        :text="__('Templates')"
                        :active="activeClass(Route::is('admin.auth.template.*'), 'c-active')" />
                </li>
                <li class="c-sidebar-nav-item">
                    <x-utils.link
                        :href="route('admin.auth.positioning.index')"
                        class="c-sidebar-nav-link"
                        :text="__('Positioning')"
                        :active="activeClass(Route::is('admin.auth.positioning.*'), 'c-active')" />
                </li>
                @if ( !$logged_in_user->isMember() )
                    <li class="c-sidebar-nav-item">
                        <x-utils.link
                            :href="route('admin.auth.settings.template.index')"
                            class="c-sidebar-nav-link"
                            :text="__('Themes')"
                            :active="activeClass(Route::is('admin.auth.settings.template.*'), 'c-active')" />
                    </li>
                @endif
                @if ( !$logged_in_user->isMember() )
                    <li class="c-sidebar-nav-item">
                        <x-utils.link
                            :href="route('admin.auth.settings.imagelist.index')"
                            class="c-sidebar-nav-link"
                            :text="__('Images')"
                            :active="activeClass(Route::is('admin.auth.imagelist.*'), 'c-active')" />
                    </li>
                @endif
            </ul>
        </li>

        <li class="c-sidebar-nav-dropdown {{ activeClass(Route::is('admin.auth.settings.updatefile.*') || Route::is('admin.auth.settings.advanced.*'), 'c-open c-show') }}">
            <x-utils.link
                href="#"
                icon="c-sidebar-nav-icon cil-settings"
                class="c-sidebar-nav-dropdown-toggle"
                :text="__('Settings')" />

            <ul class="c-sidebar-nav-dropdown-items">
                <li class="c-sidebar-nav-item">
                    <x-utils.link
                        :href="route('admin.auth.settings.updatefile.index')"
                        class="c-sidebar-nav-link"
                        :text="__('Update Files')"
                        :active="activeClass(Route::is('admin.auth.settings.updatefile.*'), 'c-active')" />
                </li>
                <li class="c-sidebar-nav-item">
                    <x-utils.link
                        :href="route('admin.auth.settings.advanced.index')"
                        class="c-sidebar-nav-link"
                        :text="__('Advanced')"
                        :active="activeClass(Route::is('admin.auth.settings.advanced.*'), 'c-active')" />
                </li>
            </ul>
        </li>

        <li class="c-sidebar-nav-dropdown {{ activeClass(Route::is('admin.auth.video.*'), 'c-open c-show') }}">
            <x-utils.link
                href="#"
                icon="c-sidebar-nav-icon cil-settings"
                class="c-sidebar-nav-dropdown-toggle"
                :text="__('Video')" />

            <ul class="c-sidebar-nav-dropdown-items">
                <li class="c-sidebar-nav-item">
                    <x-utils.link
                            :href="route('admin.auth.video.templates.index')"
                            class="c-sidebar-nav-link"
                            :text="__('Templates')"
                            :active="activeClass(Route::is('admin.auth.video.templates.*'), 'c-active')" />
                </li>
                <li class="c-sidebar-nav-item">
                    <x-utils.link
                            :href="route('admin.auth.video.themes.index')"
                            class="c-sidebar-nav-link"
                            :text="__('Themes')"
                            :active="activeClass(Route::is('admin.auth.video.themes.*'), 'c-active')" />
                </li>
                <li class="c-sidebar-nav-item">
                    <x-utils.link
                            :href="route('admin.auth.video.drafts.index', array('drafts' => true))"
                            class="c-sidebar-nav-link"
                            :text="__('Drafts')"
                            :active="activeClass(Route::is('admin.auth.video.drafts.*'), 'c-active')" />
                </li>
                <li class="c-sidebar-nav-item">
                    <x-utils.link
                        :href="route('admin.auth.video.projects.index')"
                        class="c-sidebar-nav-link"
                        :text="__('Projects')"
                        :active="activeClass(Route::is('admin.auth.video.projects.*'), 'c-active')" />
                </li>
                <li class="c-sidebar-nav-item">
                    <x-utils.link
                        :href="route('admin.auth.video.media.folder.index')"
                        class="c-sidebar-nav-link"
                        :text="__('Media')"
                        :active="activeClass(Route::is('admin.auth.video.media.*'), 'c-active')" />
                </li>
                <li class="c-sidebar-nav-item">
                    <x-utils.link
                        :href="route('admin.auth.video.shares.index')"
                        class="c-sidebar-nav-link"
                        :text="__('Shares')"
                        :active="activeClass(Route::is('admin.auth.video.shares.*'), 'c-active')" />
                </li>
                <li class="c-sidebar-nav-item">
                    <x-utils.link
                        :href="route('admin.auth.video.logs.index')"
                        class="c-sidebar-nav-link"
                        :text="__('Logs')"
                        :active="activeClass(Route::is('admin.auth.video.logs.*'), 'c-active')" />
                </li>
            </ul>
        </li>
    </ul>

    <button class="c-sidebar-minimizer c-class-toggler" type="button" data-target="_parent" data-class="c-sidebar-minimized"></button>
</div><!--sidebar-->

<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    |
    | Here you can change the default title of your admin panel.
    |
    | For detailed instructions you can look the title section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'title' => 'AdminLTE 3',
    'title_prefix' => '',
    'title_postfix' => '',

    /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    |
    | Here you can activate the favicon.
    |
    | For detailed instructions you can look the favicon section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_ico_only' => false,
    'use_full_favicon' => false,

    /*
    |--------------------------------------------------------------------------
    | Google Fonts
    |--------------------------------------------------------------------------
    |
    | Here you can allow or not the use of external google fonts. Disabling the
    | google fonts may be useful if your admin panel internet access is
    | restricted somehow.
    |
    | For detailed instructions you can look the google fonts section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'google_fonts' => [
        'allowed' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Logo
    |--------------------------------------------------------------------------
    |
    | Here you can change the logo of your admin panel.
    |
    | For detailed instructions you can look the logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'logo' => '<b>Admin</b>LTE',
    'logo_img' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
    'logo_img_class' => 'brand-image img-circle elevation-3',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'Admin Logo',

    /*
    |--------------------------------------------------------------------------
    | Authentication Logo
    |--------------------------------------------------------------------------
    |
    | Here you can setup an alternative logo to use on your login and register
    | screens. When disabled, the admin panel logo will be used instead.
    |
    | For detailed instructions you can look the auth logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'auth_logo' => [
        'enabled' => true,
        'img' => [
            'path' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
            'alt' => 'Logo',
            'class' => '',
            'width' => 50,
            'height' => 50,
        ],
    ],


    /*
    |--------------------------------------------------------------------------
    | Preloader Animation
    |--------------------------------------------------------------------------
    |
    | Here you can change the preloader animation configuration. Currently, two
    | modes are supported: 'fullscreen' for a fullscreen preloader animation
    | and 'cwrapper' to attach the preloader animation into the content-wrapper
    | element and avoid overlapping it with the sidebars and the top navbar.
    |
    | For detailed instructions you can look the preloader section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'preloader' => [
        'enabled' => true,
        'mode' => 'fullscreen',
        'img' => [
            'path' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
            'alt' => 'AdminLTE Preloader Image',
            'effect' => 'animation__shake',
            'width' => 60,
            'height' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Menu
    |--------------------------------------------------------------------------
    |
    | Here you can activate and change the user menu.
    |
    | For detailed instructions you can look the user menu section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'usermenu_enabled' => true,
    'usermenu_header' => false,
    'usermenu_header_class' => 'bg-primary',
    'usermenu_image' => false,
    'usermenu_desc' => false,
    'usermenu_profile_url' => false,

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | Here we change the layout of your admin panel.
    |
    | For detailed instructions you can look the layout section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => null,
    'layout_fixed_navbar' => null,
    'layout_fixed_footer' => null,
    'layout_dark_mode' => null,

    /*
    |--------------------------------------------------------------------------
    | Authentication Views Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the authentication views.
    |
    | For detailed instructions you can look the auth classes section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_auth_card' => 'card-outline card-primary',
    'classes_auth_header' => '',
    'classes_auth_body' => '',
    'classes_auth_footer' => '',
    'classes_auth_icon' => '',
    'classes_auth_btn' => 'btn-flat btn-primary',

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the admin panel.
    |
    | For detailed instructions you can look the admin panel classes here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_body' => '',
    'classes_brand' => '',
    'classes_brand_text' => '',
    'classes_content_wrapper' => '',
    'classes_content_header' => '',
    'classes_content' => '',
    'classes_sidebar' => 'sidebar-dark-primary elevation-4',
    'classes_sidebar_nav' => '',
    'classes_topnav' => 'navbar-white navbar-light',
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container',

    /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar of the admin panel.
    |
    | For detailed instructions you can look the sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'sidebar_mini' => 'lg',
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => false,
    'sidebar_collapse_remember' => false,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'l',
    'sidebar_nav_accordion' => true,
    'sidebar_nav_animation_speed' => 300,

    /*
    |--------------------------------------------------------------------------
    | Control Sidebar (Right Sidebar)
    |--------------------------------------------------------------------------
    |
    | Here we can modify the right sidebar aka control sidebar of the admin panel.
    |
    | For detailed instructions you can look the right sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'right_sidebar' => false,
    'right_sidebar_icon' => 'fas fa-cogs',
    'right_sidebar_theme' => 'dark',
    'right_sidebar_slide' => true,
    'right_sidebar_push' => true,
    'right_sidebar_scrollbar_theme' => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide' => 'l',

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    |
    | Here we can modify the url settings of the admin panel.
    |
    | For detailed instructions you can look the urls section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_route_url' => false,
    'dashboard_url' => 'home',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'register_url' => 'register',
    'password_reset_url' => 'password/reset',
    'password_email_url' => 'password/email',
    'profile_url' => false,
    'disable_darkmode_routes' => false,

    /*
    |--------------------------------------------------------------------------
    | Laravel Asset Bundling
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Laravel Asset Bundling option for the admin panel.
    | Currently, the next modes are supported: 'mix', 'vite' and 'vite_js_only'.
    | When using 'vite_js_only', it's expected that your CSS is imported using
    | JavaScript. Typically, in your application's 'resources/js/app.js' file.
    | If you are not using any of these, leave it as 'false'.
    |
    | For detailed instructions you can look the asset bundling section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'laravel_asset_bundling' => false,
    'laravel_css_path' => 'css/app.css',
    'laravel_js_path' => 'js/app.js',

    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar/top navigation of the admin panel.
    |
    | For detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'menu' => [
        // Navbar items:
        [
            'text' => 'Configuración',
            'url' => 'admin/configuracion/edit',
            'icon' => 'fas fa-fw fa-cogs',
            'classes' => 'bg-blue text-white',
        ],
        [
            'text' => 'Gestiones',
            'url'  => 'admin/gestiones',
            'icon' => 'fas fa-fw fa-tasks',
            'classes' => 'bg-blue text-white',
        ],
        [
            'text' => 'Niveles',
            'url'  => 'admin/niveles',
            'icon' => 'fas fa-fw fa-layer-group',
            'classes' => 'bg-blue text-white',
        ],
        [
            'text' => 'Personas',
            'url'  => 'admin/personas',
            'icon' => 'fas fa-fw fa-users',
            'classes' => 'bg-blue text-white',
        ],
        [
            'text' => 'Turnos',
            'url'  => 'admin/turnos',
            'icon' => 'fas fa-fw fa-clock', // Icono de reloj para turnos
            'classes' => 'bg-blue text-white',
        ],
        [
            'text' => 'Paralelos',
            'url'  => 'admin/paralelos',
            'icon' => 'fas fa-fw fa-users', // Icono de grupo para paralelos
            'classes' => 'bg-blue text-white',
        ],
        [
            'text' => 'Periodos',
            'url'  => 'admin/periodos',
            'icon' => 'fas fa-fw fa-calendar-alt', // Icono de calendario para representar periodos
            'classes' => 'bg-green text-white',    // Puedes cambiar el color para diferenciarlo de Paralelos
        ],
        [
            'text' => 'Materias',
            'url'  => 'admin/materias',
            'icon' => 'fas fa-fw fa-book',
            'classes' => 'bg-info text-white', // 'bg-info' combina bien con el azul de paralelos
        ],
        [
            'text' => 'Grados',
            'url'  => 'admin/grados',
            'icon' => 'fas fa-fw fa-graduation-cap',
            'classes' => 'bg-primary text-white',
        ],
        [
            'text' => 'Carreras',
            'url'  => 'admin/carreras',
            'icon' => 'fas fa-fw fa-graduation-cap', // O también 'fas fa-fw fa-university'
            'classes' => 'bg-navy text-white',
        ],
        [
            'text' => 'Pensum',
            'url'  => 'admin/pensums', // O la ruta que estés usando para tu gestor drag & drop
            'icon' => 'fas fa-fw fa-clipboard-list',
            'classes' => 'bg-blue text-white',
        ],
        [
            'text'    => 'Roles',
            'url'     => 'admin/roles',
            'icon'    => 'fas fa-fw fa-user-check',
            'classes' => 'bg-blue text-white',
        ],
        [
            'text'    => 'Personal',
            'icon'    => 'fas fa-fw fa-users',
            'url'     => 'admin/personal',
        ],


        [
            'text' => 'multilevel',
            'icon' => 'fas fa-fw fa-share',
            'submenu' => [
                [
                    'text' => 'level_one',
                    'url' => '#',
                ],
                [
                    'text' => 'level_one',
                    'url' => '#',
                    'submenu' => [
                        [
                            'text' => 'level_two',
                            'url' => '#',
                        ],
                        [
                            'text' => 'level_two',
                            'url' => '#',
                            'submenu' => [
                                [
                                    'text' => 'level_three',
                                    'url' => '#',
                                ],
                                [
                                    'text' => 'level_three',
                                    'url' => '#',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'text' => 'level_one',
                    'url' => '#',
                ],
            ],
        ],
        ['header' => 'labels'],
        [
            'text' => 'important',
            'icon_color' => 'red',
            'url' => '#',
        ],
        [
            'text' => 'warning',
            'icon_color' => 'yellow',
            'url' => '#',
        ],
        [
            'text' => 'information',
            'icon_color' => 'cyan',
            'url' => '#',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Menu Filters
    |--------------------------------------------------------------------------
    |
    | Here we can modify the menu filters of the admin panel.
    |
    | For detailed instructions you can look the menu filters section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'filters' => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins Initialization
    |--------------------------------------------------------------------------
    |
    | Here we can modify the plugins used inside the admin panel.
    |
    | For detailed instructions you can look the plugins section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Plugins-Configuration
    |
    */

    'plugins' => [
        'Datatables' => [
            'active' => true,
            'files' => [
                // Core
                ['type' => 'js', 'asset' => false, 'location' => 'https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js'],
                ['type' => 'js', 'asset' => false, 'location' => 'https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js'],
                ['type' => 'css', 'asset' => false, 'location' => 'https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css'],
                // Buttons
                ['type' => 'js', 'asset' => false, 'location' => 'https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js'],
                ['type' => 'js', 'asset' => false, 'location' => 'https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js'],
                ['type' => 'js', 'asset' => false, 'location' => 'https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js'],
                ['type' => 'js', 'asset' => false, 'location' => 'https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js'],
                ['type' => 'js', 'asset' => false, 'location' => 'https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js'],
                ['type' => 'css', 'asset' => false, 'location' => 'https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css'],
                // Export Libs
                ['type' => 'js', 'asset' => false, 'location' => 'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js'],
                ['type' => 'js', 'asset' => false, 'location' => 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js'],
                ['type' => 'js', 'asset' => false, 'location' => 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js'],
            ],
        ],
        'Select2' => [
            'active' => true,
            'files' => [
                ['type' => 'js', 'asset' => false, 'location' => 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js'],
                ['type' => 'css', 'asset' => false, 'location' => 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css'],
            ],
        ],
        'Sweetalert2' => [
            'active' => true,
            'files' => [
                ['type' => 'css', 'asset' => false, 'location' => 'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css'],
                ['type' => 'js', 'asset' => false, 'location' => 'https://cdn.jsdelivr.net/npm/sweetalert2@11'],
            ],
        ],
        'Inputmask' => [
            'active' => true,
            'files' => [
                ['type' => 'js', 'asset' => false, 'location' => 'https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.8/jquery.inputmask.min.js'],
            ],
        ],
        'AntiBack' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => 'data:text/javascript;base64,' . base64_encode('
                        // 1. Interceptar el botón Back a nivel de historial nativo
                        history.pushState(null, null, location.href);
                        window.addEventListener("popstate", function () {
                            window.location.href = "/login";
                        });

                        // 2. Verificar en tiempo real con el servidor si la sesión sigue activa al volver a enfocar la pestaña o usar bfcache
                        window.addEventListener("pageshow", function(e) {
                            if (e.persisted || (performance.navigation && performance.navigation.type === 2)) {
                                window.location.href = "/login";
                            }
                        });

                        // 3. Verificación activa silenciosa contra el servidor cada vez que la página recupera el foco
                        window.addEventListener("focus", function() {
                            fetch("/admin/configuracion/edit", { method: "HEAD", redirect: "manual" })
                                .then(response => {
                                    if (response.status === 401 || response.status === 302 || response.type === "opaqueredirect") {
                                        window.location.href = "/login";
                                    }
                                }).catch(() => {
                                    // Si hay error de red o sesión muerta, forzar login
                                });
                        });
                    ')
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | IFrame
    |--------------------------------------------------------------------------
    |
    | Here we change the IFrame mode configuration. Note these changes will
    | only apply to the view that extends and enable the IFrame mode.
    |
    | For detailed instructions you can look the iframe mode section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/IFrame-Mode-Configuration
    |
    */

    'iframe' => [
        'default_tab' => [
            'url' => null,
            'title' => null,
        ],
        'buttons' => [
            'close' => true,
            'close_all' => true,
            'close_all_other' => true,
            'scroll_left' => true,
            'scroll_right' => true,
            'fullscreen' => true,
        ],
        'options' => [
            'loading_screen' => 1000,
            'auto_show_new_tab' => true,
            'use_navbar_items' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Livewire support.
    |
    | For detailed instructions you can look the livewire here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'livewire' => false,
];

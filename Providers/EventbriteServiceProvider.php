<?php

namespace Modules\Eventbrite\Providers;

use App\Conversation;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\ServiceProvider;
use Modules\Eventbrite\Api\Eventbrite;
use Modules\Eventbrite\Api\EventbriteClient;
use Modules\Eventbrite\Entities\EventbriteSetting;
use View;

//Module alias
define('EVENTBRITE_MODULE', 'eventbrite');

class EventbriteServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
    * Register the service provider.
    *
    * @return void
    */
    public function register()
    {
        //
    }

    /**
     * Boot the application events.
     *
     * @return void
     */

    public function boot()
    {
        $this->registerConfig();
        $this->registerAssets();
        $this->registerViews();
        $this->loadRoutesFrom(__DIR__.'/../Http/routes.php');
        $this->hooks();
        $this->registerTranslations();
        $this->registerMigration();
    }

    protected function registerMigration()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        $this->publishes([
            __DIR__ . '/../Database/Migrations/2023_02_07_091128_create_eventbrite_settings_table.php' => database_path('migrations/'. date('Y_m_d_His', time()).'_create_eventbrite_settings_table.php'),
        ], 'eventbrite-migration');
    }
    /**
    * Register config.
    *
    * @return void
    */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/eventbrite.php' => config_path('eventbrite.php'),
        ], 'eventbrite-config');

        $this->mergeConfigFrom(
            __DIR__.'/../Config/eventbrite.php',
            'eventbrite'
        );
    }

    /**
     * Module hooks.
     */
    public function hooks()
    {
        \Eventy::addFilter('stylesheets', function ($styles) {
            $styles[] = \Module::getPublicPath(EVENTBRITE_MODULE).'/css/eventbrite.css';
            return $styles;
        });

        \Eventy::addFilter('javascripts', function ($scripts) {
          $scripts[] = \Module::getPublicPath(EVENTBRITE_MODULE).'/js/eventbrite.js';
          return $scripts;
        });

        \Eventy::addAction('customer.profile.extra', [$this, 'customerProfileExtra'], 20, 2);
        \Eventy::addAction('mailboxes.settings.menu', [$this, 'mailboxSettingsMenu']);
    }

    /**
     * Show data in customer Profile Extra section
     * @param mixed $customer
     * @return void
     */
    public function customerProfileExtra($customer, $conversation)
    {
        if ($conversation == '') {
          return '';
        }

        $eventbriteConfig = $this->getEventbriteSecretKey($conversation->mailbox->id);

        if(!empty($eventbriteConfig)) {
            list($eventbriteSecret, $eventbriteOrgId) = $eventbriteConfig;
            $eventbrite = new Eventbrite($eventbriteSecret, $eventbriteOrgId);
            $orders = $eventbrite->getOrders($customer->getMainEmail());
            $tickets = $eventbrite->getTickets($customer->getMainEmail());
            $urls = $eventbrite->getUrls($customer->getMainEmail());

            echo View::make('eventbrite::customer_fields_view', [
                'orders' => $orders,
                'tickets' => $tickets,
                'urls' => $urls,
                'customer' => $customer,
                'mailbox' => $conversation->mailbox
            ])->render();
        }
    }

    public function getEventbriteSecretKey($mailboxId)
    {
        $eventbriteSettings = EventbriteSetting::select('eventbrite_secret_key', 'eventbrite_org_id')->where('mailbox_id', $mailboxId)->first();

        if (isset($eventbriteSettings)) {
            return [Crypt::decryptString($eventbriteSettings->eventbrite_secret_key), $eventbriteSettings->eventbrite_org_id];
        } else {
            return [];
        }
    }



    /**
    * Show data in customer Profile Extra section
    * @param mixed $customer
    * @return void
    */
    public function mailboxSettingsMenu($mailbox)
    {
        echo \View::make('eventbrite::mailbox_settings_menu', [
            'mailbox' => $mailbox,
        ])->render();
    }

    /**
     * Register views.
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/eventbrite');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ], 'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/eventbrite';
        }, \Config::get('view.paths')), [$sourcePath]), 'eventbrite');
    }


    /**
     * Register views.
     * @return void
     */
    public function registerAssets()
    {
        $this->publishes([
            __DIR__.'/../Public/css' => public_path('modules/eventbrite/css'),
        ], 'public');
    }


    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        // $langPath = resource_path('lang/modules/eventbrite');

        // if (is_dir($langPath)) {
        //     $this->loadTranslationsFrom($langPath, 'eventbrite');
        // } else {
        //     $this->loadTranslationsFrom(__DIR__ .'/../Resources/lang', 'eventbrite');
        // }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}

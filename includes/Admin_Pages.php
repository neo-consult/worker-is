<?php
namespace WorkerIS;

class Admin_Pages {

    /**
     * Konstruktor: Registriert den Hook zur Erstellung der Admin-Menüs.
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_plugin_pages'));
        Logger::log('Admin_Pages initialized.');
    }

    /**
     * Fügt die Haupt- und Submenüseiten im Backend hinzu.
     *
     * Admin_Pages registriert hier alle Menüpunkte und leitet das Routing an die jeweiligen Sub-Seiten.
     */
    public function add_plugin_pages() {
        // Hauptmenüpunkt: Dashboard
        add_menu_page(
            __('Worker IS Dashboard', 'worker-is'),
            __('Worker IS', 'worker-is'),
            'manage_options',
            'worker-is-dashboard',
            array($this, 'render_dashboard'),
            'dashicons-admin-users',
            6
        );

        // Submenü: Worker Profiles
        add_submenu_page(
            'worker-is-dashboard',
            __('Worker Profiles', 'worker-is'),
            __('Worker Profiles', 'worker-is'),
            'manage_options',
            'worker-is-profiles',
            array($this, 'render_sub_page')
        );

        // Submenü: Employer Requests
        add_submenu_page(
            'worker-is-dashboard',
            __('Employer Requests', 'worker-is'),
            __('Employer Requests', 'worker-is'),
            'manage_options',
            'worker-is-employers',
            array($this, 'render_sub_page')
        );

        // Submenü: Reports
        add_submenu_page(
            'worker-is-dashboard',
            __('Reports', 'worker-is'),
            __('Reports', 'worker-is'),
            'manage_options',
            'worker-is-reports',
            array($this, 'render_sub_page')
        );

        // Submenü: Form Builder
        add_submenu_page(
            'worker-is-dashboard',
            __('Form Builder', 'worker-is'),
            __('Form Builder', 'worker-is'),
            'manage_options',
            'worker-is-form-builder',
            array($this, 'render_sub_page')
        );

        // Submenü: Settings
        add_submenu_page(
            'worker-is-dashboard',
            __('Settings', 'worker-is'),
            __('Settings', 'worker-is'),
            'manage_options',
            'worker-is-settings',
            array($this, 'render_sub_page')
        );

        Logger::log('Admin pages added.');
    }

    /**
     * Rendert das Dashboard.
     */
    public function render_dashboard() {
        ?>
        <div class="wrap">
            <h1><?php _e('Worker IS Dashboard', 'worker-is'); ?></h1>
            <p><?php _e('Welcome to the Worker IS Dashboard.', 'worker-is'); ?></p>
        </div>
        <?php
        Logger::log('Dashboard rendered.');
    }

    /**
     * Zentrales Routing für Admin-Sub-Seiten.
     *
     * Basierend auf dem "page"-Parameter in der URL wird die entsprechende Sub-Seiten-Klasse instanziert
     * und deren render()-Methode aufgerufen.
     */
    public function render_sub_page() {
        $page = isset($_GET['page']) ? $_GET['page'] : '';
        switch ($page) {
            case 'worker-is-profiles':
                (new \WorkerIS\SubPages\Worker_Profiles())->render();
                break;
            case 'worker-is-employers':
                (new \WorkerIS\SubPages\Employer_Requests())->render();
                break;
            case 'worker-is-reports':
                (new \WorkerIS\SubPages\Reports())->render();
                break;
            case 'worker-is-form-builder':
                (new \WorkerIS\SubPages\Form_Builder())->render();
                break;
            case 'worker-is-settings':
                (new \WorkerIS\SubPages\Settings())->render();
                break;
            default:
                echo '<div class="wrap"><h1>' . __('Page not found.', 'worker-is') . '</h1></div>';
                Logger::log('Unknown admin subpage requested.', array('page' => $page));
                break;
        }
    }
}

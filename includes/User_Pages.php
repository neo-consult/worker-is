<?php
namespace WorkerIS;

class User_Pages {

    /**
     * Konstruktor: Registriert den Hook zur Erstellung der Vermittler-Seiten.
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_user_pages'));
        Logger::log('User_Pages initialized.');
    }

    /**
     * Fügt die Menüpunkte für Vermittler im Backend hinzu.
     * Hier verwenden wir die Capability "edit_posts", sodass auch Worker Manager diese Menüs sehen.
     */
    public function add_user_pages() {
        // Hauptmenüpunkt für Vermittler – z. B. "Vermittler Dashboard"
        add_menu_page(
            __('Vermittler Dashboard', 'worker-is'),
            __('Vermittler', 'worker-is'),
            'edit_posts', // Worker Manager benötigen "edit_posts"
            'worker-is-user-dashboard',
            array($this, 'render_user_dashboard'),
            'dashicons-businessman',
            6
        );

        // Sub-Seite: Zuweisungen (Assigned Profiles)
        add_submenu_page(
            'worker-is-user-dashboard',
            __('Assigned Profiles', 'worker-is'),
            __('Assigned Profiles', 'worker-is'),
            'edit_posts',
            'worker-is-assigned-profiles',
            array($this, 'render_user_sub_page')
        );

        // Sub-Seite: Meine Anfragen (My Requests)
        add_submenu_page(
            'worker-is-user-dashboard',
            __('My Requests', 'worker-is'),
            __('My Requests', 'worker-is'),
            'edit_posts',
            'worker-is-user-requests',
            array($this, 'render_user_sub_page')
        );

        // Sub-Seite: Create Worker Profile
        add_submenu_page(
            'worker-is-user-dashboard',
            __('Create Worker Profile', 'worker-is'),
            __('Create Profile', 'worker-is'),
            'edit_posts',
            'worker-is-create-profile',
            array($this, 'render_user_sub_page')
        );
        
        // Neuer Menüpunkt: Edit Worker Profile
        add_submenu_page(
            'worker-is-user-dashboard',
            __('Edit Worker Profile', 'worker-is'),
            __('Edit Profile', 'worker-is'),
            'edit_posts',
            'worker-is-edit-profile',
            array($this, 'render_user_sub_page')
        );

        // Sub-Seite: Vermittler-Einstellungen (User Settings)
        add_submenu_page(
            'worker-is-user-dashboard',
            __('User Settings', 'worker-is'),
            __('User Settings', 'worker-is'),
            'edit_posts',
            'worker-is-user-settings',
            array($this, 'render_user_sub_page')
        );

        Logger::log('User pages added.');
    }

    /**
     * Rendert das Vermittler-Dashboard.
     */
    public function render_user_dashboard() {
        ?>
        <div class="wrap">
            <h1><?php _e('Vermittler Dashboard', 'worker-is'); ?></h1>
            <p><?php _e('Willkommen im Vermittler Dashboard.', 'worker-is'); ?></p>
        </div>
        <?php
        Logger::log('User Dashboard rendered.');
    }

    /**
     * Zentrales Routing für Vermittler-Sub-Seiten.
     *
     * Basierend auf dem "page"-Parameter in der URL wird die entsprechende Sub-Seiten-Klasse instanziert
     * und deren render()-Methode aufgerufen.
     */
    public function render_user_sub_page() {
        $page = isset($_GET['page']) ? $_GET['page'] : '';
        switch ($page) {
            case 'worker-is-assigned-profiles':
                (new \WorkerIS\SubPages\User_Assigned_Profiles())->render();
                break;
            case 'worker-is-user-requests':
                (new \WorkerIS\SubPages\User_Requests())->render();
                break;
            case 'worker-is-create-profile':
                (new \WorkerIS\SubPages\Worker_Profile_Create())->render();
                break;
            case 'worker-is-edit-profile':
                (new \WorkerIS\SubPages\Worker_Profile_Edit())->render();
                break;
            case 'worker-is-user-settings':
                (new \WorkerIS\SubPages\User_Settings())->render();
                break;
            default:
                echo '<div class="wrap"><h1>' . __('Page not found.', 'worker-is') . '</h1></div>';
                Logger::log('Unknown user subpage requested.', array('page' => $page));
                break;
        }
    }
}

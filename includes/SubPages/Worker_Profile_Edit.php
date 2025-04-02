<?php
namespace WorkerIS\SubPages;

use WorkerIS\SubPages\Worker_Profile_Form;

class Worker_Profile_Edit {
    public function render() {
        $profile_id = isset($_GET['profile_id']) ? intval($_GET['profile_id']) : 0;
        if ($profile_id > 0) {
            Worker_Profile_Form::render('edit', $profile_id);
        } else {
            echo '<div class="alert alert-danger">Kein Profil ausgewählt.</div>';
        }
    }
}

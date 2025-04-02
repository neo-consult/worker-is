<?php
namespace WorkerIS\SubPages;

use WorkerIS\SubPages\Worker_Profile_Form;

class Worker_Profile_Create {
    public function render() {
        Worker_Profile_Form::render('create');
    }
}

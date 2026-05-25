import './bootstrap';
import $ from 'jquery';
window.$ = window.jQuery = $;

import 'bootstrap';
import DataTable from 'datatables.net-bs5';
window.DataTable = DataTable;

import Swal from 'sweetalert2';
window.Swal = Swal;

import alertService from './services/alertService';
import notificationService from './services/notificationService';

window.alertService = alertService;
window.notificationService = notificationService;
window.CataskyAlerts = alertService;
window.CataskyNotifications = notificationService;

window.alert = (message) => alertService.infoAlert('CATASKY', message);
window.confirm = (message) => {
    alertService.warningAlert('Confirmation required', message);
    return false;
};
window.prompt = () => {
    alertService.warningAlert('Unsupported action', 'Text prompts must use a CATASKY modal form.');
    return null;
};

// Global UI Enhancements
$(document).ready(function() {
    notificationService.init({ pollInterval: 60000 });

    // Smooth scroll
    $('a[href^="#"]').on('click', function(event) {
        var target = $(this.getAttribute('href'));
        if( target.length ) {
            event.preventDefault();
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 100
            }, 800);
        }
    });

    // Tooltip initialization
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
});

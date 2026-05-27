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
// Utility to resolve relative image URLs to absolute URLs using the base URL injected from Blade
window.getAbsoluteImageUrl = function(src) {
    if (!src) return src;
    // If already absolute (http:// or https:// or //) return as‑is
    if (/^(?:[a-z]+:)?\/\//i.test(src)) return src;
    // Remove leading slash to avoid double slash when concatenating
    const clean = src.replace(/^\//, '');
    return `${window.baseUrl}/${clean}`;
};

$(document).ready(function() {
    notificationService.init({ pollInterval: 60000 });

    // Smooth scroll
    $('a[href^="#"]').on('click', function(event) {
        var target = $(this.getAttribute('href'));
        if( target.length ) {
            event.preventDefault();
            // Build the template with absolute image URL
            // Duplicate template generation removed; handled later for tooltip initialization
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 100
            }, 800);
        }
    });

    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
});

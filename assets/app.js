/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import 'jquery';
import '../public/semantic-ui/dist/semantic.min.css'
import '../public/semantic-ui/dist/semantic.min.js'
import './styles/app.css'

$(document).ready(function() {
    $('.ui.dropdown').dropdown();
    $('.sidebar-menu-toggler').on('click', function() {
        var target = $(this).data('target');
        $(target)
            .sidebar({
                dinPage: true,
                transition: 'overlay',
                mobileTransition: 'overlay'
            })
            .sidebar('toggle');
    });
});

$('#finalize').click(function(){
    $('#finalize').addClass('loading');
});

// start the Stimulus application
import './bootstrap';

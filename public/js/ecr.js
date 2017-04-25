$(document).ready(function() {
    $('#btnSearch').click(function() {
        if ($('#searchbox').val().length > 4) {
            bootbox.alert("Invalid Airport Identifier in search box.  One FAA Identifier or ICAO Identifier only.");
            return false;
        }
        waitingDialog.show("Processing Request...");
        $.ajax({
            method: "GET",
            url: "https://api.aircharts.org/Airport/" + $('#searchbox').val(),
            dataType: 'json',
            success: function(data) {
                var oldtype = null;
                html = "<h2>" + data.info.icao + "/" + data.info.iata + " - " + data.info.name + "</h2>";
                $.each(data.charts, function() {
                    if (oldtype != this.type) {
                        html = html + "<h4>" + this.type + "</h4>";
                    }
                    html = html + "<button class=\"btn btn-primary btnchart text-center\" data-href=\"" + this.url + "\"'>" + this.name + "</button>";
                });
                $('#chartbox').html(html);
                waitingDialog.hide();
            },
            error: function() {
                waitingDialog.hide();
                $('#chartbox').html('<div class="text-error">Error processing your request</div>');
            }
        });
        return false;
    });
});

var waitingDialog = waitingDialog || (function ($) {
        'use strict';

        // Creating modal dialog's DOM
        var $dialog = $(
            '<div class="modal fade" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-hidden="true" style="padding-top:15%; overflow-y:visible;">' +
            '<div class="modal-dialog modal-m">' +
            '<div class="modal-content">' +
            '<div class="modal-header"><h3 style="margin:0;"></h3></div>' +
            '<div class="modal-body">' +
            '<div class="progress progress-striped active" style="margin-bottom:0;"><div class="progress-bar" style="width: 100%"></div></div>' +
            '</div>' +
            '</div></div></div>');

        return {
            /**
             * Opens our dialog
             * @param message Custom message
             * @param options Custom options:
             *                  options.dialogSize - bootstrap postfix for dialog size, e.g. "sm", "m";
             *                  options.progressType - bootstrap postfix for progress bar type, e.g. "success", "warning".
             */
            show: function (message, options) {
                // Assigning defaults
                if (typeof options === 'undefined') {
                    options = {};
                }
                if (typeof message === 'undefined') {
                    message = 'Loading';
                }
                var settings = $.extend({
                    dialogSize: 'm',
                    progressType: 'ogblue',
                    onHide: null // This callback runs after the dialog was hidden
                }, options);

                // Configuring dialog
                $dialog.find('.modal-dialog').attr('class', 'modal-dialog').addClass('modal-' + settings.dialogSize);
                $dialog.find('.progress-bar').attr('class', 'progress-bar');
                if (settings.progressType) {
                    $dialog.find('.progress-bar').addClass('progress-bar-' + settings.progressType);
                }
                $dialog.find('h3').text(message);
                // Adding callbacks
                if (typeof settings.onHide === 'function') {
                    $dialog.off('hidden.bs.modal').on('hidden.bs.modal', function (e) {
                        settings.onHide.call($dialog);
                    });
                }
                // Opening dialog
                $dialog.modal();
            },
            /**
             * Closes dialog
             */
            hide: function () {
                $dialog.modal('hide');
            }
        };

    })(jQuery);
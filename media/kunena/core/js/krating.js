/**
 * Kunena       Component
 * @package     Kunena.Template.Crypsis
 *
 * @copyright     Copyright (C) 2008 - 2022 Kunena Team. All rights reserved.
 * @license     https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link        https://www.kunena.org
 **/

 jQuery(document).ready(function ($) {
    // Krating element
    const krating = document.querySelector('#krating');

    // Initialize
    (function init() {
        const topic_id = $("#topic_id").val();
        const userid = $("#userid").val();
        // alert("User id " + userid)

        if ($('#krating').length > 0) {
            $.ajax({
                    dataType: "json",
                    url: $('#krating_url').val(),
                    data: 'topic_id=' + topic_id
                }
            ).done(function (response) {
                    showRecentRating(buildItem(), response.data, topic_id)
                    // addRatingWidget(buildItem(), response, topic_id);
                }
            ).fail(function (reponse) {
                    //TODO: handle the error of ajax request
                }
            );
        }
    })();

    function showRecentRating(ratingItem, rate, topicid) {
        // debugger;
        // const ratingElement = ratingItem.querySelector('.c-rating');

        const callback = function (rating) {
            $.ajax({
                    dataType: "json",
                    url: $('#krating_submit_url').val(),
                    data: 'starid=' + rating + '&topic_id=' + topicid
                }
            ).done(function (response) {
                    debugger;
                    if (response.success) {
                        // $('<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">&times;</button><h4>' + Joomla.Text._('COM_KUNENA_RATING_SUCCESS_LABEL') + '</h4>' + Joomla.Text._(response.message) + '</div>').appendTo('#system-message-container');
                        $('<joomla-alert type="success" close-text="Close" dismiss="true" role="alert" style="animation-name: joomla-alert-fade-in;"><button type="button" class="joomla-alert--close" aria-label="Close"><span aria-hidden="true">×</span></button><div class="alert-heading"><span class="success"></span><span class="visually-hidden">info</span></div><div class="alert-wrapper"><div class="alert-message">' + Joomla.Text._(response.message) + '.</div></div></joomla-alert>').appendTo('#system-message-container');

                    } else {
                        // $('<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">&times;</button><h4>' + Joomla.Text._('COM_KUNENA_RATING_WARNING_LABEL') + '</h4>' + Joomla.Text._(response.message) + '</div>').appendTo('#system-message-container');

                        $('<joomla-alert type="danger" close-text="Close" dismiss="true" role="alert" style="animation-name: joomla-alert-fade-in;"><button type="button" class="joomla-alert--close" aria-label="Close"><span aria-hidden="true">×</span></button><div class="alert-heading"><span class="danger"></span><span class="visually-hidden">info</span></div><div class="alert-wrapper"><div class="alert-message">' + Joomla.Text._(response.message) + '.</div></div></joomla-alert>').appendTo('#system-message-container');
                }
            }
            ).fail(function (reponse) {
                    $('<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">&times;</button><h4>' + Joomla.Text._('COM_KUNENA_RATING_WARNING_LABEL') + '</h4>' + reponse + '</div>').appendTo('#system-message-container');
                }
            );
        };

        const r = rating(ratingItem, rate, 5, callback);
    }

    // Build krating item
    function buildItem() {
        const ratingItem = document.createElement('div');
        ratingItem.classList.add('star-wrapper');
        krating.appendChild(ratingItem);
        return ratingItem;
    }
}
);
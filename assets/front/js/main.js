$('#per_page').change(function (event) {
    window.location = event.target.value;
});

$('form#delete input').last().bind('click', function () {
    return confirm('Are you sure, you want to proceed the action?');
});

$('div[id^="participant-content-"]').on('submit', 'form', function (event) {
    event.preventDefault();
    var url = '';
    var match = $(this);
    if (match.is('form[id^="invite-"]') || match.is('form[id^="retry-"]')) {
        url = '/participants/invite';
    } else if (match.is('form[id^="kick-"]')) {
        url = '/participants/kick';
    }
    $.ajax({
        type: 'POST',
        url: url,
        data: match.serialize(),
        success: function (response) {
            refreshParticipants(match, response);
        }
    });
});

function relatedId(form, identifier) {
    return form.serializeArray().filter(function (field) {
        return field.name === identifier;
    })[0].value;
}

function refreshParticipants(match, response) {
    var target = '#participant-content-' + relatedId(match, 'subscription') + ' div.modal-body';
    $(target).replaceWith($(response).find(target));
}

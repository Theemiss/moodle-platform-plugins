define(['jquery', 'core/ajax', 'core/notification', 'core/templates'],
    function($, ajax, notification, templates) {
        return {
            init: function(courseid) {
                $('#oneclick-export-btn').on('click', function(e) {
                    e.preventDefault();
                    
                    var btn = $(this);
                    btn.prop('disabled', true);
                    templates.render('local_oneclickexport/loading', {}).then(function(html) {
                        btn.after(html);
                        
                        ajax.call([{
                            methodname: 'local_oneclickexport_initiate_export',
                            args: {courseid: courseid},
                            done: function(response) {
                                if (response.fileurl) {
                                    window.location.href = response.fileurl;
                                }
                            },
                            fail: notification.exception,
                            always: function() {
                                btn.prop('disabled', false);
                                $('.oneclick-export-loading').remove();
                            }
                        }]);
                    }).catch(notification.exception);
                });
            }
        };
    });
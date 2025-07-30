define(['jquery', 'core/ajax'], function($, ajax) {
    return {
        init: function(apiUrl, orgId, apiKey) {
            $(document).ready(function() {
                // Fetch labs from the API
                $.ajax({
                    url: apiUrl,
                    method: 'GET',
                    headers: {
                        'Organization-ID': orgId,
                        'API-Key': apiKey
                    },
                    success: function(response) {
                        if (response.labs && response.labs.length > 0) {
                            const select = $('#id_tekouinlabid');
                            select.empty();
                            response.labs.forEach(function(lab) {
                                select.append(new Option(lab.name, lab.id));
                            });
                        }
                    },
                    error: function() {
                        console.error('Failed to fetch labs');
                    }
                });
            });
        }
    };
});
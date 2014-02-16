/**
 * Created with JetBrains PhpStorm.
 * User: Omar
 * Date: 13/12/13
 * Time: 2:18 AM
 * To change this template use File | Settings | File Templates.
 */
jQuery(document).ready(function($)
{
    $('#aerh-sync-now').click(function(e)
    {
        e.preventDefault();
        nonce = jQuery(this).attr("data-nonce");
        var data = {
            action: 'Aragon_eRH_Sync',
            nonce: nonce,
            ajax_nonce: aerhAjax.ajaxnonce
        };

        $.ajax({
            type : "post",
            dataType : "json",
            url : aerhAjax.ajaxurl,
            cache: false,
            data : data,
            success: function(response) {
                if(response.success == true) {
                    var synced_records = response.data.results.total_records;
                    create_jobs_table(response.data.results.jobs);
                    $("#sync-success").html(synced_records);
                }
                else {
                    alert(response.data.error);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                alert("Error: " + errorThrown + ". Is the URL correct?");
            }
        })

    });

    $('#aerh-bulk-url').click(function(e)
    {
        e.preventDefault();
        nonce = jQuery(this).attr("data-nonce");
        var data = {
            action: 'Aragon_eRH_Bulk_URL',
            nonce: nonce,
            ajax_nonce: aerhAjax.ajaxnonce
        };

        $.ajax({
            type : "post",
            dataType : "json",
            url : aerhAjax.ajaxurl,
            cache: false,
            data : data,
            success: function(response) {
                if(response.success == true) {
                    var bulk_url = response.data.url;
                    $('input#Aragon_eRH_setting_rss-bulk-url').val(bulk_url);
                }
                else {
                    alert(response.data.error);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                alert("Error: " + errorThrown + ". Is the URL correct?");
            }
        })

    });

    $(window).on('load', function() {
        if ($('.current-positions-list-wrapper[data-scroll="true"]').data('scroll')) {
            $('.current-positions-list-wrapper[data-scroll="true"]').scrollbox({linear: true,step: 1,delay: 0,speed: 100});
        }
    });

});

function get_table_data() {
    var data = {
        action: 'Aragon_eRH_Get_Table'
    };

    jQuery.post( aerhAjax.ajaxurl, data, function( response )
    {
        jQuery("#sync-success").html(response);
        create_jobs_table(response.data.results.jobs);
    });
}

function create_jobs_table(jobs) {
    var table = jQuery('#jobs-table');
    var rows = [];
    if ( jobs.length < 1 ) {
        return;
    }
    for ( var i = 0; i < jobs.length; i++ ) {
        var row = document.createElement('tr');

        for(var index in jobs[i]) {
            var td = document.createElement('td');

            if (index == 'url') {
                val = document.createElement('a');
                val.href = jobs[i][index];
                val.target = '_blank'
                val.innerHTML = 'Link';
                td.appendChild(val);
            } else {
                var val = jobs[i][index];
                td.innerHTML = val;
            }
            row.appendChild(td);
        }

        rows.push(row);
    }

    table.append(rows);
}


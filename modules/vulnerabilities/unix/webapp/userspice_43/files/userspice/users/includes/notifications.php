

<div id="notificationsModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title" id="notification_type">New Notifications</h4>
        </div>
        <div id="notificationsModalBody" class="col-lg-12" style="padding: 0px; padding-top: 15px"></div>
        <div class="modal-footer">
            <div class="btn-group btn-block"><button type="button" class="btn btn-block btn-default" data-dismiss="modal">Close</button></div>
        </div>
    </div>

  </div>
</div>

<script type="text/javascript">


    //$('#notificationsTrigger').on('click', function(){
	function displayNotifications(new_all) {

		if(new_all == 'new'){
			notif_to_get = {'new_all':'new'}
			$('#notification_type').html('New Notifications');
		} else {
			notif_to_get = {'new_all':'all'}
			$('#notification_type').html('All Notifications');
		}

        $.ajax({
            url: '<?=$us_url_root?>users/parsers/getNotifications.php',
            type: 'POST',
			data: notif_to_get,
			dataType: 'json',
            success: function(response) {
                $('#notificationsModalBody').html(response);
                //$('#notifCount').hide();
                displayNotifRows(1);
            },
            error: function() {
                $('#notificationsModalBody').html('<div class="text-center btn-lg btn-danger" style="margin: 15px">There was an error retrieving your notifications.</div>');
            }
        });
        $('#notificationsModal').on('shown.bs.modal', function(e){
            $('#notificationsTrigger').on('focus', function(e){$(this).blur();});
        });

	}
    //});

//$(document).ready(function(){
    $(document).on("click", "#notif-pagination li", function(event){
        var pageId = $(this).find('a').text();
        if (pageId == '>>') pageId = $('#notif-pagination li:nth-last-child(2) a').text();
        if (pageId == '<<') pageId = 1;
        displayNotifRows(pageId);
    });
    function displayNotifRows(pageId) {
        $('#notif-pagination li.active').removeClass('active');
        $('#notif-pagination li a').filter(function(index) { return $(this).text() == pageId; }).parent().addClass('active');
        var floor = (pageId - 1) * 5;
        var ceil = pageId * 5;
        $.each($('.notification-row'), function(){
            var id = $(this).data('id');
            console.log(id);
            if (id > floor && id <= ceil) $(this).show();
            else $(this).hide();
        });
        if (pageId == 1) $('#notif-pagination .first').addClass('disabled');
        else $('#notif-pagination .first').removeClass('disabled');
        if (pageId == $('#notif-pagination li').length-2) $('#notif-pagination .last').addClass('disabled');
        else $('#notif-pagination .last').removeClass('disabled');
    }


//});

//For dismissing notifications
function dismissNotif(id_array) {

	if(id_array.length > 1){
		var confirm_result = confirm('Are you sure you want to mark all notifications as read and dismiss them?');
		if(!confirm_result){
			die();
		}
	}

	data = {'id_array':id_array,'user_id':<?php echo $user->data()->id;?>}

	$.ajax({
		url: '<?=$us_url_root?>users/parsers/dismissNotifications.php',
		type: 'POST',
		data: data,
		dataType: 'json',
		success: function (response) { //The data has posted, now process the response.
			if(response.status === "success") { //There were no errors upon processing the data, so it was successful.
				id_array.forEach(function(id) {
					$('#notification_' + id).hide('fast');
				});

				if(response.num_new_notif == 0){
					$('#mark_all_notif').hide();
					$('#notif_pagination').hide();
					$('#notificationsModalBody').css('padding-top', '0px');
				}

				$('#notifCount').html(response.num_new_notif);

				displayNotifications('new');

			} else if(response.status === "error") { //There were errors upon processing the data, so tell the user about it.
				$('#notificationsModalBody').prepend('<div id="notificationDismissError" class="col-lg-12 panel-default">' + response.error_info + '</div>');
				$('#notificationDismissError').delay(3200).fadeOut(300);
			}
		},
		error: function() {
			$('#notificationsModalBody').prepend('<div id="notificationDismissError" class="text-center btn-lg btn-danger" style="margin: 15px">There was an error dismissing your notifications.</div>');
			$('#notificationDismissError').delay(3200).fadeOut(300);
		}
	});
}
</script>

jQuery(document).ready(function($) {
	/*var data = {
		'action': 'SaveNewChaeschtli',
		'whatever': ajax_object.we_value      // We pass php values differently!
	};*/
	// We can also pass the url value separately from ajaxurl for front end AJAX implementations
  $("#chae-dash-form").submit(function(event) {
    event.preventDefault();
    $.ajax({
       type: 'POST',
       url: ajax_object.ajax_url,
       data: $('#chae-dash-form').serialize(),   // I WANT TO ADD EXTRA DATA + SERIALIZE DATA
       success: function(response){
          //alert('Got this from the server: ' + response);
          $('#chae-dash-form').closest('.inside').html(response);
       }
    });
  });

  $(function() {
  $('.chae-input-group :input')
    .focus(function() {
      $(this).prev('label').addClass('hide');
    })
    .blur(function() {
      if(!$(this).val()){
        $(this).prev('label').removeClass('hide');
      }
    });
});

$('.clockpicker').clockpicker({
    placement: 'top',
    align: 'left',
    donetext: 'Übernehmen',
    afterDone: function() {
        $(this).prev('label').addClass('hide');
    }
});

$(function() {
  var nextDate = nextDay(new Date(), 6);
  $('[data-toggle="datepicker"]').datepicker({
    autoHide: true,
    zIndex: 2048,
    date: nextDate,
    format: 'dd.mm.yyyy'
  }).val(nextDate.getDate() + '.' + (nextDate.getMonth() + 1) + '.' + nextDate.getFullYear());

});

function nextDay(d, dow){
    d.setDate(d.getDate() + (dow+(7-d.getDay())) % 7);
    return d;
}
});

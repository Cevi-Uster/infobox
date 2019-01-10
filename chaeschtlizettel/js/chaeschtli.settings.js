if (typeof jQuery === 'undefined') {
  throw new Error('StufenTableFunction requires jQuery library.');
}

(function($) {
    //'use strict';

    $.fn.StufenTableFunction = function(nonce, restBaseUrl) {
      
      var loadStufenTable = function loadStufenTable(){
        $.ajax({
          url: restBaseUrl + '/wp-json/chaeschtlizettel/v1/stufen',
          beforeSend: function ( xhr ) {
            xhr.setRequestHeader( 'X-WP-Nonce', nonce );
          },
          success: function(data, response) {
            var html = '<table id="stufenTable" class="table table-striped table-bordered">';
            html += '<thead>';
            html += '<th>stufen_id</th><th>name</th><th>abteilung</th><th>jahrgang</th>';
            html += '</thead>';
            html += '<tbody>';
            //console.log(data);
            html += data.reduce(function(string, item) {
              return string + "<tr><td>" + item.stufen_id + "</td><td>" + item.name  + "</td><td>" + item.abteilung + "</td><td>" + item.jahrgang +  "</td></tr>"
            }, '');
            html += '</tbody>';
            html += '</table>';
            $('div#stufeTableContainer').html(html);
            makeTableEditable();
         },
         error: function(XMLHttpRequest, textStatus, errorThrown){
           $('div#errorMessageContainer').html('<p>Could not load Stufen form server. Got error: ' + errorThrown + '</p>');
           $('div#errorMessageContainer').attr('class', 'alert alert-warning');
           $('div#errorMessageContainer').attr('role', 'warning');
         }
       });
      }

      var makeTableEditable = function makeTableEditable(){
        $('#stufenTable').Tabledit({
          url: restBaseUrl + '/wp-json/chaeschtlizettel/v1/stufen',
          nonce: nonce,
          restoreButton: false,
          deleteCallbackFunction: function() {
              loadStufenTable();
          },
          columns: {
            identifier: [0, 'stufen_id'],
            editable: [[1, 'name'], [2, 'abteilung', '{"m": "Knaben", "f": "Mädchen"}'], [3, 'jahrgang']]
          }
        });
      } 

      var addNewStufe = function addNewStufe(){
        var formData = JSON.stringify($('#newStufeForm').serializeJSON());

        $.ajax( {
          url: restBaseUrl +'/wp-json/chaeschtlizettel/v1/stufen/insert/',
          method: 'POST',
          beforeSend: function ( xhr ) {
            xhr.setRequestHeader( 'X-WP-Nonce', nonce );
          },
          error: function(XMLHttpRequest, textStatus, errorThrown){
           $('div#errorMessageContainer').html('<p>Could not create new Stufe. Got error: ' + errorThrown + '</p>');
           $('div#errorMessageContainer').attr('class', 'alert alert-warning');
           $('div#errorMessageContainer').attr('role', 'warning');
          },
          data: formData
          } ).done( function ( response ) {
            console.log( response );
            $("#newStufeForm")[0].reset();
            loadStufenTable();
        } );
      }
      
      document.getElementById("newStufeFormSubmitButton").addEventListener("click", function(event){
          event.preventDefault();
          addNewStufe();
      });

      loadStufenTable();
      }

      
    return this;
    }
(jQuery));
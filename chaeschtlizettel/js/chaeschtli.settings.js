if (typeof jQuery === 'undefined') {
  throw new Error('StufenTableFunction requires jQuery library.');
}

(function($) {
    //'use strict';

    $.fn.StufenTableFunction = function(nonce, restBaseUrl) {

      var loadStufenTable = function loadStufenTable() {
        $.ajax({
          url: restBaseUrl + '/wp-json/chaeschtlizettel/v1/stufen',
          beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', nonce);
          },
          success: function(data, response) {
            var html = '<table id="stufenTable" class="table table-striped table-bordered">';
            html += '<thead>';
            html += '<th>stufen_id</th><th>name</th><th>abteilung</th><th>jahrgang</th><th>e-mail</th>';
            html += '</thead>';
            html += '<tbody>';
            //console.log(data);
            html += data.reduce(function(string, item) {
              return string + "<tr><td>" + item.stufen_id + "</td><td>" + item.name + "</td><td>" + item.abteilung + "</td><td>" + item.jahrgang + "</td><td>" + item.email + "</td></tr>";
            }, '');
            html += '</tbody>';
            html += '</table>';
            $('div#stufeTableContainer').html(html);
            makeTableEditable();
          },
          error: function(XMLHttpRequest, textStatus, errorThrown) {
            $('div#errorMessageContainer').html('<p>Could not load Stufen form server. Got error: ' + errorThrown + '</p>');
            $('div#errorMessageContainer').attr('class', 'alert alert-warning');
            $('div#errorMessageContainer').attr('role', 'warning');
          }
        });
      };

      var makeTableEditable = function makeTableEditable() {
        $('#stufenTable').Tabledit({
          url: restBaseUrl + '/wp-json/chaeschtlizettel/v1/stufen',
          nonce: nonce,
          restoreButton: false,
          hideIdentifier: false,
          deleteCallbackFunction: function() {
            loadStufenTable();
          },
          columns: {
            identifier: [0, 'stufen_id'],
            editable: [
              [1, 'name'],
              [2, 'abteilung', '{"m": "m", "f": "f"}'],
              [3, 'jahrgang'],
              [4, 'email']
            ]
          }
        });
      };

      var addNewStufe = function addNewStufe() {
        var formData = JSON.stringify($('#newStufeForm').serializeJSON());

        $.ajax({
          url: restBaseUrl + '/wp-json/chaeschtlizettel/v1/stufen/insert/',
          method: 'POST',
          beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', nonce);
          },
          error: function(XMLHttpRequest, textStatus, errorThrown) {
            $('div#errorMessageContainer').html('<p>Could not create new Stufe. Got error: ' + errorThrown + '</p>');
            $('div#errorMessageContainer').attr('class', 'alert alert-warning');
            $('div#errorMessageContainer').attr('role', 'warning');
          },
          data: formData
        }).done(function(response) {
          console.log(response);
          $("#newStufeForm")[0].reset();
          loadStufenTable();
        });
      };

      document.getElementById("newStufeFormSubmitButton").addEventListener("click", function(event) {
        event.preventDefault();
        addNewStufe();
      });

      loadStufenTable();
    };


    $.fn.StufenMemberTableFunction = function(nonce, restBaseUrl) {

      var loadStufenmemberTable = function loadStufenmemberTable() {
        console.log('Try to load stufenmember from REST');
        $.ajax({
          url: restBaseUrl + '/wp-json/chaeschtlizettel/v1/stufenmember',
          beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', nonce);
          },
          success: function(data, response) {
            var html = '<table id="stufenmemberTable" class="table table-striped table-bordered">';
            html += '<thead>';
            html += '<th>id</th><th>user_name</th><th>stufen_name</th>';
            html += '</thead>';
            html += '<tbody>';
            console.log(data);
            html += data.reduce(function(string, item) {
              return string + "<tr><td>" + item.id + "</td><td>" + item.user_name + "</td><td>" + item.stufen_name + "</td></tr>";
            }, '');
            html += '</tbody>';
            html += '</table>';
            $('div#stufeMemberTableContainer').html(html);
            loadStufen();
          },
          error: function(XMLHttpRequest, textStatus, errorThrown) {
            $('div#errorMessageContainer').html('<p>Could not load Stufenmembers form server. Got error: ' + errorThrown + '</p>');
            $('div#errorMessageContainer').attr('class', 'alert alert-warning');
            $('div#errorMessageContainer').attr('role', 'warning');
          }
        });
      };

      var loadStufen = function loadStufen() {
        $.get(restBaseUrl + '/wp-json/chaeschtlizettel/v1/stufen', {}, function(data, response) {
          var stufenNames = {};
          var stufenOptionHtml = "";
          data.forEach(function(stufe) {
            stufenNames[stufe.name] = stufe.name;
            stufenOptionHtml += '<option value="' + stufe.name + '">' + stufe.name + '</option>';
          });
          $('select#newStufenmemberStufenName').html(stufenOptionHtml);
          loadUsers(1, stufenNames);
        });
      };

      var loadUsers = function loadUsers(page, stufenNames, userNames) {
        var perPage = 10;
        if (page === undefined) {
          page = 1;
        }
        if (userNames === undefined) {
          userNames = {};
        }
        
        $.ajax({
          url: restBaseUrl + '/wp-json/wp/v2/users?per_page=' + perPage + '&page=' + page,
          type: "GET",
          beforeSend: function(xhr) {
              xhr.setRequestHeader('X-WP-Nonce', nonce);
          },
          success: function(data, response) {
            data.forEach(function(user) {
              userNames[user.id] = user.name;
            });
            console.log(userNames);
            if (data.length == perPage) {
              page++;
              loadUsers(page, stufenNames, userNames);
            } else {
              var userOptionHtml = "";
              for (var key in userNames) {
                userOptionHtml += '<option value="' + key + '">' + userNames[key] + '</option>';
              }
              $('select#newStufenmemberUserName').html(userOptionHtml);
              makeTableEditable(JSON.stringify(stufenNames), JSON.stringify(userNames));
            }
          },
          error: function(XMLHttpRequest, textStatus, errorThrown) {
            $('div#errorMessageContainer').html('<p>Could not load users form server. Got error: ' + errorThrown + '</p>');
            $('div#errorMessageContainer').attr('class', 'alert alert-warning');
            $('div#errorMessageContainer').attr('role', 'warning');
          }
        });
      };

      var makeTableEditable = function makeTableEditable(stufenNames, userNames) {
        console.log('Make stufenmemberTable editable');
        $('#stufenmemberTable').Tabledit({
          url: restBaseUrl + '/wp-json/chaeschtlizettel/v1/stufenmember',
          nonce: nonce,
          restoreButton: false,
          hideIdentifier: false,
          deleteCallbackFunction: function() {
            loadStufenmemberTable();
          },
          columns: {
            identifier: [0, 'id'],
            editable: [
              [1, 'user_name', userNames],
              [2, 'stufen_name', stufenNames]
            ]
          }
        });
      };

      var addNewStufenmember = function addNewStufenmember() {
        var formData = JSON.stringify($('#newStufenmemberForm').serializeJSON());

        $.ajax({
          url: restBaseUrl + '/wp-json/chaeschtlizettel/v1/stufenmember/insert/',
          method: 'POST',
          beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', nonce);
          },
          error: function(XMLHttpRequest, textStatus, errorThrown) {
            $('div#errorMessageContainer').html('<p>Could not add new Stufenmember. Got error: ' + errorThrown + '</p>');
            $('div#errorMessageContainer').attr('class', 'alert alert-warning');
            $('div#errorMessageContainer').attr('role', 'warning');
          },
          data: formData
        }).done(function(response) {
          console.log(response);
          $("#newStufenmemberForm")[0].reset();
          loadStufenmemberTable();
        });
      };

      document.getElementById("newStufenmemberFormSubmitButton").addEventListener("click", function(event) {
        event.preventDefault();
        addNewStufenmember();
      });

      loadStufenmemberTable();
    };
    return this;

  }
  (jQuery));
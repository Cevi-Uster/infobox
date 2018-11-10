$('#stufentable').Tabledit({
    url: 'wp-json/chaeschtlizettel/v1/stufen',
    columns: {
        identifier: [0, 'stufen_id'],
        editable: [[1, 'name']
    }
});
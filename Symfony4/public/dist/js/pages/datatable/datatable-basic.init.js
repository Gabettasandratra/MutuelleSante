var french = {
    "sEmptyTable": "Aucune donnée disponible dans le tableau",
    "sInfo": "Affichage de l'élément _START_ à _END_ sur _TOTAL_ éléments",
    "sInfoEmpty": "Affichage de l'élément 0 à 0 sur 0 élément",
    "sInfoFiltered": "(filtré à partir de _MAX_ éléments au total)",
    "sInfoPostFix": "",
    "sInfoThousands": ",",
    "sLengthMenu": "Afficher _MENU_ éléments",
    "sLoadingRecords": "Chargement...",
    "sProcessing": "Traitement...",
    "sSearch": "Rechercher :",
    "sZeroRecords": "Aucun élément correspondant trouvé",
    "oPaginate": {
        "sFirst": "Premier",
        "sLast": "Dernier",
        "sNext": "Suivant",
        "sPrevious": "Précédent"
    },
    "oAria": {
        "sSortAscending": ": activer pour trier la colonne par ordre croissant",
        "sSortDescending": ": activer pour trier la colonne par ordre décroissant"
    },
    "select": {
        "rows": {
            "_": "%d lignes sélectionnées",
            "0": "Aucune ligne sélectionnée",
            "1": "1 ligne sélectionnée"
        }
    }
};


$('#zero_config').DataTable({
    "language": french
});

$('#order').DataTable({
    "order": [
        [2, "asc"]
    ],
    "language": french
});

$('#order_2_desc').DataTable({
    "order": [
        [1, "desc"]
    ],
    "language": french
});

$('#scroll_ver').DataTable({
    "dom": "t",
    "scrollY": "50vh",
    "scrollX": true,
    "scrollCollapse": true,
    "paging": false,
    "language": french
});

var tableExcel = $('#export_excel').DataTable({
    dom: "t",
    buttons: [
        'excel'
    ]
});

tableExcel.buttons().container().appendTo($('#export_excel_btn'));
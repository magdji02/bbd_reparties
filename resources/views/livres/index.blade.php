@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">Gestion des Livres</h2>

    {{-- Formulaire de création --}}
    <div class="card mb-4">
        <div class="card-header">Ajouter un livre</div>
        <div class="card-body">
            <form id="createLivreForm">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="titre" placeholder="Titre" required>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="auteur" placeholder="Auteur" required>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="editeur" placeholder="Éditeur" required>
                    </div>
                    <div class="col-md-4">
                        <input type="number" class="form-control" name="stock" placeholder="Stock" min="0" required>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="bibliotheque" placeholder="Bibliothèque" required>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-success w-100">Enregistrer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Liste des livres --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h5>Liste des livres</h5>
            <button id="refreshBtn" class="btn btn-sm btn-outline-primary">Actualiser</button>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-striped align-middle" id="livresTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Titre</th>
                        <th>Auteur</th>
                        <th>Éditeur</th>
                        <th>Stock</th>
                        <th>Bibliothèque</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr id="loadingRow">
                        <td colspan="7" class="text-center">
                            <div class="spinner-border text-primary"></div>
                            <div>Chargement...</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
$(document).ready(function () {
    loadLivres();

    $('#refreshBtn').click(function () {
        loadLivres();
    });

    $('#createLivreForm').submit(function (e) {
        e.preventDefault();
        $.ajax({
            url: '/api/livres',
            type: 'POST',
            data: $(this).serialize(),
            success: function () {
                $('#createLivreForm')[0].reset();
                loadLivres();
                toastr.success("Livre ajouté !");
            },
            error: function (xhr) {
                let errors = xhr.responseJSON.errors;
                if (errors) {
                    Object.values(errors).forEach(msgs => toastr.error(msgs[0]));
                } else {
                    toastr.error("Erreur inattendue !");
                }
            }
        });
    });

    function loadLivres() {
        $('#loadingRow').show();
        $.get('/api/livres', function (response) {
            let tbody = $('#livresTable tbody');
            tbody.empty();
            if (response.length > 0) {
                response.forEach(livre => {
                    tbody.append(`
                        <tr>
                            <td>${livre.id}</td>
                            <td>${livre.titre}</td>
                            <td>${livre.auteur}</td>
                            <td>${livre.editeur}</td>
                            <td>${livre.stock}</td>
                            <td>${livre.bibliotheque}</td>
                            <td>
                                <button class="btn btn-sm btn-danger delete-btn" data-id="${livre.id}">Supprimer</button>
                            </td>
                        </tr>
                    `);
                });
            } else {
                tbody.append('<tr><td colspan="7" class="text-center text-muted">Aucun livre trouvé</td></tr>');
            }
        }).fail(function () {
            toastr.error("Erreur de chargement des livres");
        });
    }

    $(document).on('click', '.delete-btn', function () {
        let id = $(this).data('id');
        if (confirm("Supprimer ce livre ?")) {
            $.ajax({
                url: `/api/livres/${id}`,
                type: 'DELETE',
                success: function (res) {
                    toastr.success(res.message || "Supprimé !");
                    loadLivres();
                },
                error: function () {
                    toastr.error("Erreur lors de la suppression");
                }
            });
        }
    });
});
</script>
@endsection

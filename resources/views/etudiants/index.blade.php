@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Gestion des Étudiants</h1>

    <!-- Formulaire de création -->
    <div class="card mb-4">
        <div class="card-header">
            <h5>Ajouter un nouvel étudiant</h5>
        </div>
        <div class="card-body">
            <form id="createEtudiantForm">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="code_permanent" class="form-label">Code Permanent *</label>
                        <input type="text" class="form-control" id="code_permanent" name="code_permanent" required>
                    </div>
                    <div class="col-md-6">
                        <label for="nom" class="form-label">Nom complet *</label>
                        <input type="text" class="form-control" id="nom" name="nom" required>
                    </div>
                    <div class="col-md-4">
                        <label for="universite" class="form-label">Université *</label>
                        <select class="form-select" id="universite" name="universite" required>
                            <option value="">Sélectionner...</option>
                            <option value="UAD">UAD</option>
                            <option value="UGB">UGB</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="specialite" class="form-label">Spécialité *</label>
                        <input type="text" class="form-control" id="specialite" name="specialite" required>
                    </div>
                    <div class="col-md-4">
                        <label for="nbreEmprunts" class="form-label">Nombre d'emprunts</label>
                        <input type="number" class="form-control" id="nbreEmprunts" name="nbreEmprunts" value="0" min="0">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Enregistrer
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des étudiants -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Liste des étudiants</h5>
            <div>
                <button id="refreshBtn" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-sync-alt me-1"></i> Actualiser
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="etudiantsTable">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Code Permanent</th>
                            <th>Nom</th>
                            <th>Université</th>
                            <th>Emprunts</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr id="loadingRow">
                            <td colspan="6" class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Chargement...</span>
                                </div>
                                <p class="mt-2 mb-0">Chargement des données...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal d'édition -->
    <div class="modal fade" id="editEtudiantModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modifier étudiant</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editEtudiantForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="edit_id" name="id">
                        <div class="mb-3">
                            <label for="edit_code_permanent" class="form-label">Code Permanent</label>
                            <input type="text" class="form-control" id="edit_code_permanent" name="code_permanent" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_nom" class="form-label">Nom</label>
                            <input type="text" class="form-control" id="edit_nom" name="nom" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_universite" class="form-label">Université</label>
                            <select class="form-select" id="edit_universite" name="universite" required>
                                <option value="UAD">UAD</option>
                                <option value="UGB">UGB</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_specialite" class="form-label">Spécialité</label>
                            <input type="text" class="form-control" id="edit_specialite" name="specialite" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_nbreEmprunts" class="form-label">Emprunts</label>
                            <input type="number" class="form-control" id="edit_nbreEmprunts" name="nbreEmprunts" min="0">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" id="saveChangesBtn" class="btn btn-primary">Enregistrer</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialisation
    loadEtudiants();

    // Rafraîchissement manuel
    $('#refreshBtn').click(function() {
        loadEtudiants();
    });

    // Création d'un étudiant
    $('#createEtudiantForm').submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: '/api/etudiants',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('#createEtudiantForm')[0].reset();
                loadEtudiants();
                toastr.success('Étudiant créé avec succès');
            },
            error: function(xhr) {
                let errors = xhr.responseJSON?.errors;
                if (errors) {
                    $.each(errors, function(key, value) {
                        toastr.error(value[0]);
                    });
                } else {
                    toastr.error(xhr.responseJSON?.message || 'Erreur lors de la création');
                }
            }
        });
    });

    // Chargement des étudiants
    function loadEtudiants() {
        $('#loadingRow').show();
        $.ajax({
            url: '/api/etudiants',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                const tbody = $('#etudiantsTable tbody');
                tbody.empty();
                
                if (response.status === 'success' && response.data.length > 0) {
                    $.each(response.data, function(index, etudiant) {
                        tbody.append(`
                            <tr>
                                <td>${etudiant.id}</td>
                                <td>${etudiant.code_permanent}</td>
                                <td>${etudiant.nom}</td>
                                <td><span class="badge bg-${etudiant.universite === 'UAD' ? 'primary' : 'success'}">${etudiant.universite}</span></td>
                                <td>${etudiant.nbreEmprunts}</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary edit-btn" data-id="${etudiant.id}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger delete-btn" data-id="${etudiant.id}">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        `);
                    });
                } else {
                    tbody.append(`
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                Aucun étudiant trouvé
                            </td>
                        </tr>
                    `);
                }
            },
            error: function(xhr) {
                $('#etudiantsTable tbody').html(`
                    <tr>
                        <td colspan="6" class="text-center py-4 text-danger">
                            ${xhr.responseJSON?.message || 'Erreur lors du chargement des données'}
                        </td>
                    </tr>
                `);
            },
            complete: function() {
                $('#loadingRow').hide();
            }
        });
    }

    // Édition d'un étudiant
    $(document).on('click', '.edit-btn', function() {
        const id = $(this).data('id');
        $.ajax({
            url: `/api/etudiants/${id}`,
            type: 'GET',
            success: function(response) {
                $('#edit_id').val(response.id);
                $('#edit_code_permanent').val(response.code_permanent);
                $('#edit_nom').val(response.nom);
                $('#edit_universite').val(response.universite);
                $('#edit_specialite').val(response.specialite);
                $('#edit_nbreEmprunts').val(response.nbreEmprunts);
                $('#editEtudiantModal').modal('show');
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Erreur lors du chargement');
            }
        });
    });

    // Sauvegarde des modifications
    $('#saveChangesBtn').click(function() {
        const id = $('#edit_id').val();
        $.ajax({
            url: `/api/etudiants/${id}`,
            type: 'PUT',
            data: $('#editEtudiantForm').serialize(),
            success: function(response) {
                $('#editEtudiantModal').modal('hide');
                loadEtudiants();
                toastr.success('Modifications enregistrées');
            },
            error: function(xhr) {
                let errors = xhr.responseJSON?.errors;
                if (errors) {
                    $.each(errors, function(key, value) {
                        toastr.error(value[0]);
                    });
                } else {
                    toastr.error(xhr.responseJSON?.message || 'Erreur lors de la mise à jour');
                }
            }
        });
    });

    // Suppression d'un étudiant
    $(document).on('click', '.delete-btn', function() {
        const id = $(this).data('id');
        if (confirm('Voulez-vous vraiment supprimer cet étudiant ?')) {
            $.ajax({
                url: `/api/etudiants/${id}`,
                type: 'DELETE',
                success: function(response) {
                    loadEtudiants();
                    toastr.success(response.message || 'Étudiant supprimé');
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Erreur lors de la suppression');
                }
            });
        }
    });
});
</script>
@endsection
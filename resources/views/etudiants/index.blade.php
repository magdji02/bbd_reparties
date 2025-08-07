@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestion des Étudiants</h2>
        <button id="refreshBtn" class="btn btn-outline-primary">
            <i class="fas fa-sync-alt me-1"></i> Actualiser
        </button>
    </div>

    {{-- Formulaire de création --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Ajouter un étudiant</h5>
        </div>
        <div class="card-body">
            <form id="createEtudiantForm" class="row g-3">
                @csrf
                <div class="col-md-3">
                    <label class="form-label">Code Permanent</label>
                    <input type="text" class="form-control" name="code_permanent" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Nom complet</label>
                    <input type="text" class="form-control" name="nom" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Université</label>
                    <select class="form-select" name="universite" required>
                        <option value="UAD">UAD</option>
                        <option value="UGB">UGB</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Spécialité</label>
                    <input type="text" class="form-control" name="specialite" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Emprunts</label>
                    <input type="number" class="form-control" name="nbreEmprunts" value="0" min="0">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Liste des étudiants --}}
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Liste des étudiants</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="etudiantsTable">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">ID</th>
                            <th width="15%">Code Permanent</th>
                            <th width="20%">Nom</th>
                            <th width="15%">Université</th>
                            <th width="20%">Spécialité</th>
                            <th width="10%">Emprunts</th>
                            <th width="15%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr id="loadingRow">
                            <td colspan="7" class="text-center py-4">
                                <div class="spinner-border text-primary"></div>
                                <div class="mt-2">Chargement en cours...</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Configuration de Toastr
    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: "toast-top-right",
        timeOut: 5000
    };

    // Chargement initial
    loadEtudiants();

    // Actualisation manuelle
    $('#refreshBtn').click(function() {
        loadEtudiants();
    });

    // Ajout d'un étudiant
    $('#createEtudiantForm').submit(function(e) {
        e.preventDefault();
        let btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Enregistrement...');

        $.ajax({
            url: '/api/etudiants',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('#createEtudiantForm')[0].reset();
                loadEtudiants();
                toastr.success('Étudiant ajouté avec succès');
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    $.each(xhr.responseJSON.errors, function(key, errors) {
                        toastr.error(errors[0]);
                    });
                } else {
                    toastr.error('Une erreur est survenue lors de l\'ajout');
                }
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Enregistrer');
            }
        });
    });

    // Suppression d'un étudiant
    $(document).on('click', '.delete-btn', function() {
        let id = $(this).data('id');
        let row = $(this).closest('tr');
        
        if (confirm('Êtes-vous sûr de vouloir supprimer cet étudiant ?')) {
            row.css('opacity', '0.5');
            
            $.ajax({
                url: '/api/etudiants/' + id,
                type: 'DELETE',
                success: function(response) {
                    toastr.success(response.message || 'Étudiant supprimé');
                    loadEtudiants();
                },
                error: function() {
                    row.css('opacity', '1');
                    toastr.error('Erreur lors de la suppression');
                }
            });
        }
    });

    // Fonction de chargement des étudiants
    function loadEtudiants() {
        $('#loadingRow').show();
        
        $.ajax({
            url: '/api/etudiants',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                let tbody = $('#etudiantsTable tbody');
                tbody.empty();
                
                if (response.data && response.data.length > 0) {
                    $.each(response.data, function(index, etudiant) {
                        let badgeClass = etudiant.universite === 'UAD' ? 'bg-primary' : 'bg-success';
                        
                        tbody.append(`
                            <tr>
                                <td>${etudiant.id}</td>
                                <td>${etudiant.code_permanent}</td>
                                <td>${etudiant.nom}</td>
                                <td><span class="badge ${badgeClass}">${etudiant.universite}</span></td>
                                <td>${etudiant.specialite}</td>
                                <td>${etudiant.nbreEmprunts}</td>
                                <td>
                                    <button class="btn btn-sm btn-danger delete-btn" data-id="${etudiant.id}">
                                        <i class="fas fa-trash-alt me-1"></i> Supprimer
                                    </button>
                                </td>
                            </tr>
                        `);
                    });
                } else {
                    tbody.append(`
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                Aucun étudiant trouvé
                            </td>
                        </tr>
                    `);
                }
            },
            error: function() {
                $('#etudiantsTable tbody').html(`
                    <tr>
                        <td colspan="7" class="text-center text-danger py-4">
                            Erreur lors du chargement des données
                        </td>
                    </tr>
                `);
            },
            complete: function() {
                $('#loadingRow').hide();
            }
        });
    }
});
</script>
@endsection
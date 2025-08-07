@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestion des Emprunts</h2>
        <button id="refreshBtn" class="btn btn-outline-primary">
            <i class="fas fa-sync-alt me-1"></i> Actualiser
        </button>
    </div>

    {{-- Formulaire de création --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Nouvel emprunt</h5>
        </div>
        <div class="card-body">
            <form id="createEmpruntForm" class="row g-3">
                @csrf
                <div class="col-md-4">
                    <label class="form-label">Étudiant *</label>
                    <select class="form-select" name="etudiant_id" required id="etudiantSelect">
                        <option value="">Choisir un étudiant...</option>
                        @foreach(\App\Models\Etudiant::all() as $etudiant)
                            <option value="{{ $etudiant->id }}">{{ $etudiant->nom }} ({{ $etudiant->code_permanent }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Livre *</label>
                    <select class="form-select" name="livre_id" required id="livreSelect">
                        <option value="">Choisir un livre...</option>
                        @foreach(\App\Models\Livre::all() as $livre)
                            <option value="{{ $livre->id }}">{{ $livre->titre }} ({{ $livre->auteur }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date emprunt *</label>
                    <input type="date" class="form-control" name="date_emprunt" required value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date retour *</label>
                    <input type="date" class="form-control" name="date_retour" required value="{{ date('Y-m-d', strtotime('+14 days')) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Statut *</label>
                    <select class="form-select" name="statut" required>
                        <option value="emprunté">Emprunté</option>
                        <option value="retourné">Retourné</option>
                        <option value="en retard">En retard</option>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Liste des emprunts --}}
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Liste des emprunts</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="empruntsTable">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Étudiant</th>
                            <th>Livre</th>
                            <th>Date emprunt</th>
                            <th>Date retour</th>
                            <th>Statut</th>
                            <th>Actions</th>
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

{{-- Modal pour modification --}}
<div class="modal fade" id="editEmpruntModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modifier emprunt</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editEmpruntForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" id="edit_id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Étudiant *</label>
                            <select class="form-select" name="etudiant_id" required id="edit_etudiant_id">
                                @foreach(\App\Models\Etudiant::all() as $etudiant)
                                    <option value="{{ $etudiant->id }}">{{ $etudiant->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Livre *</label>
                            <select class="form-select" name="livre_id" required id="edit_livre_id">
                                @foreach(\App\Models\Livre::all() as $livre)
                                    <option value="{{ $livre->id }}">{{ $livre->titre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date emprunt *</label>
                            <input type="date" class="form-control" name="date_emprunt" required id="edit_date_emprunt">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date retour *</label>
                            <input type="date" class="form-control" name="date_retour" required id="edit_date_retour">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Statut *</label>
                            <select class="form-select" name="statut" required id="edit_statut">
                                <option value="emprunté">Emprunté</option>
                                <option value="retourné">Retourné</option>
                                <option value="en retard">En retard</option>
                            </select>
                        </div>
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
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Configuration
    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: "toast-top-right",
        timeOut: 5000
    };

    // Initialisation
    loadEmprunts();

    // Actualisation
    $('#refreshBtn').click(loadEmprunts);

    // Création d'un emprunt
    $('#createEmpruntForm').submit(function(e) {
        e.preventDefault();
        let btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Enregistrement...');

        $.ajax({
            url: '/api/emprunts',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('#createEmpruntForm')[0].reset();
                loadEmprunts();
                toastr.success('Emprunt enregistré avec succès');
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    $.each(xhr.responseJSON.errors, function(key, errors) {
                        toastr.error(errors[0]);
                    });
                } else {
                    toastr.error('Erreur lors de la création');
                }
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Enregistrer');
            }
        });
    });

    // Chargement des emprunts
    function loadEmprunts() {
        $('#loadingRow').show();
        
        $.ajax({
            url: '/api/emprunts',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                let tbody = $('#empruntsTable tbody');
                tbody.empty();
                
                if (response.length > 0) {
                    response.forEach(emprunt => {
                        let badgeClass = {
                            'emprunté': 'bg-primary',
                            'retourné': 'bg-success',
                            'en retard': 'bg-danger'
                        }[emprunt.statut] || 'bg-secondary';
                        
                        let today = new Date();
                        let retour = new Date(emprunt.date_retour);
                        let isLate = emprunt.statut !== 'retourné' && retour < today;
                        
                        if (isLate) {
                            badgeClass = 'bg-danger';
                            emprunt.statut = 'en retard';
                        }

                        tbody.append(`
                            <tr>
                                <td>${emprunt.id}</td>
                                <td>${emprunt.etudiant.nom} (${emprunt.etudiant.code_permanent})</td>
                                <td>${emprunt.livre.titre} - ${emprunt.livre.auteur}</td>
                                <td>${emprunt.date_emprunt}</td>
                                <td class="${isLate ? 'text-danger fw-bold' : ''}">${emprunt.date_retour}</td>
                                <td><span class="badge ${badgeClass}">${emprunt.statut}</span></td>
                                <td>
                                    <button class="btn btn-sm btn-warning edit-btn me-1" data-id="${emprunt.id}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-btn" data-id="${emprunt.id}">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        `);
                    });
                } else {
                    tbody.append(`
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                Aucun emprunt trouvé
                            </td>
                        </tr>
                    `);
                }
            },
            error: function() {
                $('#empruntsTable tbody').html(`
                    <tr>
                        <td colspan="7" class="text-center text-danger py-4">
                            Erreur lors du chargement
                        </td>
                    </tr>
                `);
            },
            complete: function() {
                $('#loadingRow').hide();
            }
        });
    }

    // Préparation de la modification
    $(document).on('click', '.edit-btn', function() {
        let id = $(this).data('id');
        
        $.ajax({
            url: `/api/emprunts/${id}`,
            type: 'GET',
            success: function(response) {
                $('#edit_id').val(response.id);
                $('#edit_etudiant_id').val(response.etudiant_id);
                $('#edit_livre_id').val(response.livre_id);
                $('#edit_date_emprunt').val(response.date_emprunt);
                $('#edit_date_retour').val(response.date_retour);
                $('#edit_statut').val(response.statut);
                $('#editEmpruntModal').modal('show');
            },
            error: function() {
                toastr.error('Erreur lors du chargement');
            }
        });
    });

    // Enregistrement des modifications
    $('#saveChangesBtn').click(function() {
        let id = $('#edit_id').val();
        let btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Enregistrement...');
        
        $.ajax({
            url: `/api/emprunts/${id}`,
            type: 'PUT',
            data: $('#editEmpruntForm').serialize(),
            success: function(response) {
                $('#editEmpruntModal').modal('hide');
                loadEmprunts();
                toastr.success('Modifications enregistrées');
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    $.each(xhr.responseJSON.errors, function(key, errors) {
                        toastr.error(errors[0]);
                    });
                } else {
                    toastr.error('Erreur lors de la mise à jour');
                }
            },
            complete: function() {
                btn.prop('disabled', false).html('Enregistrer');
            }
        });
    });

    // Suppression d'un emprunt
    $(document).on('click', '.delete-btn', function() {
        let id = $(this).data('id');
        let row = $(this).closest('tr');
        
        if (confirm('Supprimer cet emprunt ? Cette action est irréversible.')) {
            row.css('opacity', '0.5');
            
            $.ajax({
                url: `/api/emprunts/${id}`,
                type: 'DELETE',
                success: function(response) {
                    toastr.success(response.message || 'Supprimé avec succès');
                    loadEmprunts();
                },
                error: function() {
                    row.css('opacity', '1');
                    toastr.error('Erreur lors de la suppression');
                }
            });
        }
    });
});
</script>
@endsection
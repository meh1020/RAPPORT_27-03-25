<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\PecheController;
use App\Http\Controllers\RapportController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ListeMadaController;
use App\Http\Controllers\AvurnavController;
use App\Http\Controllers\SitrepController;
use App\Http\Controllers\ZoneController;
use App\Http\Controllers\PollutionController;
use App\Http\Controllers\BilanSarController;
use App\Http\Controllers\TypeEvenementController;
use App\Http\Controllers\CauseEvenementController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\test;
use App\Http\Controllers\MerTerritorial;
use App\Http\Controllers\CabotageController;
use App\Http\Controllers\VedetteSar;
use App\Http\Controllers\PassageInoffensifController;
use App\Http\Controllers\SuiviNavireParticulierController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\PortController;

// Page d'accueil publique
Route::get('/', function () {
    return view('welcome');
});

// -------------------------
// ROUTES D'AUTHENTIFICATION
// -------------------------

// Affichage et traitement du formulaire de connexion
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);

// Déconnexion
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// Mot de passe oublié et réinitialisation
Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

// Changement de mot de passe pour l'utilisateur connecté

Route::get('password/change', [PasswordController::class, 'showChangeForm'])->name('password.change.form');
Route::post('password/change', [PasswordController::class, 'change'])->name('password.change');


use App\Http\Controllers\UserController;

Route::middleware(['auth'])->group(function () {

    Route::middleware(['superadmin'])->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});

use App\Http\Controllers\ProfileController;

Route::middleware(['auth'])->group(function () {
    // Affiche le formulaire d'édition du profil de l'utilisateur connecté
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    // Traite la mise à jour des informations
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    
    // Vos autres routes protégées...
});


// -------------------------
// ROUTES PROTÉGÉES (authentification requise)
// -------------------------
Route::group(['middleware' => 'auth'], function () {

    // DASHBOARD
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ARTICLES
    Route::get('/export-articles', [ArticleController::class, 'exportCSV'])->name('articles.export');
    Route::post('/import-articles', [ArticleController::class, 'importCSV'])->name('articles.import');
    Route::get('/articles/filter', [ArticleController::class, 'filter'])->name('articles.filter');
    Route::get('/articles/export-filtered', [ArticleController::class, 'exportFilteredCSV'])->name('articles.export.filtered');
    Route::resource('articles', ArticleController::class);

    // LISTMADA
    Route::get('/listeMada', [ListeMadaController::class, 'index'])->name('list.mada');
    Route::delete('/listeMada/{id}/destroy', [ListeMadaController::class, 'index'])->name('listmadas.destroy');
    Route::get('/export-listmada', [ListeMadaController::class, 'export'])->name('listmadas.export');
    Route::post('/import-listmada', [ListeMadaController::class, 'import'])->name('listmadas.import');

    // AVURNAV
    Route::get('/export-pdf_nav/{id}', [AvurnavController::class, 'exportPDF'])->name('export.pdf');
    Route::get('/avurnav', [AvurnavController::class, 'index'])->name('avurnav.index');
    Route::get('/avurnav/create', [AvurnavController::class, 'create'])->name('avurnav.create');
    Route::post('/avurnav/store', [AvurnavController::class, 'store']);

    // POLLUTION
    Route::resource('pollutions', PollutionController::class);
    Route::get('/export-pdf/{id}', [PollutionController::class, 'exportPDF'])->name('pollutions.exportPDF');

    // SITREP
    Route::resource('sitreps', SitrepController::class);
    Route::get('/sitreps/{id}/export-pdf', [SitrepController::class, 'exportPDF'])->name('sitreps.exportPDF');

    // ZONES
    Route::get('/zone/{id}', [ZoneController::class, 'show'])->name('zone.show');
    Route::get('/zone/1', [ZoneController::class, 'show'])->name('zone.show1');
    Route::post('/import-articles/{id}', [ZoneController::class, 'importCSV'])->name('zone.import');

    // TYPE ET CAUSE EVENEMENT
    Route::prefix('bilan_sars')->group(function () {
        Route::resource('type_evenements', TypeEvenementController::class);
        Route::resource('cause_evenements', CauseEvenementController::class);
        Route::resource('regions', RegionController::class);
    });

    // Peche
    Route::resource('peche', PecheController::class);
    Route::post('/import-peche', [PecheController::class, 'importCSV'])->name('peche.import');

    // BILAN_SARS
    Route::resource('bilan_sars', BilanSarController::class)->except(['edit', 'update', 'show']);
    Route::get('general', [test::class, 'index']);
    Route::post('/bilan_sars/import', [BilanSarController::class, 'import'])->name('bilan_sars.import');

    // RAPPORTS
    Route::get('/rapports', [RapportController::class, 'index'])->name('rapport.index');
    Route::get('/rapport', [RapportController::class, 'index'])->name('rapport.index');
    Route::get('/rapport/pdf', [RapportController::class, 'exportPdf'])->name('rapport.exportPdf');

    // CABOTAGE
    Route::resource('cabotage', CabotageController::class);
    Route::post('/cabotage/store', [CabotageController::class, 'store'])->name('cabotage.store');
    Route::post('/cabotage/import', [CabotageController::class, 'import'])->name('cabotage.import');

    // MER TERRITORIAL
    Route::resource('mer_territorial', MerTerritorial::class);
    Route::post('/import-mer_territorial', [MerTerritorial::class, 'importCSV'])->name('mer_territorial.import');

    // VEDETTE SAR
    Route::resource('vedette_sar', VedetteSar::class);
    Route::post('/vedette_sar/store', [VedetteSar::class, 'store'])->name('vedette_sar.store');

    // PASSAGE INNOFENSIF
    Route::resource('passage_inoffensifs', PassageInoffensifController::class)->only(['index', 'create', 'store', 'destroy']);
    Route::post('passage_inoffensifs/import', [PassageInoffensifController::class, 'import'])->name('passage_inoffensifs.import');

    // SUIVI DES NAVIRES PARTICULIERS
    Route::resource('suivi_navire_particuliers', SuiviNavireParticulierController::class)->only(['index', 'create', 'store', 'destroy']);

    // DESTINATIONS (filtre destination mada)
    Route::get('/destinations', [DestinationController::class, 'index'])->name('destinations.index');
    Route::get('/destinations/create', [DestinationController::class, 'create'])->name('destinations.create');
    Route::post('/destinations', [DestinationController::class, 'store'])->name('destinations.store');
    Route::delete('/destinations/{destination}', [DestinationController::class, 'destroy'])->name('destinations.destroy');

    // PORTS
    Route::resource('ports', PortController::class)->except('show');
});

<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackOffice\ActivityLogController;
use App\Http\Controllers\BackOffice\AiBrainController;
use App\Http\Controllers\BackOffice\AiPromptController;
use App\Http\Controllers\BackOffice\DocumentStyleController;
use App\Http\Controllers\BackOffice\GenreController;
use App\Http\Controllers\BackOffice\AudienceController;
use App\Http\Controllers\BackOffice\KdpLayoutController;
use App\Http\Controllers\BackOffice\LanguageController;
use App\Http\Controllers\BackOffice\MediaController;
use App\Http\Controllers\BackOffice\NovelGeneratorController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\BackOffice\UserController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;
use romanzipp\QueueMonitor\Controllers\ShowQueueMonitorController;

Route::middleware('guest')->group(function () {
    Route::prefix('login')->group(function () {
        Route::get('/', [AuthController::class, 'loginForm'])->name('login'); // Default route name
        Route::post('/', [AuthController::class, 'login'])->name('login.submit')->middleware('throttle:3,1');
    });

    Route::prefix('register')->group(function () {
        Route::get('/', [AuthController::class, 'registerForm'])->name('register'); // Default route name
        Route::post('/', [AuthController::class, 'register'])->name('register.submit');
    });

    Route::prefix('forgot-password')->group(function () {
        Route::get('/', [AuthController::class, 'forgetPasswordForm'])->name('forgot-password');
        Route::post('/', [AuthController::class, 'forgotPassword'])->name('forgot-password.submit');
    });

    Route::prefix('password-reset')->group(function () {
        Route::get('{email}/{token}', [AuthController::class, 'resetPasswordForm'])->name('password.reset'); // Default route name
        Route::post('{email}/{token}', [AuthController::class, 'resetPassword'])->name('password.reset.submit');
    });
});

Route::middleware('auth')->group(function () {
    Route::prefix('verification')->group(function () {
        Route::get('notice', [AuthController::class, 'emailVerificationNotice'])->name('verification.notice');
        Route::post('resend', [AuthController::class, 'emailVerificationResend'])->name('verification.resend');
        Route::get('verification/{id}/{hash}', [AuthController::class, 'emailVerification'])->middleware('signed')->name('verification.verify'); // Default route name
    });

    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});

Route::prefix('auth-user')->name('auth-user.')->middleware('auth')->group(function () {
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('index', [AuthController::class, 'dashboard'])->name('index');
    });

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('index', [AuthController::class, 'profileIndex'])->name('index');
        Route::patch('update', [AuthController::class, 'profileUpdate'])->name('update');
    });

    Route::prefix('account')->name('account.')->group(function () {
        Route::get('index', [AuthController::class, 'accountIndex'])->name('index');
        Route::patch('update', [AuthController::class, 'accountUpdate'])->name('update');
    });
});

Route::prefix('search')->name('search.')->group(function () {

    Route::middleware(['response.cache:3600,public,300,etag'])->group(function () {
        Route::get('per-pages', [SearchController::class, 'perPages'])->name('per-pages');
        Route::get('genders', [SearchController::class, 'genders'])->name('genders');
        Route::get('religions', [SearchController::class, 'religions'])->name('religions');
        Route::get('marital-statuses', [SearchController::class, 'maritalStatuses'])->name('marital-statuses');

        Route::get('activity-log-events', [SearchController::class, 'activityLogEvents'])->name('activity-log-events');
        Route::get('activity-log-subject-types', [SearchController::class, 'activityLogSubjectTypes'])->name('activity-log-subject-types');

        Route::get('user-permissions', [SearchController::class, 'userPermissions'])->name('user-permissions');
        Route::get('user-permissions-by-group', [SearchController::class, 'userPermissionsByGroup'])->name('user-permissions-by-group');

        Route::get('novel-continuities', [SearchController::class, 'novelContinuities'])->name('novel-continuities');
        Route::get('novel-generator-statuses', [SearchController::class, 'novelGeneratorStatuses'])->name('novel-generator-statuses');
        Route::get('novel-generator-step-statuses', [SearchController::class, 'novelGeneratorStepStatuses'])->name('novel-generator-step-statuses');

    });

    Route::middleware(['response.cache:60,public,30,etag'])->group(function () {
        Route::get('genres', [SearchController::class, 'genres'])->name('genres');
        Route::get('audiences', [SearchController::class, 'audiences'])->name('audiences');
        Route::get('languages', [SearchController::class, 'languages'])->name('languages');
        Route::get('ai-brains', [SearchController::class, 'aiBrains'])->name('ai-brains');
        Route::get('kdp-layouts', [SearchController::class, 'kdpLayouts'])->name('kdp-layouts');
        Route::get('document-styles', [SearchController::class, 'documentStyles'])->name('document-styles');
        Route::get('ai-prompts', [SearchController::class, 'aiPrompts'])->name('ai-prompts');
        Route::get('users', [SearchController::class, 'users'])->name('users');

        Route::get('ai-brain/{slugOrId}', [SearchController::class, 'aiBrain'])->name('ai-brain');
        Route::get('user-permission/{slugOrId}', [SearchController::class, 'userPermission'])->name('user-permission');
    });

    Route::middleware(['response.cache:60,private,300,etag'])->get('user/{slugOrId}', [SearchController::class, 'user'])->name('user');
});

Route::prefix('back-office')->name('back-office.')->middleware(['auth'])->group(function () {

    Route::prefix('medias')->name('medias.')->group(function () {
        Route::get('/', [MediaController::class, 'index'])->name('index');
        Route::get('details/{slug}', [MediaController::class, 'details'])->name('details');
        Route::delete('delete/{slug}', [MediaController::class, 'delete'])->name('delete');

        Route::post('quick-save', [MediaController::class, 'quickSave'])->name('quick-save');
        Route::patch('quick-update/{slug}', [MediaController::class, 'quickUpdate'])->name('quick-update');
    });

    Route::prefix('genres')->name('genres.')->group(function () {
        Route::get('/', [GenreController::class, 'index'])->name('index');
        Route::get('create', [GenreController::class, 'create'])->name('create');
        Route::get('edit/{slug}', [GenreController::class, 'edit'])->name('edit');
        Route::get('details/{slug}', [GenreController::class, 'details'])->name('details');

        Route::post('save', [GenreController::class, 'save'])->name('save');
        Route::patch('update/{slug}', [GenreController::class, 'update'])->name('update');
        Route::delete('delete/{slug}', [GenreController::class, 'delete'])->name('delete');
    });

    Route::prefix('audiences')->name('audiences.')->group(function () {
        Route::get('/', [AudienceController::class, 'index'])->name('index');
        Route::get('create', [AudienceController::class, 'create'])->name('create');
        Route::get('edit/{slug}', [AudienceController::class, 'edit'])->name('edit');
        Route::get('details/{slug}', [AudienceController::class, 'details'])->name('details');

        Route::post('save', [AudienceController::class, 'save'])->name('save');
        Route::patch('update/{slug}', [AudienceController::class, 'update'])->name('update');
        Route::delete('delete/{slug}', [AudienceController::class, 'delete'])->name('delete');
    });

    Route::prefix('languages')->name('languages.')->group(function () {
        Route::get('/', [LanguageController::class, 'index'])->name('index');
        Route::get('create', [LanguageController::class, 'create'])->name('create');
        Route::get('edit/{slug}', [LanguageController::class, 'edit'])->name('edit');
        Route::get('details/{slug}', [LanguageController::class, 'details'])->name('details');

        Route::post('save', [LanguageController::class, 'save'])->name('save');
        Route::patch('update/{slug}', [LanguageController::class, 'update'])->name('update');
        Route::delete('delete/{slug}', [LanguageController::class, 'delete'])->name('delete');
    });

    Route::prefix('ai-brains')->name('ai-brains.')->group(function () {
        Route::get('/', [AiBrainController::class, 'index'])->name('index');
        Route::get('details/{slug}', [AiBrainController::class, 'details'])->name('details');

        Route::get('create', [AiBrainController::class, 'create'])->name('create');
        Route::get('edit/{slug}', [AiBrainController::class, 'edit'])->name('edit');

        Route::post('save', [AiBrainController::class, 'save'])->name('save');
        Route::patch('update/{slug}', [AiBrainController::class, 'update'])->name('update');
        Route::delete('delete/{slug}', [AiBrainController::class, 'delete'])->name('delete');
    });

    Route::prefix('kdp-layouts')->name('kdp-layouts.')->group(function () {
        Route::get('/', [KdpLayoutController::class, 'index'])->name('index');
        Route::get('details/{slug}', [KdpLayoutController::class, 'details'])->name('details');
    });

    Route::prefix('ai-prompts')->name('ai-prompts.')->group(function () {
        Route::get('/', [AiPromptController::class, 'index'])->name('index');
        Route::get('edit/{slug}', [AiPromptController::class, 'edit'])->name('edit');
        Route::get('details/{slug}', [AiPromptController::class, 'details'])->name('details');

        Route::post('save', [AiPromptController::class, 'save'])->name('save');
        Route::patch('update/{slug}', [AiPromptController::class, 'update'])->name('update');
    });

    Route::prefix('document-styles')->name('document-styles.')->group(function () {
        Route::get('/', [DocumentStyleController::class, 'index'])->name('index');
        Route::get('details/{slug}', [DocumentStyleController::class, 'details'])->name('details');
    });

    Route::prefix('users')->name('users.')->middleware(['is.super.admin'])->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('create', [UserController::class, 'create'])->name('create');
        Route::get('edit/{slug}', [UserController::class, 'edit'])->name('edit');
        Route::get('details/{slug}', [UserController::class, 'details'])->name('details');

        Route::post('save', [UserController::class, 'save'])->name('save');
        Route::patch('update/{slug}', [UserController::class, 'update'])->name('update');
        Route::delete('delete/{slug}', [UserController::class, 'delete'])->name('delete');
        Route::patch('active/{slug}', [UserController::class, 'active'])->name('active');
        Route::patch('inactive/{slug}', [UserController::class, 'inactive'])->name('inactive');
    });

    Route::prefix('novel-generators')->name('novel-generators.')->group(function () {
        Route::get('/', [NovelGeneratorController::class, 'index'])->name('index');
        Route::post('step-1-save', [NovelGeneratorController::class, 'save'])->name('step-1-save');

        Route::delete('delete/{slug}', [NovelGeneratorController::class, 'delete'])->name('delete');
    });

    Route::prefix('activity-logs')->name('activity-logs.')->group(function () {
        Route::get('index', [ActivityLogController::class, 'index'])->name('index');

        Route::get('details/{slug}', [ActivityLogController::class, 'details'])->name('details');
        Route::get('{modelSlug}/show-all/{recordSlug}', [ActivityLogController::class, 'indexForModel'])->name('show-all');

        Route::delete('delete/{slug}', [ActivityLogController::class, 'delete'])->name('delete');
    });

    Route::prefix('queue-monitor')->name('queue-monitor.')->middleware(['is.super.admin'])->group(function () {
        Route::get('/', ShowQueueMonitorController::class)->name('index');
    });

});

Route::get('/', function () {
    return redirect()->route('home');
});

Route::get('home', [PageController::class, 'home'])->name('home');

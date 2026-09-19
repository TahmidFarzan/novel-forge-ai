<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackOffice\ActivityLogController;
use App\Http\Controllers\BackOffice\AiBrainController;
use App\Http\Controllers\BackOffice\AiBrainOutputTypeController;
use App\Http\Controllers\BackOffice\AiPromptController;
use App\Http\Controllers\BackOffice\AudienceController;
use App\Http\Controllers\BackOffice\DocumentStyleController;
use App\Http\Controllers\BackOffice\GenreController;
use App\Http\Controllers\BackOffice\KdpLayoutController;
use App\Http\Controllers\BackOffice\LanguageController;
use App\Http\Controllers\BackOffice\MediaController;
use App\Http\Controllers\BackOffice\NovelController;
use App\Http\Controllers\BackOffice\NovelTypeController;
use App\Http\Controllers\BackOffice\UserController;
use App\Http\Controllers\PageController;
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

        Route::get('novel-statuses', [SearchController::class, 'novelStatuses'])->name('novel-statuses');
    });

    Route::middleware(['response.cache:60,public,30,etag'])->group(function () {
        Route::get('genres', [SearchController::class, 'genres'])->name('genres');
        Route::get('audiences', [SearchController::class, 'audiences'])->name('audiences');
        Route::get('novel-types', [SearchController::class, 'novelTypes'])->name('novel-types');
        Route::get('languages', [SearchController::class, 'languages'])->name('languages');
        Route::get('ai-brains', [SearchController::class, 'aiBrains'])->name('ai-brains');
        Route::get('kdp-layouts', [SearchController::class, 'kdpLayouts'])->name('kdp-layouts');
        Route::get('document-styles', [SearchController::class, 'documentStyles'])->name('document-styles');
        Route::get('ai-prompts', [SearchController::class, 'aiPrompts'])->name('ai-prompts');
        Route::get('ai-brain-output-types', [SearchController::class, 'aiBrainOutputTypes'])->name('ai-brain-output-types');
        Route::get('users', [SearchController::class, 'users'])->name('users');

        Route::get('ai-brain/{slugOrId}', [SearchController::class, 'aiBrain'])->name('ai-brain');
        Route::get('user-permission/{slugOrId}', [SearchController::class, 'userPermission'])->name('user-permission');
    });

    Route::middleware(['response.cache:60,private,300,etag'])->get('user/{slugOrId}', [SearchController::class, 'user'])->name('user');
});

Route::prefix('back-office')->name('back-office.')->middleware(['auth'])->group(function () {

    Route::prefix('medias')->name('medias.')->group(function () {
        Route::get('/', [MediaController::class, 'index'])->name('index');
        Route::post('quick-save', [MediaController::class, 'quickSave'])->name('quick-save');

        Route::prefix('{slug}')->group(function () {
            Route::get('details', [MediaController::class, 'details'])->name('details');
            Route::delete('delete', [MediaController::class, 'delete'])->name('delete');
            Route::patch('quick-update', [MediaController::class, 'quickUpdate'])->name('quick-update');
        });
    });

    Route::prefix('genres')->name('genres.')->group(function () {
        Route::get('/', [GenreController::class, 'index'])->name('index');
        Route::get('create', [GenreController::class, 'create'])->name('create');
        Route::post('save', [GenreController::class, 'save'])->name('save');

        Route::prefix('{slug}')->group(function () {
            Route::get('edit', [GenreController::class, 'edit'])->name('edit');
            Route::get('details', [GenreController::class, 'details'])->name('details');
            Route::patch('update', [GenreController::class, 'update'])->name('update');
            Route::delete('delete', [GenreController::class, 'delete'])->name('delete');
        });
    });

    Route::prefix('audiences')->name('audiences.')->group(function () {
        Route::get('/', [AudienceController::class, 'index'])->name('index');
        Route::get('create', [AudienceController::class, 'create'])->name('create');
        Route::post('save', [AudienceController::class, 'save'])->name('save');

        Route::prefix('{slug}')->group(function () {
            Route::get('edit', [AudienceController::class, 'edit'])->name('edit');
            Route::get('details', [AudienceController::class, 'details'])->name('details');
            Route::patch('update', [AudienceController::class, 'update'])->name('update');
            Route::delete('delete', [AudienceController::class, 'delete'])->name('delete');
        });
    });

    Route::prefix('novel-types')->name('novel-types.')->group(function () {
        Route::get('/', [NovelTypeController::class, 'index'])->name('index');
        Route::get('create', [NovelTypeController::class, 'create'])->name('create');
        Route::post('save', [NovelTypeController::class, 'save'])->name('save');

        Route::prefix('{slug}')->group(function () {
            Route::get('edit', [NovelTypeController::class, 'edit'])->name('edit');
            Route::get('details', [NovelTypeController::class, 'details'])->name('details');
            Route::patch('update', [NovelTypeController::class, 'update'])->name('update');
            Route::delete('delete', [NovelTypeController::class, 'delete'])->name('delete');
        });
    });

    Route::prefix('languages')->name('languages.')->group(function () {
        Route::get('/', [LanguageController::class, 'index'])->name('index');
        Route::get('create', [LanguageController::class, 'create'])->name('create');
        Route::post('save', [LanguageController::class, 'save'])->name('save');

        Route::prefix('{slug}')->group(function () {
            Route::get('edit', [LanguageController::class, 'edit'])->name('edit');
            Route::get('details', [LanguageController::class, 'details'])->name('details');
            Route::patch('update', [LanguageController::class, 'update'])->name('update');
            Route::delete('delete', [LanguageController::class, 'delete'])->name('delete');
        });
    });

    Route::prefix('ai-brains')->name('ai-brains.')->group(function () {
        Route::get('/', [AiBrainController::class, 'index'])->name('index');
        Route::get('create', [AiBrainController::class, 'create'])->name('create');
        Route::post('save', [AiBrainController::class, 'save'])->name('save');

        Route::prefix('{slug}')->group(function () {
            Route::get('edit', [AiBrainController::class, 'edit'])->name('edit');
            Route::get('details', [AiBrainController::class, 'details'])->name('details');
            Route::patch('update', [AiBrainController::class, 'update'])->name('update');
            Route::delete('delete', [AiBrainController::class, 'delete'])->name('delete');
        });
    });

    Route::prefix('kdp-layouts')->name('kdp-layouts.')->group(function () {
        Route::get('/', [KdpLayoutController::class, 'index'])->name('index');
        Route::get('{slug}/details', [KdpLayoutController::class, 'details'])->name('details');
    });

    Route::prefix('ai-prompts')->name('ai-prompts.')->group(function () {
        Route::get('/', [AiPromptController::class, 'index'])->name('index');
        Route::post('save', [AiPromptController::class, 'save'])->name('save');

        Route::prefix('{slug}')->group(function () {
            Route::get('edit', [AiPromptController::class, 'edit'])->name('edit');
            Route::get('details', [AiPromptController::class, 'details'])->name('details');
            Route::patch('update', [AiPromptController::class, 'update'])->name('update');
        });
    });

    Route::prefix('ai-brain-output-types')->name('ai-brain-output-types.')->group(function () {
        Route::get('/', [AiBrainOutputTypeController::class, 'index'])->name('index');
        Route::get('{slug}/details', [AiBrainOutputTypeController::class, 'details'])->name('details');
    });

    Route::prefix('document-styles')->name('document-styles.')->group(function () {
        Route::get('/', [DocumentStyleController::class, 'index'])->name('index');
        Route::get('{slug}/details', [DocumentStyleController::class, 'details'])->name('details');
    });

    Route::prefix('users')->name('users.')->middleware(['is.super.admin'])->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('create', [UserController::class, 'create'])->name('create');
        Route::post('save', [UserController::class, 'save'])->name('save');

        Route::prefix('{slug}')->group(function () {
            Route::get('edit', [UserController::class, 'edit'])->name('edit');
            Route::get('details', [UserController::class, 'details'])->name('details');
            Route::patch('update', [UserController::class, 'update'])->name('update');
            Route::delete('delete', [UserController::class, 'delete'])->name('delete');
            Route::patch('active', [UserController::class, 'active'])->name('active');
            Route::patch('inactive', [UserController::class, 'inactive'])->name('inactive');
        });
    });

    Route::prefix('novels')->name('novels.')->group(function () {
        Route::get('/', [NovelController::class, 'index'])->name('index');
        Route::get('create', [NovelController::class, 'create'])->name('create');

        Route::prefix('create')->name('create.')->group(function () {
            Route::post('foundation', [NovelController::class, 'createStep1Foundation'])->name('foundation');
        });

        Route::prefix('{slug}')->group(function () {
            Route::get('edit', [NovelController::class, 'edit'])->name('edit');

            Route::prefix('generate')->name('generate.')->group(function () {
                Route::patch('foundation', [NovelController::class, 'generateStep1Foundation'])->name('foundation');
                Route::patch('characters', [NovelController::class, 'generateStep2Characters'])->name('characters');
                Route::patch('world-bible', [NovelController::class, 'generateStep3WorldBible'])->name('world-bible');
                Route::patch('locations', [NovelController::class, 'generateStep4Locations'])->name('locations');
                Route::patch('factions', [NovelController::class, 'generateStep5Factions'])->name('factions');
                Route::patch('creature', [NovelController::class, 'generateStep6Creatures'])->name('creature');
                Route::patch('system', [NovelController::class, 'generateStep7Systems'])->name('system');
                Route::patch('timeline', [NovelController::class, 'generateStep8Timeline'])->name('timeline');
                Route::patch('story-structure', [NovelController::class, 'generateStep9StoryStructure'])->name('story-structure');
                Route::patch('twists-and-foreshadowing', [NovelController::class, 'generateStep10TwistsAndForeshadowing'])->name('twists-and-foreshadowing');
                Route::patch('scene-planner', [NovelController::class, 'generateStep11ScenePlans'])->name('scene-planner');
                Route::patch('dialogue-planner', [NovelController::class, 'generateStep12DialoguePlans'])->name('dialogue-planner');
                Route::patch('chapter-planner', [NovelController::class, 'generateStep13ChapterPlan'])->name('chapter-planner');
                Route::patch('page-planner', [NovelController::class, 'generateStep14PagePlan'])->name('page-planner');
                Route::patch('chapter-summaries', [NovelController::class, 'generateStep15_1ChapterSummaries'])->name('chapter-summaries');
                Route::patch('chapter-content', [NovelController::class, 'generateStep15_2ChapterContent'])->name('chapter-content');
            });

            Route::patch('review', [NovelController::class, 'reviewNovel'])->name('review');

            Route::delete('delete', [NovelController::class, 'delete'])->name('delete');
        });
    });

    Route::prefix('activity-logs')->name('activity-logs.')->group(function () {
        Route::get('index', [ActivityLogController::class, 'index'])->name('index');

        Route::get('{modelSlug}/show-all/{recordSlug}', [ActivityLogController::class, 'indexForModel'])->name('show-all');

        Route::prefix('{slug}')->group(function () {
            Route::get('details', [ActivityLogController::class, 'details'])->name('details');
            Route::delete('delete', [ActivityLogController::class, 'delete'])->name('delete');
        });
    });

    Route::prefix('queue-monitor')->name('queue-monitor.')->middleware(['is.super.admin'])->group(function () {
        Route::get('/', ShowQueueMonitorController::class)->name('index');
    });
});

Route::get('/', function () {
    return redirect()->route('home');
});

Route::get('home', [PageController::class, 'home'])->name('home');

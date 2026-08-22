<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\EmailSettingsController;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\HomepageController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public site
|--------------------------------------------------------------------------
*/

Route::get('/', [PageController::class, 'home'])->name('home');

Route::get('/blog', [PostController::class, 'index'])->name('posts.index');

Route::get('/contact', [EnquiryController::class, 'create'])->name('contact');
Route::post('/contact', [EnquiryController::class, 'store'])->name('contact.store');

/*
|--------------------------------------------------------------------------
| Guest authentication (whitelist only — no open registration)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');

    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Members' area — any signed-in, active account
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'active'])->prefix('members')->name('members.')->group(function () {
    Route::get('/', [MemberController::class, 'dashboard'])->name('dashboard');

    Route::get('/profile', [MemberController::class, 'editProfile'])->name('profile');
    Route::put('/profile', [MemberController::class, 'updateProfile'])->name('profile.update');
    Route::put('/password', [MemberController::class, 'updatePassword'])->name('password.update');

    // Posts: members manage their own, admins manage everybody's.
    Route::get('/posts', [PostController::class, 'manage'])->name('posts.index');
    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');

    // Images. Uploading returns JSON so the editor can insert a photograph
    // without the member losing what they have already typed.
    Route::get('/images', [MediaController::class, 'index'])->name('media.index');
    Route::post('/images', [MediaController::class, 'store'])->name('media.store');
    Route::put('/images/{medium}', [MediaController::class, 'update'])->name('media.update');
    Route::delete('/images/{medium}', [MediaController::class, 'destroy'])->name('media.destroy');

    /*
    |----------------------------------------------------------------------
    | Administrators only
    |----------------------------------------------------------------------
    */
    Route::middleware('admin')->group(function () {
        // Homepage position
        Route::get('/homepage', [HomepageController::class, 'edit'])->name('homepage.edit');
        Route::put('/homepage', [HomepageController::class, 'update'])->name('homepage.update');
        Route::delete('/homepage', [HomepageController::class, 'reset'])->name('homepage.reset');

        // Automated emails
        Route::get('/emails', [EmailSettingsController::class, 'edit'])->name('emails.edit');
        Route::post('/emails/{template}', [EmailSettingsController::class, 'handle'])->name('emails.handle');

        // Whitelist and accounts
        Route::get('/whitelist', [MemberController::class, 'whitelist'])->name('whitelist.index');
        Route::post('/whitelist', [MemberController::class, 'storeWhitelist'])->name('whitelist.store');
        Route::post('/whitelist/{entry}/invite', [MemberController::class, 'regenerateInvite'])
            ->name('whitelist.invite');
        Route::delete('/whitelist/{entry}', [MemberController::class, 'destroyWhitelist'])
            ->name('whitelist.destroy');
        Route::put('/users/{user}', [MemberController::class, 'updateUser'])->name('users.update');

        // Contact form inbox
        Route::get('/enquiries', [EnquiryController::class, 'index'])->name('enquiries.index');
        Route::get('/enquiries/{enquiry}', [EnquiryController::class, 'show'])->name('enquiries.show');
        Route::put('/enquiries/{enquiry}', [EnquiryController::class, 'update'])->name('enquiries.update');
        Route::delete('/enquiries/{enquiry}', [EnquiryController::class, 'destroy'])
            ->name('enquiries.destroy');

        // Standing pages
        Route::get('/pages', [PageController::class, 'index'])->name('pages.index');
        Route::get('/pages/create', [PageController::class, 'create'])->name('pages.create');
        Route::post('/pages', [PageController::class, 'store'])->name('pages.store');
        Route::get('/pages/{page}/edit', [PageController::class, 'edit'])->name('pages.edit');
        Route::put('/pages/{page}', [PageController::class, 'update'])->name('pages.update');
        Route::delete('/pages/{page}', [PageController::class, 'destroy'])->name('pages.destroy');
    });
});

/*
|--------------------------------------------------------------------------
| Slug routes — declared last so they never shadow the fixed paths above
|--------------------------------------------------------------------------
*/

Route::get('/blog/{post:slug}', [PostController::class, 'show'])->name('posts.show');
Route::get('/{page:slug}', [PageController::class, 'show'])->name('pages.show');

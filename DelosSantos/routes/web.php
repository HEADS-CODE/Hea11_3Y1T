<?php

use App\Http\Controllers\TaskController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\Auth\AccountStateController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AdminProfileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\MemberRequestController;
use App\Http\Controllers\AdminRequestController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\WeatherController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AdminTaskController;
use App\Http\Controllers\TaskExtensionRequestController;
use App\Http\Controllers\MemberPortalController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login/member');

Route::get('/login/member', [LoginController::class, 'showMemberLogin'])->name('login.member');
Route::post('/login/member', [LoginController::class, 'loginMember'])->middleware('throttle:auth-login')->name('login.member.submit');
Route::get('/login/admin', [LoginController::class, 'showAdminLogin'])->name('login.admin');
Route::post('/login/admin', [LoginController::class, 'loginAdmin'])->middleware('throttle:auth-login')->name('login.admin.submit');
Route::get('/register/member', [RegistrationController::class, 'showMemberRegistration'])->name('register.member');
Route::post('/register/member', [RegistrationController::class, 'registerMember'])->middleware('throttle:auth-register')->name('register.member.submit');
Route::get('/register/admin', [RegistrationController::class, 'showAdminRegistration'])->name('register.admin');
Route::post('/register/admin', [RegistrationController::class, 'registerAdmin'])->middleware('throttle:auth-register')->name('register.admin.submit');
Route::get('/verify-email/{user}/{token}', [VerificationController::class, 'verify'])->name('verification.verify');
Route::get('/forgot-password/member', fn () => app(PasswordController::class)->showForgot('member'))->name('password.request.member');
Route::post('/forgot-password/member', fn (Illuminate\Http\Request $request) => app(PasswordController::class)->sendResetLink($request, 'member'))->middleware('throttle:password-reset')->name('password.email.member');
Route::get('/forgot-password/admin', fn () => app(PasswordController::class)->showForgot('admin'))->name('password.request.admin');
Route::post('/forgot-password/admin', fn (Illuminate\Http\Request $request) => app(PasswordController::class)->sendResetLink($request, 'admin'))->middleware('throttle:password-reset')->name('password.email.admin');
Route::get('/reset-password/{role}/{token}', fn (Illuminate\Http\Request $request, string $role, string $token) => app(PasswordController::class)->showReset($request, $role, $token))->middleware('throttle:password-reset')->name('password.reset');
Route::post('/reset-password/member', fn (Illuminate\Http\Request $request) => app(PasswordController::class)->reset($request, 'member'))->middleware('throttle:password-reset')->name('password.reset.member');
Route::post('/reset-password/admin', fn (Illuminate\Http\Request $request) => app(PasswordController::class)->reset($request, 'admin'))->middleware('throttle:password-reset')->name('password.reset.admin');
Route::get('/member-requests/{memberRequest}/verify-email/{token}', [MemberRequestController::class, 'verifyEmailChange'])->name('member.requests.verify-email');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/verify-email', [AccountStateController::class, 'verificationNotice'])->name('verification.notice');
    Route::get('/approval-pending', [AccountStateController::class, 'approvalPending'])->name('approval.pending');
    Route::get('/account/inactive', [AccountStateController::class, 'inactiveNotice'])->name('account.inactive');
        Route::post('/verify-email/resend', [VerificationController::class, 'resend'])->middleware('throttle:verification-resend')->name('verification.resend');
    Route::middleware('account.ready')->group(function () {
        Route::get('/weather', [WeatherController::class, 'show'])->middleware('throttle:30,1')->name('weather.show');
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    });

    Route::middleware(['account.ready', 'role:admin'])->group(function () {
        Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/admin/profile', [AdminProfileController::class, 'edit'])->name('admin.profile.edit');
        Route::put('/admin/profile', [AdminProfileController::class, 'update'])->name('admin.profile.update');
        Route::resource('members', MemberController::class);
        Route::get('/members/{member}/documents', [MemberController::class, 'documents'])->name('members.documents.index');
        Route::get('/members/{member}/documents/{document}/view', [MemberController::class, 'viewDocument'])->name('members.documents.view');
        Route::post('/members/{member}/archive', [MemberController::class, 'archive'])->name('members.archive');
        Route::post('/members/{member}/restore', [MemberController::class, 'restore'])->name('members.restore');
        Route::post('/members/{member}/toggle-status', [MemberController::class, 'toggleStatus'])->name('members.toggle-status');
        Route::resource('membership-categories', CategoryController::class)->except('show')->names('categories');
        Route::resource('membership-levels', LevelController::class)->except('show')->names('levels');
        Route::get('/admin/approvals', [ApprovalController::class, 'index'])->name('admin.approvals.index');
        Route::post('/admin/approvals/{user}/approve', [ApprovalController::class, 'approve'])->name('admin.approvals.approve');
        Route::post('/admin/approvals/{user}/reject', [ApprovalController::class, 'reject'])->name('admin.approvals.reject');
        Route::get('/admin/transactions', [TransactionController::class, 'index'])->name('admin.transactions.index');
        Route::post('/admin/transactions/{transaction}/approve', [TransactionController::class, 'approve'])->name('admin.transactions.approve');
        Route::post('/admin/transactions/{transaction}/cancel', [TransactionController::class, 'cancel'])->name('admin.transactions.cancel');
        Route::get('/admin/transactions/{transaction}/payment', [PaymentController::class, 'adminShow'])->name('admin.transactions.payment');
        Route::post('/admin/transactions/{transaction}/payment', [PaymentController::class, 'adminRecord'])->name('admin.transactions.payment.store');
        Route::get('/admin/transactions/{transaction}/receipt', [TransactionController::class, 'receipt'])->name('admin.transactions.receipt');
        Route::get('/admin/members/{member}/transactions', [TransactionController::class, 'historyForMember'])->name('admin.members.transactions');
        Route::get('/admin/requests', [AdminRequestController::class, 'index'])->name('admin.requests.index');
        Route::get('/admin/requests/{memberRequest}', [AdminRequestController::class, 'show'])->name('admin.requests.show');
        Route::post('/admin/requests/{memberRequest}/approve', [AdminRequestController::class, 'approve'])->name('admin.requests.approve');
        Route::post('/admin/requests/{memberRequest}/reject', [AdminRequestController::class, 'reject'])->name('admin.requests.reject');
        Route::get('/admin/requests/{memberRequest}/documents/{document}', [AdminRequestController::class, 'downloadDocument'])->name('admin.requests.documents.download');
        Route::get('/admin/activity', [ActivityController::class, 'index'])->name('admin.activity.index');
        Route::get('/admin/reports', [ReportController::class, 'index'])->name('admin.reports.index');
        Route::get('/admin/reports/export', [ReportController::class, 'export'])->name('admin.reports.export');
        Route::get('/admin/tasks', [AdminTaskController::class, 'index'])->name('admin.tasks.index');
        Route::get('/admin/tasks/create', [AdminTaskController::class, 'create'])->name('admin.tasks.create');
        Route::post('/admin/tasks', [AdminTaskController::class, 'store'])->name('admin.tasks.store');
        Route::get('/admin/tasks/{task}/edit', [AdminTaskController::class, 'edit'])->name('admin.tasks.edit');
        Route::put('/admin/tasks/{task}', [AdminTaskController::class, 'update'])->name('admin.tasks.update');
        Route::post('/admin/tasks/{task}/archive', [AdminTaskController::class, 'archive'])->name('admin.tasks.archive');
        Route::post('/admin/task-extension-requests/{taskExtensionRequest}/approve', [TaskExtensionRequestController::class, 'approve'])->name('admin.task-extension-requests.approve');
        Route::post('/admin/task-extension-requests/{taskExtensionRequest}/reject', [TaskExtensionRequestController::class, 'reject'])->name('admin.task-extension-requests.reject');
    });

    Route::middleware(['account.ready', 'role:member'])->group(function () {
        Route::get('/portal', fn () => redirect()->route('tasks.index'))->name('portal');
        Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
        Route::get('/my-tasks', [TaskController::class, 'myTasks'])->name('member.tasks');
        Route::patch('/tasks/{task}/complete', [TaskController::class, 'complete'])->name('tasks.complete');
        Route::post('/tasks/{task}/extension-requests', [TaskExtensionRequestController::class, 'store'])->name('tasks.extension-requests.store');
        Route::get('/my-membership', [MemberPortalController::class, 'membership'])->name('member.membership');
        Route::get('/membership/categories', [MemberPortalController::class, 'categories'])->name('member.categories');
        Route::get('/membership/levels', [MemberPortalController::class, 'levels'])->name('member.levels');
        Route::get('/my-transactions', [TransactionController::class, 'historyForMember'])->name('member.transactions');
        Route::get('/my-requests', [MemberRequestController::class, 'index'])->name('member.requests.index');
        Route::get('/my-transactions/{transaction}/receipt', [TransactionController::class, 'receipt'])->name('member.transactions.receipt');
        Route::get('/membership/renew', [TransactionController::class, 'showRenewal'])->name('member.renew');
        Route::post('/membership/renew', [TransactionController::class, 'renew'])->name('member.renew.store');
        Route::get('/membership/level-change', [TransactionController::class, 'showLevelChange'])->name('member.level-change');
        Route::post('/membership/level-change', [TransactionController::class, 'levelChange'])->name('member.level-change.store');
        Route::get('/membership/category-change', [MemberRequestController::class, 'categoryChange'])->name('member.category-change');
        Route::post('/membership/category-change', [MemberRequestController::class, 'storeCategoryChange'])->name('member.category-change.store');
        Route::get('/membership/profile-change', [MemberRequestController::class, 'profileChange'])->name('member.profile-change');
        Route::post('/membership/profile-change', [MemberRequestController::class, 'storeProfileChange'])->name('member.profile-change.store');
    });

    Route::middleware('role:member')->group(function () {
        Route::post('/account/reactivation', [MemberRequestController::class, 'storeReactivation'])->name('member.requests.reactivation');
        Route::get('/membership/payment/{transaction}', [PaymentController::class, 'memberShow'])->name('member.payment');
        Route::post('/membership/payment/{transaction}', [PaymentController::class, 'memberPay'])->name('member.payment.store');
        Route::get('/membership/payment', fn () => redirect()->route('member.transactions'))->name('member.payment.latest');
    });
});

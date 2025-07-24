<?php
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SuperAdmin\AdminController;
use App\Http\Controllers\SuperAdmin\PlanController;
use App\Http\Controllers\WorkspacesController;
use App\Http\Controllers\CardsController;
use App\Http\Controllers\TodosController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\MeetingsController;
use App\Http\Controllers\MessageBoardController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FilesController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\AIController;
use App\Http\Controllers\SubscriptionPlan;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdmin\HomeController as SuperAdminHomeController;

Route::get('/', function () {
    return view('login');
});

Route::post('/users/authenticate', [UserController::class, 'authenticate'])->name('users.authenticate');
Route::middleware(['web'])->group(function () {
    Route::get('/home', [DashboardController::class, 'index'])->name('home.index');
});

Route::post('/ask-chatgpt', [AIController::class, 'ask'])->name('ask.chatgpt');
Route::post('/grammar-check', [AIController::class, 'checkGrammar'])->name('grammar.check'); 

Route::get('/projects', [WorkspacesController::class, 'index'])->name('workspaces.index');
Route::post('/projects/store', [WorkspacesController::class, 'store'])->name('workspaces.store');
Route::get('/projects/switch/{id}', [WorkspacesController::class, 'switch'])->name('workspaces.switch');
Route::put('/projects/{id}', [WorkspacesController::class, 'update'])->name('workspaces.update');
Route::delete('/projects/destroy/{id}', [WorkspacesController::class, 'destroy'])->name('workspaces.destroy');

Route::get('/cards', [CardsController::class, 'index'])->name('cards.index');
Route::put('/cards/{note}/update-note-status',[CardsController::class, 'updateNoteStatus'])->name('cards.update-status');
Route::post('/cards/store', [CardsController::class, 'store'])->name('cards.store');
Route::post('/cards/update', [CardsController::class, 'update'])->name('cards.update');
Route::delete('/cards/destroy/{id}', [CardsController::class, 'destroy'])->name('cards.destroy');

Route::get('/todos', [TodosController::class, 'index'])->name('todos.index');
Route::get('/todos/create', [TodosController::class, 'create'])->name('todos.create');
Route::post('/todos/store', [TodosController::class, 'store'])->name('todos.store');
Route::post('/todos/update/', [TodosController::class, 'update'])->name('todos.update');
Route::delete('/todos/destroy/{id}', [TodosController::class, 'destroy'])->name('todos.destroy');
Route::put('/todos/{id}/complete', [TodosController::class, 'markAsDone'])->name('todos.markAsDone');

Route::get('/status/manage', [StatusController::class, 'index'])->name('status.index');
Route::post('/status/store', [StatusController::class, 'store'])->name('status.store');
Route::post('/status/update', [StatusController::class, 'update'])->name('status.update');
Route::delete('/status/destroy/{id}', [StatusController::class, 'destroy'])->name('status.destroy');

Route::get('/meetings', [MeetingsController::class, 'index'])->name('meetings.index');
Route::post('/meetings/{id}/save-recording', [MeetingsController::class, 'saveRecording'])->name('meetings.recording.save');
Route::post('/meetings', [MeetingsController::class, 'store'])->name('meetings.store');
Route::get('/recording', [MeetingsController::class, 'recordingIndex'])->name('recordings.index');
Route::post('/save-recording', [MeetingController::class, 'saveRecording'])->name('recording.save');
Route::get('/recordings/fetch', [MeetingsController::class, 'fetchFromDaily'])->name('recordings.storeBlob');
Route::get('/recording/{id}/stream', [MeetingsController::class, 'stream'])->name('recordings.stream');
Route::get('/recording/{id}/download', [MeetingsController::class, 'download'])->name('recordings.download');

Route::get('/message-board', [MessageBoardController::class, 'index'])->name('message-board.index');
Route::get('/message-board', [MessageBoardController::class, 'index'])->name('message-board.index');
Route::post('/message-board', [MessageBoardController::class, 'store'])->name('message-board.store');
Route::delete('/message-board/{type}/{id}', [MessageBoardController::class, 'destroy'])->name('message-board.destroy');
Route::get('/message-board/{message}/edit', [MessageBoardController::class, 'edit'])->name('message-board.edit');
Route::put('/message-board/{message}', [MessageBoardController::class, 'update'])->name('message-board.update');
Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');
Route::put('/comments/{id}', [CommentController::class, 'update'])->name('comments.update');
Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

Route::get('/superadmin/home', [SuperAdminHomeController::class, 'index'])->name('superadmin.panel');
Route::get('home/getAdminMonthlyCount', [SuperAdminHomeController::class, 'getAdminMonthlyCount'])->name('chart.admin_monthly_count');
Route::get('home/getActiveSubscriptionPerPlan', [SuperAdminHomeController::class, 'getActiveSubscriptionsPerPlan'])->name('chart.activeSubscriptionPerPlan');
Route::get('/admins', [AdminController::class,'index'])->name('admins.index');
Route::post('/admins/store', [AdminController::class, 'store'])->name('admins.store');
Route::post('/admins/update/{id}', [AdminController::class, 'update'])->name('admins.update');
Route::delete('/admins/destroy/{id}', [AdminController::class,'destroy'])->name('admins.destroy');


Route::get('/plans', [PlanController::class,'index'])->name('plans.index');
Route::post('/plans/store', [PlanController::class, 'store'])->name('plans.store');
Route::put('/plans/update/{id}', [PlanController::class, 'update'])->name('plans.update');
Route::delete('/plans/destroy/{id}', [PlanController::class, 'destroy'])->name('plans.destroy');

Route::get('/files', [FilesController::class, 'index'])->name('files.index');
Route::post('/files/upload', [FilesController::class, 'upload'])->name('files.upload');
Route::get('/files/view/{id}', [FilesController::class, 'view'])->name('files.view');
Route::put('/files/{id}', [FilesController::class, 'update'])->name('files.update');
Route::delete('/files/{id}', [FilesController::class, 'delete'])->name('files.delete');
Route::get('/files/display', [FilesController::class, 'displayFolder'])->name('files.display');
Route::get('/files/no-folder', [FilesController::class, 'displayFile'])->name('files.noFolder');
Route::put('/{folder}', [FilesController::class, 'updateFolder'])->name('folders.update');
Route::delete('/{folder}', [FilesController::class, 'destroyFolder'])->name('folders.destroy');
Route::get('/files/download/{id}', [FilesController::class, 'download'])->name('files.download');

Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
Route::post('/chat/send', [ChatController::class, 'send'])->name('chat.send');
Route::get('/chat/messages/{to_id}', [ChatController::class, 'getMessages'])->name('chat.messages');
Route::put('/chat/update/{id}', [ChatController::class, 'update'])->name('chat.update');
Route::delete('/chat/delete/{id}', [ChatController::class, 'delete'])->name('chat.delete');
Route::get('/chat/attachment/{message}', [ChatController::class, 'downloadAttachment'])->name('chat.attachment');
Route::post('/chat/reply/{message}', [ChatController::class, 'storeReply'])->middleware('auth') ->name('chat.reply');
Route::post('/chat/group/store', [ChatController::class, 'storeGroup'])->name('chat.group.store');
Route::post('/chat/forward', [ChatController::class, 'forward'])->name('chat.forward');
Route::get('/chat/reply-attachment/{id}', [ChatController::class, 'showReplyAttachment'])->name('chat.reply.attachment');
Route::get('/chat/reply-attachment/{message}', [ChatController::class, 'downloadReplyAttachment'])->name('chat.download.attachment');
Route::put('/groups/{group}', [ChatController::class, 'updateGroup'])->name('groups.update');  
Route::delete('/groups/{group}', [ChatController::class, 'destroyGroup'])->name('groups.destroy');

Route::get('/users', [UserController::class, 'index'])->name('users.index');
Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
Route::post('/users/store', [UserController::class, 'store'])->name('users.store');
Route::get('/users/edit/{id}', [UserController::class, 'edit_user'])->name('users.edit');
Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
Route::delete('/users/delete_user/{user}', [UserController::class, 'delete_user'])->name('users.delete_user');
Route::get('/users/list', [UserController::class, 'list'])->name('users.list');

Route::get('/subscription-plan', [SubscriptionPlan::class, 'index'])->name('subscription-plan.index');
Route::get('/subscription-plan/buy-plan', [SubscriptionPlan::class, 'create'])->name('subscription-plan.buy-plan');
Route::post('/subscription-plan/store', [SubscriptionPlan::class, 'store'])->name('subscription-plan.store');
Route::get('/subscription-plan/checkout/{id}', [SubscriptionPlan::class, 'show'])->name('subscription-plan.checkout');
Route::delete('/subscriptions/{id}/quit', [SubscriptionPlan::class, 'quit'])->name('subscription.quit');

Route::get('/events', [CalendarController::class, 'index'])->name('calendar.index');
Route::get('/events/events', [CalendarController::class, 'fetchEvents'])->name('calendar.events');
Route::post('/events/assign', [CalendarController::class, 'store'])->name('events.store');
Route::put('/events/update/{id}', [CalendarController::class, 'update'])->name('event.update');
Route::delete('/events/delete/{id}', [CalendarController::class, 'destroy'])->name('event.delete');
Route::get('/events/google-events', [CalendarController::class, 'getGoogleEvents'])->name('calendar.calendar');
Route::put('/events/{id}', [CalendarController::class, 'update'])->name('events.update');
Route::delete('/events/{id}', [CalendarController::class, 'destroy'])->name('events.destroy');
Route::post('/logout', [UserController::class, 'logout'])->name('logout');
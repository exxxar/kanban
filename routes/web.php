<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Services\TaskApiTester;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// routes/web.php

use App\Http\Controllers\BoardController;

Route::get('/board/{uuid}', [BoardController::class, 'show'])
    ->name('board.show');

Route::get("/test", function (){
    $type = "base";
    $count = 5;

    $tester = new TaskApiTester();

    for ($i = 1; $i <= $count; $i++) {

        $result = match ($type) {
            'base' => $tester->createBase(),
            'user' => $tester->createUser(),
            'order' => $tester->createOrder(),
            'text' => $tester->createText(),
            'finance' => $tester->createFinance(),
            'all' => $tester->createAll(),
            default => ""
        };

        dd($result);
    }
});


Route::get('/', [HomeController::class, 'index']);


Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

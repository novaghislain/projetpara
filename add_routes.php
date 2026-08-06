<?php
$content = file_get_contents('routes/gel.php');
if (strpos($content, "Route::get('/invitations'") === false) {
    $to_insert = "
    // -- Invitations Secrétaire ------------------------
    Route::get('/invitations', [\App\Http\Controllers\GelSecretary\InvitationController::class, 'index'])->name('gel-secretary.invitations.index');
    Route::post('/invitations/{id}/accept', [\App\Http\Controllers\GelSecretary\InvitationController::class, 'accept'])->name('gel-secretary.invitations.accept');
    Route::post('/invitations/{id}/reject', [\App\Http\Controllers\GelSecretary\InvitationController::class, 'reject'])->name('gel-secretary.invitations.reject');
";
    $content = preg_replace('/(Route::get\(\'\/dashboard\',)/', $to_insert . "\n    $1", $content);
    file_put_contents('routes/gel.php', $content);
    echo "Routes inserted\n";
} else {
    echo "Routes already exist\n";
}

<?php

Route::group(['middleware' => 'web', 'prefix' => \Helper::getSubdirectory(), 'namespace' => 'Modules\Eventbrite\Http\Controllers'], function () {
    Route::get('/eventbrite/settings/{mailbox}', 'EventbriteController@index')->name('eventbrite.settings');
    Route::put('/eventbrite/settings/{mailbox}', 'EventbriteController@update')->name('eventbrite.settings.update');
    Route::delete('/eventbrite/settings/{mailbox}', 'EventbriteController@destroy')->name('eventbrite.settings.destroy');
    Route::delete('/eventbrite/refresh/{mailbox}/{customer}', 'EventbriteController@refresh')->name('eventbrite.refresh');
});

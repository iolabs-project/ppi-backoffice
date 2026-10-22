<?php

// The dashboard is behind auth: guests are sent to the login page
test('guests are redirected to login', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('login'));
});

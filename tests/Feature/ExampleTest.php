<?php

it('redirects guest to login for home', function () {
    $response = $this->get('/');

    $response->assertRedirect('/login');
});

<?php

test('the root redirects unauthenticated users to login', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('admin.login'));
});

test('admin login page loads successfully', function () {
    $response = $this->get(route('admin.login'));

    $response->assertStatus(200);
});

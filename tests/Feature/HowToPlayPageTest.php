<?php

use Inertia\Testing\AssertableInertia as Assert;

test('guest can access how to play page', function () {
    $this->get('/how-to-play')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('HowToPlay/Index')
            ->has('steps', 5)
            ->has('sections')
            ->has('faqs')
            ->has('support')
            ->where('support.title', 'Fair Play and Delivery Tips'));
});

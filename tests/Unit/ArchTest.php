<?php

arch('no insecure php functions (eval, exec, md5, unserialize, rand...)')
    ->preset()
    ->security();

arch('no debugging helpers are left in the application code')
    ->expect(['dd', 'ddd', 'dump', 'var_dump', 'ray'])
    ->not->toBeUsed();

arch('application code reads config() instead of env(), so cached config keeps working')
    ->expect('App')
    ->not->toUse('env');

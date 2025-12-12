<?php
require_once __DIR__ . '/../app/helpers.php';
var_dump('cache_set', cache_set('x', ['a'=>1], 2));
var_dump('cache_get', cache_get('x'));
var_dump('cache_delete', cache_delete('x'));
var_dump('cache_get_after_delete', cache_get('x'));

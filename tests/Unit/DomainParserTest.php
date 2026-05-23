<?php

use OTGH\LaravelWhois\Domain\DomainParser;

it('parses a simple domain', function () {
    $info = DomainParser::parse('example.com');

    expect($info->registrable)->toBe('example.com');
    expect($info->extension)->toBe('com');
});

it('parses subdomain and extracts registrable domain', function () {
    $info = DomainParser::parse('www.blog.example.co.uk');

    expect($info->registrable)->toBe('example.co.uk');
    expect($info->extension)->toBe('co.uk');
});

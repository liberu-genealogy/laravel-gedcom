<?php

namespace Asdfx\LaravelGedcom\Concerns;

trait UuidModel
{
    public function getIncrementing(): bool
    {
        return false;
    }

    public function getKeyType(): string
    {
        return 'string';
    }

    public function getKeyName(): string
    {
        return 'uid';
    }
}
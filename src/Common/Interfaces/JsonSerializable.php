<?php

namespace SugoiCloud\Common\Interfaces;

interface JsonSerializable
{
    public function toJson(): string;
}
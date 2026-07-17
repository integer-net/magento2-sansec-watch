<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Model;

interface GetApiUrlInterface
{
    /**
     * Returns the API Url with `{id}` as the placeholder for the ID
     *
     * @return string
     */
    public function getApiUrl(): string;
}

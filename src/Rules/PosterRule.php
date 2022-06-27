<?php

namespace EscolaLms\Cart\Rules;

use EscolaLms\Cart\Enums\ConstantEnum;
use EscolaLms\Files\Rules\FileOrStringRule;

class PosterRule extends FileOrStringRule
{
    public function __construct($productId)
    {
        $prefixPath = ConstantEnum::DIRECTORY . '/' . $productId;

        parent::__construct(['image'], $prefixPath);
    }
}
<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;

class CloudinaryUpload extends Field
{
    protected string $view = 'filament.forms.cloudinary-upload';

    public function multiple(bool $condition = true): static
    {
        $this->isMultiple = $condition;
        return $this;
    }
}
<?php

namespace Motekar\FlexField;

use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Support\Str;

trait HasFlexField
{
    public function flexField($name): HasOneOrMany
    {
        if (empty($flexFieldConfig = config("flexfield.$name"))) {
            throw new \Exception("FlexField $name not available");
        }

        $flexClassName = Str::studly("{$this->getTable()}_{$name}_flex");

        if ($flexFieldConfig['isMany']) {
            return $this->hasMany($flexClassName);
        }

        return $this->hasOne($flexClassName);
    }
}

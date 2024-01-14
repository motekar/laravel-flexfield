<?php

namespace Motekar\FlexField\Tests\TestClasses;
;
use Illuminate\Database\Eloquent\Model;
use Motekar\FlexField\HasFlexField;

class TestModel extends Model
{
    use HasFlexField;

    protected $table = 'test_models';
    protected $fillable = ['name'];

    public $timestamps = false;
}

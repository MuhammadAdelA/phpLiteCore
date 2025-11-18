<?php

namespace App\Models;

use PhpLiteCore\Database\Model\BaseModel;

class Post extends BaseModel
{
    /**
     * The validation rules for a post.
     *
     * @var array
     */
    protected array $rules = [
        'title' => 'required|min:5',
        'body' => 'required|min:10',
    ];

    // If your table name was different from 'posts', you would define it here:
    // protected string $table = 'my_custom_posts_table';
}

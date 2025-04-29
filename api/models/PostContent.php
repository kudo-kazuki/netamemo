<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostContent extends Model
{
    protected $table = 'post_contents';
    public $timestamps = true;

    protected $fillable = ['post_id', 'heading_id', 'content'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function heading(): BelongsTo
    {
        return $this->belongsTo(TemplateHeading::class, 'heading_id');
    }
}

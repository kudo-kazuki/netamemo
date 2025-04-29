<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateHeading extends Model
{
    protected $table = 'template_headings';
    public $timestamps = true;

    protected $fillable = ['template_id', 'heading_order', 'heading_title'];

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }
}

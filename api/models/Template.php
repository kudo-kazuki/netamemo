<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Models\TemplateHeading;

class Template extends Model
{
    protected $table = 'templates';
    public $timestamps = true;

    protected $fillable = ['user_id', 'title', 'visibility'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function headings(): HasMany
    {
        return $this->hasMany(TemplateHeading::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}

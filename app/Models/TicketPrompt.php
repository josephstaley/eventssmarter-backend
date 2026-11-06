<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class TicketPrompt extends Model
{
    use SoftDeletes;
    protected $table = 'ticket_prompts';
    protected $fillable = ['ticket_id', 'prompt_json'];
    function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }
    function getPromptJsonAttribute($value){
        return json_decode($value);
    }
}

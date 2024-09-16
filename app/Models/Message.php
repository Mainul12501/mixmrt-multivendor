<?php

namespace App\Models;

use App\CentralLogics\Helpers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\DB;

class Message extends Model
{
    use HasFactory;

    protected $casts = [
        'conversation_id' => 'integer',
        'sender_id' => 'integer',
        'is_seen' => 'integer'
    ];

    protected $appends = ['file_full_url'];

    public function sender()
    {
        return $this->belongsTo(UserInfo::class, 'sender_id');
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function getFileFullUrlAttribute(){
        $images = [];
        $value = is_array($this->file)?$this->file:json_decode($this->file,true);
        if ($value){
            foreach ($value as $item){
                $item = is_array($item)?$item:(is_object($item) && get_class($item) == 'stdClass' ? json_decode(json_encode($item), true):['img' => $item, 'storage' => 'public']);
                if($item['storage']=='s3'){
                    $images[] = Helpers::s3_storage_link('conversation',$item['img']);
                }else{
                    $images[] = Helpers::local_storage_link('conversation',$item['img']);
                }
            }
        }

        return $images;
    }
}

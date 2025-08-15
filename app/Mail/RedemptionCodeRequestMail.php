<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RedemptionCodeRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $record;

    public function __construct($record)
    {
        $this->record = $record;
    }

    public function build()
    {
        $productsHtml = '';
        foreach ($this->record->items as $item) {
            $productsHtml .= "<li><strong>{$item->product->name}</strong></li>";
        }
    
        return $this->subject(trans('public.redemption_code_request'))
                    ->html(
                        "<p>" . trans('public.email') . ": <strong>{$this->record->user->email}</strong></p>" .
                        "<p>" . trans('public.product_request_redeem') . ":</p>" .
                        "<ul>" . $productsHtml . "</ul>"
                    );
    }
}

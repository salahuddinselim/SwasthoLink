<?php

namespace App\View\Components;

use App\Services\QrCodeService;
use Illuminate\View\Component;
use Illuminate\View\View;

class QrCode extends Component
{
    public string $svg;

    public function __construct(string $data, int $size = 160)
    {
        $this->svg = app(QrCodeService::class)->svg($data, $size);
    }

    public function render(): View
    {
        return view('components.qr-code');
    }
}

<?php

namespace App\Services;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;

/**
 * Renders lookup codes as scannable QR codes for the printed/SMS slip —
 * the "scan-instead-of-type" shortcut described in the offline pharmacy
 * flow. Uses the SVG writer specifically because this app runs on XAMPP
 * without the GD extension, which the PNG writer requires.
 */
class QrCodeService
{
    public function svg(string $data, int $size = 160): string
    {
        $qrCode = new QrCode(data: $data, size: $size, margin: 4);

        return (new SvgWriter())->write($qrCode)->getString();
    }
}

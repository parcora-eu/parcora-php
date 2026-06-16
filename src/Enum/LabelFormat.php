<?php

declare(strict_types=1);

namespace Parcora\Enum;

/** A label output format. */
enum LabelFormat: string
{
    case PdfA4 = 'pdf_a4';
    case PdfLabel = 'pdf_label';
    case Zpl = 'zpl';
}

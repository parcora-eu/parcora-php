<?php

declare(strict_types=1);

namespace Parcora\Enum;

/** A carrier supported by the platform. */
enum CarrierCode: string
{
    case Omniva = 'omniva';
    case Venipak = 'venipak';
    case Unisend = 'unisend';
    case DpdBaltic = 'dpd_baltic';
    case Bpost = 'bpost';
    case Itella = 'itella';
    case UberDirect = 'uber_direct';
    case Inpost = 'inpost';
    case Postnord = 'postnord';
    case Bring = 'bring';
    case DhlParcel = 'dhl_parcel';
    case Matkahuolto = 'matkahuolto';
    case Instabee = 'instabee';
    case LatvijasPasts = 'latvijas_pasts';
    case Gls = 'gls';
    case Helthjem = 'helthjem';
}

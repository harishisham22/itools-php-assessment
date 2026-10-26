<?php

namespace App;

enum State: string
{
    case JOHOR = "johor";
    case KEDAH = "kedah";
    case KELANTAN = "kelantan";
    case MELAKA = "melaka";
    case NEGERI_SEMBILAN = "negeri_sembilan";
    case PAHANG = "pahang";
    case PENANG = "penang";
    case PERAK = "perak";
    case PERLIS = "perlis";
    case SABAH = "sabah";
    case SARAWAK = "sarawak";
    case SELANGOR = "selangor";
    case TERENGGANU = "terengganu";

    // Federal Territories
    case KUALA_LUMPUR = "kuala_lumpur";
    case LABUAN = "labuan";
    case PUTRAJAYA = "putrajaya";
}
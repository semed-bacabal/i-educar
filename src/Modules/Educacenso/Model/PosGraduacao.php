<?php

namespace iEducar\Modules\Educacenso\Model;

class PosGraduacao
{
    public const ESPECIALIZACAO = 1;

    public const MESTRADO = 2;

    public const DOUTORADO = 3;

    public const NAO_POSSUI = 4;

    public static function getDescriptiveValues()
    {
        return [
            self::ESPECIALIZACAO => 'Especialização',
            self::MESTRADO => 'Mestrado',
            self::DOUTORADO => 'Doutorado',
        ];
    }
}

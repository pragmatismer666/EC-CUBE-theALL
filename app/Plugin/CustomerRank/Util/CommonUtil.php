<?php
/*
* Plugin Name : CustomerRank
*
* Copyright (C) BraTech Co., Ltd. All Rights Reserved.
* http://www.bratech.co.jp/
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Plugin\CustomerRank\Util;

class CommonUtil
{

    public static function roundByCalcRule($value, $calcRule)
    {
        switch ($calcRule) {
            // 四捨五入
            case \Eccube\Entity\Master\RoundingType::ROUND:
                $ret = round($value);
                break;
            // 切り捨て
            case \Eccube\Entity\Master\RoundingType::FLOOR:
                $ret = intval(bcmul($value,1,0));
                break;
            // 切り上げ
            case \Eccube\Entity\Master\RoundingType::CEIL:
                $ret = ceil($value);
                break;
            // デフォルト:切り上げ
            default:
                $ret = ceil($value);
                break;
        }

        return $ret;
    }
}

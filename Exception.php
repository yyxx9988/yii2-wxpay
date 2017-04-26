<?php

namespace yyxx9988\wxpay;
/**
 * Exception represents an exception that is caused during unifiedorder.
 */
class Exception extends \yii\base\Exception
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Wxpay Exception';
    }
}

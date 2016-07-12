<?php
class Tools extends ToolsCore
{
    public static function switchLanguage(Context $context = null)
    {
        if (isset($_SESSION['klarna_checkout'])) {
            unset($_SESSION['klarna_checkout']);
        }
        return parent::switchLanguage($context);
    }
    public static function setCurrency($cookie)
    {
        if (isset($_SESSION['klarna_checkout'])) {
            unset($_SESSION['klarna_checkout']);
        }
        return parent::setCurrency($cookie);
    }
}

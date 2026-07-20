<?php
namespace MalikK\Himmah\Core;

use MalikK\Himmah\Domain\PostTypes;
use MalikK\Himmah\Rest\ActivityController;
use MalikK\Himmah\Blocks\DashboardBlock;

class Plugin {

    public static function init() {
        // تسجيل أنواع المحتوى المخصص
        if (class_exists('MalikK\\Himmah\\Domain\\PostTypes')) {
            PostTypes::init();
        }

        // تسجيل مسارات الـ REST API
        if (class_exists('MalikK\\Himmah\\Rest\\ActivityController')) {
            ActivityController::init();
        }

        // تسجيل بلوك لوحة تحكم هِمّة
        if (class_exists('MalikK\\Himmah\\Blocks\\DashboardBlock')) {
            DashboardBlock::init();
        }
    }
}
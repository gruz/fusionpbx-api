<?php

namespace Infrastructure\Notification;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification as FacadesNotification;


class Notification extends FacadesNotification {

    private static function modelRefresh($model) {
        if ($model instanceof Model) {
            $model->refresh();
        }
        return $model;
    }

    public static function send($notifiables, $notification) {
        $notifiables = self::modelRefresh($notifiables);

        if ($notifiables instanceof Collection) {
            // $notifiables = collect($notifiables);
            $notifiables = $notifiables->map(function($item) {
                return self::modelRefresh($item);
            });
        } else if (is_array($notifiables)) {
            $notifiables = array_map('self::modelRefresh', $notifiables);
        }

        parent::send($notifiables, $notification);
    }
}
<?php

/**
 * @copyright Copyright (C) 2025 AIZAWA Hina
 * @license https://github.com/fetus-hina/stat.ink/blob/master/LICENSE MIT
 * @author AIZAWA Hina <hina@fetus.jp>
 */

declare(strict_types=1);

namespace app\actions\api\v3;

use app\components\web\Action;
use app\models\UnregisteredPlayer3;
use yii\web\NotFoundHttpException;

final class UnregisteredPlayerAction extends Action
{
    public function run(string $splashtag): array
    {
        $player = UnregisteredPlayer3::findBySplashtagString($splashtag);

        if (!$player) {
            throw new NotFoundHttpException('Unregistered player not found.');
        }

        return [
            'name' => $player->name,
            'number' => $player->number,
            'total_battles' => $player->total_battles,
            'total_wins' => $player->total_wins,
            'win_rate' => $player->getWinRate(),
            'disconnect_rate' => $player->getDisconnectRate(),
            'performance_stats' => $player->performance_stats,
            'weapon_stats' => $player->weapon_stats,
            'lobby_stats' => $player->lobby_stats,
            'teammate_stats' => $player->teammate_stats,
        ];
    }
}
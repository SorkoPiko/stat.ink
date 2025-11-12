<?php

/**
 * @copyright Copyright (C) 2025 AIZAWA Hina
 * @license https://github.com/fetus-hina/stat.ink/blob/master/LICENSE MIT
 * @author AIZAWA Hina <hina@fetus.jp>
 */

declare(strict_types=1);

namespace app\actions\show\v3;

use Yii;
use app\models\UnregisteredPlayer3;
use yii\base\Action;
use yii\web\NotFoundHttpException;

use function preg_match;

final class UnregisteredPlayerAction extends Action
{
    public function run(): string
    {
        $request = Yii::$app->request;
        $refId = (string)$request->get('ref_id');
        $splashtag = (string)$request->get('splashtag');

        $player = null;

        if ($refId) {
            // Validate ref_id format (should be 32 character hex string)
            if (!preg_match('/^[0-9a-f]{32}$/', $refId)) {
                throw new NotFoundHttpException(Yii::t('app', 'Invalid player reference ID'));
            }
            $player = UnregisteredPlayer3::findByRefId($refId);
        } elseif ($splashtag) {
            // Try to find by splashtag
            $player = UnregisteredPlayer3::findBySplashtagString($splashtag);
        }

        if (!$player || !$player->hasSignificantData()) {
            throw new NotFoundHttpException(
                Yii::t('app', 'Could not find player or insufficient data available')
            );
        }

        return $this->controller->render('unregistered-player', [
            'player' => $player,
        ]);
    }
}
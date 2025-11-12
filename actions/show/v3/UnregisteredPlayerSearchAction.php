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
use yii\helpers\Url;
use yii\web\Response;

use function trim;
use function urlencode;

final class UnregisteredPlayerSearchAction extends Action
{
    public function run(): string|Response
    {
        $request = Yii::$app->request;
        $splashtag = trim((string)$request->post('splashtag'));

        // If form was submitted with a splashtag, try to find the player
        if ($request->isPost && $splashtag) {
            $player = UnregisteredPlayer3::findBySplashtagString($splashtag);
            
            if ($player && $player->hasSignificantData()) {
                // Redirect to the player's page using ref_id
                return $this->controller->redirect([
                    'show-v3/unregistered-player', 
                    'ref_id' => $player->ref_id
                ]);
            } else {
                Yii::$app->session->setFlash('error', 
                    Yii::t('app', 'Player not found or insufficient data available. Try exact format: Username#1234')
                );
            }
        }

        return $this->controller->render('unregistered-player-search', [
            'searchValue' => $splashtag,
        ]);
    }
}
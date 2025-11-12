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

        Yii::info("UnregisteredPlayerSearch: Starting search process", __METHOD__);
        Yii::info("Request method: " . $request->method, __METHOD__);
        Yii::info("Is POST: " . ($request->isPost ? 'yes' : 'no'), __METHOD__);
        Yii::info("Raw splashtag input: '{$splashtag}'", __METHOD__);

        // If form was submitted with a splashtag, try to find the player
        if ($request->isPost && $splashtag) {
            Yii::info("Attempting to find player with splashtag: '{$splashtag}'", __METHOD__);

            $player = UnregisteredPlayer3::findBySplashtagString($splashtag);
            
            if ($player) {
                Yii::info("Player found: {$player->name}#{$player->number} (ref_id: {$player->ref_id})", __METHOD__);
                Yii::info("Player has significant data: " . ($player->hasSignificantData() ? 'yes' : 'no'), __METHOD__);
                Yii::info("Player battle count: {$player->total_battles}", __METHOD__);

                if ($player->hasSignificantData()) {
                    // Redirect to the player's page using ref_id
                    Yii::info("Redirecting to player page with ref_id: {$player->ref_id}", __METHOD__);
                    return $this->controller->redirect([
                        'show-v3/unregistered-player',
                        'ref_id' => $player->ref_id
                    ]);
                } else {
                    $errorMsg = Yii::t('app', 'Player found but has insufficient data (less than 5 battles). Found {battles} battles.', [
                        'battles' => $player->total_battles,
                    ]);
                    Yii::warning("Player found but insufficient data: {$player->total_battles} battles", __METHOD__);
                    Yii::$app->session->setFlash('error', $errorMsg);
                }
            } else {
                Yii::warning("No player found for splashtag: '{$splashtag}'", __METHOD__);

                // Debug: Check if there's any data at all
                $debugInfo = UnregisteredPlayer3::debugDataAvailability();
                Yii::info("Debug info - Available data: " . json_encode($debugInfo), __METHOD__);

                Yii::$app->session->setFlash('error',
                    Yii::t('app', 'Player not found. Please check the exact format: Username#1234. Player must have appeared in at least 5 public battles.')
                );
            }
        }

        return $this->controller->render('unregistered-player-search', [
            'searchValue' => $splashtag,
        ]);
    }
}
<?php

namespace api\modules\v1\controllers;

use api\behaviors\returnStatusBehavior\JsonSuccess;
use common\models\Weather;
use OpenApi\Attributes\Get;
use OpenApi\Attributes\Items;
use OpenApi\Attributes\Property;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\httpclient\Client;

class WeatherController extends AppController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return ArrayHelper::merge(parent::behaviors(), ['auth' => ['except' => ['index']]]);
    }

    #[Get(
        path: '/weather/index',
        operationId: 'weather-index',
        description: 'JSON-фвйл с погодой',
        summary: 'Погода',
        security: [['bearerAuth' => []]],
        tags: ['weather']
    )]

    #[JsonSuccess(content: [
        new Property(
            property: 'weather', type: 'array',
            items: new Items(ref: '#/components/schemas/Weather'),
        )
    ])]
    public function actionIndex(): array
    {
        $weather = Weather::findOne(['key' => 'yandex_weather_json']);

        if(empty($weather)) {
            $weather = new Weather();
            $weather->key = 'yandex_weather_json';
        }

        if(($weather->created_at + 60 * 30) < time() && $weather->created_at != null) {
            return $this->returnSuccess([
                'weather' => json_decode($weather->json)
            ]);
        }
        else{
            $file = $this->getWeatherHttpClient();
            if(!$file){
                return $this->returnError([
                    'Ошибка 500' ]);
            }
            $weather->json = $file;
            $weather->created_at = time();
            $weather->save();
        }

        return $this->returnSuccess([
            'weather' =>  json_decode($this->getWeatherHttpClient())]);


    }

    public function getWeatherHttpClient ()
    {
        $client = new Client([
            'transport' => 'yii\httpclient\CurlTransport'
        ]);
        $url = Yii::$app->environment->WEATHER_API_LINK;
        $params = [
            'lat' => fn() => Yii::$app->environment->LATITUDE,
            'lon' => fn() => Yii::$app->environment->LONGITUDE,
        ];

        $response = $client->createRequest()
            ->setUrl($url)
            ->addHeaders(['X-Yandex-Weather-Key' => Yii::$app->environment->TOKEN_KEY])
            ->setData($params)
            ->send();


        if($response->isOk)
            return  $response->content;
        else {
            Yii::error($response->statusCode);
            return null;
        }

    }


}

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
        $weather = Weather::findOne(['file' => 'weather_yandex_json']);

        if(empty($weather))
            $weather = new Weather();

        if(($weather->created_at+60*30) < time() && $weather->created_at != null) {
            return $this->returnSuccess([
                'weather' => $weather
            ]);
        }
        else{
    //            file_put_contents(Yii::$app->request->hostInfo . $weather->file, $response);
            [$errors, $file] = $this->getWeatherStreamContext();
            if($errors){
                return $this->returnError([
                    'Ошибка подклюячения к Yandex.Weather' ]);
            }
            $weather->file = $file;
            $weather->created_at = time();
            $weather->save();
        }

        return $this->returnSuccess([
            'weather' =>  json_decode($this->getWeatherStreamContext())]);


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

        $response = $client->get($url, $params, [
            'headers' => [
                'X-Yandex-Weather-Key' => Yii::$app->environment->TOKEN_KEY,
            ]
        ])->send();

        $errors = null;

        if(!$response->isOk)
            $errors = 1;


        return [$errors, $response];

    }

    public function getWeatherStreamContext()
    {
        $access_key = Yii::$app->environment->TOKEN_KEY;

        $opts = array(
            'http' => array(
                'method' => 'GET',
                'header' => 'X-Yandex-Weather-Key: ' . $access_key,
                'data' => ['lat' => Yii::$app->environment->LATITUDE, 'lon' => Yii::$app->environment->LONGITUDE]
            )
        );

        $context = stream_context_create($opts);

        $file =
            file_get_contents(Yii::$app->environment->WEATHER_API_LINK,
                false, $context);
        return $file;
    }

}

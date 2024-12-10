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
                $weather->file = $this->getWeatherStreamContext();
                $weather->created_at = time();
                $weather->save();
        }


        return $this->returnSuccess([
            'weather' =>  $weather]);


    }

    public function getWeatherHttpClient ()
    {
        $client = new Client([
            'transport' => 'yii\httpclient\CurlTransport'
        ]);

        return $response = $client->createRequest()
            ->setMethod('GET')
            ->addHeaders(['Authorisation' => 'X-Yandex-Weather-Key: ' . Yii::$app->environment->TOKEN_KEY])
            ->setUrl(Yii::$app->environment->LINK)
//            ->setData(['latitude' => Yii::$app->environment->LATITUDE, 'longitude' => Yii::$app->environment->LONGITUDE])
            //               ->setOutputFile($fh)
//            ->setOptions([
//                CURLOPT_CONNECTTIMEOUT => 5, // тайм-аут подключения
//                CURLOPT_TIMEOUT => 10, // тайм-аут получения данных
//            ])
            ->send();
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
